<?php
/*
Plugin Name: WP Kneaver
Plugin URI: https://kneaver.com/products/features/wp-kneaver-wordpress-connector/
Description: WP Kneaver introduces short codes to reuse Kneaver's contents inside Wordpress posts or pages.
Version: 1.0.60
Author: Bruno Winck at Kneaver Corp
Author URI: https://kneaver.com/BrunoWinck
License: GPLv2 or later
*/
/*  Copyright 2014  Kneaver Corp  (email : support@kneaver.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

// https://wordpress.org/support/topic/get_the_content-with-formatting?replies=8
// http://www.web-templates.nu/2008/08/31/get_the_content-with-formatting/
function get_the_content_with_formatting ($more_link_text = '(more...)', $stripteaser = 0, $more_file = '') {
    $content = get_the_content($more_link_text, $stripteaser, $more_file);
    $content = apply_filters('the_content', $content);
    $content = str_replace(']]>', ']]&gt;', $content);
    return $content;
}

// More shortcodes
// a "plugin" function, invented by Bruno, not part of theme structure or requirements
function process_template0( $args, $group, $Tpl, $TplElem, $TPLIndicators, $Excerpt)
{
    // https://codex.wordpress.org/Template_Tags/get_posts
    $elems = "";
    $indicators = "";
    
    $pos = 1;
    // danger $post is a global _and_ setup_postdata expect that it is this global passed to it
    // either use WP_Query as explained in method 2 of https://codex.wordpress.org/The_Loop
    // https://codex.wordpress.org/Class_Reference/WP_Query#Parameters
    // or avoid this $post
    // option 2
    global $wp_query;
    $temp_query = clone $wp_query;
    query_posts( $args );
    while ( have_posts() ) : the_post();
    // option 2 end

    // option 1
    // $posts = get_posts( $args);
    // foreach ( $posts as $post ) :
        // https://codex.wordpress.org/Function_Reference/setup_postdata
       //  setup_postdata( $post );
    // option 1 end
        $extra_class = "";
        if ( $pos == 1)
            $extra_class .= " active";
        if ( $Excerpt)
          $content = get_the_excerpt();
        else
          $content = get_the_content_with_formatting();
        $title = get_the_title();
        $url = get_permalink();
        $slug = get_post_field( "post_name");
        {
            $elem = $TplElem;
            $elem = str_replace( '$pos', $pos, $elem);
            $elem = str_replace( '$pos0', $pos-1, $elem);
            $elem = str_replace( '$group', $group, $elem);
            $elem = str_replace( '$extra_class', $extra_class, $elem);
            $elem = str_replace( '$content', $content, $elem);
            $elem = str_replace( '$title', $title, $elem);
            $elem = str_replace( '$url', $url, $elem);
            $elem = str_replace( '$slug', $slug, $elem);
            $elem = str_replace( '<p>\n</p>', '', $elem);
            $elem = str_replace( '<p>\r\n</p>', '', $elem);
	    
            $elems .= $elem;
        }
        {
            $elem = $TPLIndicators;
            $elem = str_replace( '$pos', $pos, $elem);
            $elem = str_replace( '$pos0', $pos-1, $elem);
            $elem = str_replace( '$group', $group, $elem);
            $elem = str_replace( '$extra_class', $extra_class, $elem);
            $elem = str_replace( '$content', $content, $elem);
            $elem = str_replace( '$title', $title, $elem);
            $elem = str_replace( '$url', $url, $elem);
            $elem = str_replace( '$slug', $slug, $elem);
            $indicators .= $elem;
        }
        $pos++;
    // option 1
    // endforeach; 
    // wp_reset_postdata(); 
    // option 1 end
    // option 2
    endwhile;
    // now back to our regularly scheduled programming
    $wp_query = clone $temp_query;
    // option 2 end
    $contents = $Tpl;
    $contents = str_replace( '$group', $group, $contents);
    $contents = str_replace( '$indicators', $indicators, $contents);
    $contents = str_replace( '$elems', $elems, $contents);
   
    return $contents;
}

function process_template( $post_type, $group, $Tpl, $TplElem, $TPLIndicators, $count, $offset, $past, $future)
{
    $arguments = array();
    // use template and eval
    // have a version taking the query, use it for featured post
    // add a post_type and showposts key in $arguments array
    $arguments['post_type'] = $post_type;
    if ( $post_type == 'post')
    {
        $arguments['category_name'] = $group;
        $arguments['orderby'] = 'post_date';
        $arguments['order']  = 'DESC';
        $arguments['posts_per_page'] = $count;
        $arguments['offset'] = $offset;
        if ( !$past)
        {
          // only future
          $arguments['meta_key'] = 'start_time';
          $arguments['meta_value'] = date("c");
          $arguments['meta_compare'] = '>=';
          $arguments['orderby'] = 'meta_value';
          $arguments['order']  = 'ASC';
        }

        if ( !$future)
        {
          // only past
          $arguments['meta_key'] = 'start_time';
          $arguments['meta_value'] = date("c");
          $arguments['meta_compare'] = '<=';
          $arguments['orderby'] = 'meta_value';
        }
    }
    else
    {
        // add a group key in $arguments array if group key available in shortcode
        $arguments['child-group'] = $group;
        $arguments['orderby'] = 'post_title';
        $arguments['order']  = 'ASC';
    }
    return process_template0( $arguments, $group, $Tpl, $TplElem, $TPLIndicators, false);
}

class WPKneaver
{

    // see http://codex.wordpress.org/Writing_a_Plugin
    // https://codex.wordpress.org/Shortcode_API
    // http://www.sitepoint.com/unleash-the-power-of-the-wordpress-shortcode-api/
    // http://www.smashingmagazine.com/2012/05/01/wordpress-shortcodes-complete-guide/
    function insert_kneaver($atts, $contents=NULL) 
    {
        extract(shortcode_atts(array(
            'src'      => 'ibid',
            'id'    => '',
            'name'    => '',
            'class'    => '',
        ), $atts));
        if ($src != "ibid")
            return "Only source in same place as publisher is supported";
        if ($name)
        {
        }
        else
        if (empty($id)) 
            return '<!-- Kneaver id missing -->';
        if ($contents == NULL)
        {
            $KneaverContents = "Here will come Kneaver contents" . $contents;
            // to make it live we would need an http.get, a user/pwd, a cache because it is called at each display of page
            $KneaverSpan = "<span id='$id' >$KneaverContents</span>";
            return do_shortcode($KneaverSpan );
        }
        else
            return do_shortcode($contents );
    }

    function kneaverchat($atts, $contents=NULL) 
    {
        extract(shortcode_atts(array(
            'src'      => 'ibid',
            'id'    => '',
            'name'    => '',
            'class'    => '',
            'hashtag'    => '',
            'startdate'    => '',
            'duration'    => '',
            'moderators'    => '',
            'guests'    => '',
            'topic'    => ''
        ), $atts));
        if ($contents == NULL)
        {
            $KneaverContents = "Kneaver Chat Transcript will appear here" . $contents;
            $KneaverSpan = "<span id='$id' >$KneaverContents</span>";
            return do_shortcode($KneaverSpan );
        }
        else
            return do_shortcode($contents );
    }

    function kneaverchatplayer($atts, $contents=NULL) 
    {
        extract(shortcode_atts(array(
            'src'      => 'ibid',
            'id'    => '',
            'name'    => '',
            'class'    => '',
            'hashtag'    => '',
            'startdate'    => '',
            'duration'    => '',
            'moderators'    => '',
            'guests'    => '',
            'topic'    => ''
        ), $atts));
        if ($contents == NULL)
        {
            $KneaverContents = "Kneaver Chat Player will appear here" . $contents;
            $KneaverSpan = "<span id='$id' >$KneaverContents</span>";
            return do_shortcode($KneaverSpan );
        }
        else
            return do_shortcode($contents );
    }

    function kneaver_category_posts($atts, $contents=NULL) 
    {
        extract(shortcode_atts(array(
            'category'    => '',
            'post_type'    => 'post',
            'item_class'    => 'panel-default',
            'count'    => '5',
            'offset'    => '0',
            'past'    => true,
            'future'    => true,
        ), $atts));

        $past = ($past === true) || ($past === 'true');
        $future = ($future === true) || ($future === 'true');
        
        $Tpl = 
        '$elems'
        ;
        
        $TplElem = 
        '<div class="panel ' . $item_class . '">' .
        '  <div class="panel-heading">' .
        '    <h3 class="panel-title"><a href="$url">$title</a></h3>' .
        '  </div>' .
        '  <div class="panel-body">' .
        '    $content' .
        '  </div>' .
        '</div>'
        ;
        
        $TplElem2 = 
        '<section id="Section-$pos"><div class="container">' .
        '<a href="$url"><h2 >$title</h2></a>' .
        '$content' .
        '</div></section >'
        ;

        $TPLIndicators = 
        '' 
        ;
        
        $contents = process_template( $post_type, $category, $Tpl, $TplElem, $TPLIndicators, $count, $offset, $past, $future);
        return do_shortcode($contents );
    }

    // TODO: take inspiration from this discovered after 
    // https://github.com/billerickson/display-posts-shortcode/wiki
    function kneaver_category_posts_masonry($atts, $contents=NULL) 
    {
        extract(shortcode_atts(array(
            'category'    => '',
            'post_type'    => 'post',
            'item_class'    => 'panel-default',
            'count'    => '5',
            'offset'    => '0',
        ), $atts));

        $TplElem = 
        '<div class="masonry-item $extra_class col-sm-4">' .
        '<div class="panel ' . $item_class . '">' .
        '  <div class="panel-heading">' .
        '    <h3 class="panel-title"><a href="$url">$title</a></h3>' .
        '  </div>' .
        '  <div class="panel-body">' .
        '    $content' .
        '  </div>' .
        '</div>' .
        '</div>'
        ;
        
        $Tpl = 
        '<div class="container">' .
        '<div class="row js-masonry" data-masonry-options=\'{ "columnWidth": ".active", "itemSelector": ".masonry-item" }\'>' .
        '$elems' .
        '</div>'
        ;

        $TPLIndicators = 
        '' 
        ;
        
        $arguments = array();
        // use template and eval
        // have a version taking the query, use it for featured post
        // add a post_type and showposts key in $arguments array
        $arguments['post_type'] = $post_type;
        if ( $post_type == 'post')
        {
            $arguments['category_name'] = $category;
            $arguments['orderby'] = 'post_date';
            $arguments['order']  = 'DESC';
            $arguments['posts_per_page'] = $count;
            $arguments['offset'] = $offset;
        }
        else
        {
            // add a group key in $arguments array if group key available in shortcode
            $arguments['child-group'] = $category;
        }
        $contents = process_template0( $arguments, $category, $Tpl, $TplElem, $TPLIndicators, true);
        return do_shortcode( $contents );
    }

    function kneaver_carousel($atts, $contents=NULL) 
    {
        // extract turn fileds into local variables
        extract(shortcode_atts(array(
            'group'    => '',
            'post_type'    => 'child-post',
            'with_indicators' => 'true', 
            'item_class' => '', 
        ), $atts));

        
        // could use get_template_part but pb 1 to get output, 2 send the posts
        $Tpl = 
        '<div id="$group" class="carousel slide">' .
        '    $IndicatorsOL' .
        //   Carousel items -->
        '    <div class="carousel-inner">' .
        '$elems' .
        '    </div>' .
        // Carousel nav -->
        '    <a class="carousel-control left" href="#$group" data-slide="prev"><i class="fa fa-chevron-left fa-2x"></i></a>' .
        '    <a class="carousel-control right" href="#$group" data-slide="next"><i class="fa fa-chevron-right fa-2x"></i></a>' .
        '</div>'
        ;
        
        $TplElem = 
        '<div class="item$extra_class">' .
        '    <div class="row $item_class">' .
        '$content' .
        '    </div>' .
        '</div>' 
        ;
        $TplElem = str_replace( '$item_class', $item_class, $TplElem);

        $TPLIndicators = 
        '<li data-target="#$group" data-slide-to="$pos0" class="$extraclass"></li>' 
        ;
        
        if ( $with_indicators == 'false')
            $Tpl = str_replace( '$IndicatorsOL', '', $Tpl);
        else
            $Tpl = str_replace( '$IndicatorsOL', '<ol class="carousel-indicators">$indicators</ol>', $Tpl);
            
        $contents = process_template( $post_type, $group, $Tpl, $TplElem, $TPLIndicators, 20, 0, true, true);
        return do_shortcode($contents );
    }

    function kneaver_sections($atts, $contents=NULL) 
    {
        extract(shortcode_atts(array(
            'group'    => '',
            'post_type'    => 'child-post',
            'tpl'    => '$elems',
            'tpl_elem1'    => '<section id="$slug" class="$group">',
            'tpl_elem2'    => '<div class="container">',
            'tpl_end_elem1'    => '</section>',
            'tpl_end_elem2'    => '</div>',
            'tpl_indicators'    => '',
        ), $atts));
        $TplElem = 
        str_replace("QT", "\"", $tpl_elem1 . $tpl_elem2) .
        '$content' .
        $tpl_end_elem2 . $tpl_end_elem1
        ;

        $contents = process_template( $post_type, $group, $tpl, $TplElem, $tpl_indicators, 20, 0, true, true);
        return do_shortcode($contents );
    }

    function kneaver_sections_nav($atts, $contents=NULL) 
    {
        extract(shortcode_atts(array(
            'group'    => '',
            'post_type'    => 'child-post',
        ), $atts));

        $Tpl = 
        '$elems'
        ;
        
        $TplElem = 
        '<li><a href="#Section-$pos">$title</a></li>'
        ;

        $TPLIndicators = 
        '' 
        ;
        
        $contents = process_template( $post_type, $group, $Tpl, $TplElem, $TPLIndicators, 20, 0, true, true);
        return do_shortcode($contents );
    }

    function kneaver_tweet($atts, $contents=NULL) 
    {
        extract(shortcode_atts(array(
            'user'      => 'kneaver',
            'hashtags'      => 'km,pkm',
        ), $atts));
        if ($contents == NULL)
            $contents = "Tweet this";
    // or <a href="https://twitter.com/share" class="twitter-share-button" data-url="http://kneaver.com" data-text="test" data-via="brunowinck" data-size="large" data-related="brunowinck">Tweet</a>
    // <script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script>
        // we use intent
        // see <!-- https://dev.twitter.com/docs/intents -->
        // <a href="https://twitter.com/intent/tweet?in_reply_to=463440424141459456">Reply</a>
        // <a href="https://twitter.com/intent/retweet?tweet_id=463440424141459456">Retweet</a>
        // <a href="https://twitter.com/intent/favorite?tweet_id=463440424141459456">Favorite</a>
        // this would be better loaded once for all, without the script it's a plain link in a new window
        // with the script it appears as a dialog box
        $contents = "<script type=\"text/javascript\" async src=\"//platform.twitter.com/widgets.js\"></script>\n" . 
          "<a target=\"_new\" class=\"btn btn-primary btn-lg\" href=\"https://twitter.com/intent/tweet?via=" . $user . "&amp;related=" . $user . "&amp;hashtags=" . $hashtags . "&amp;text=" . urlencode( $contents) . "\"><i class=\"fa fa-twitter\"></i></b> ". $contents . "</a>";
        return do_shortcode( $contents );
    }

    function kneaver_include($atts, $contents=NULL) 
    {
        extract(shortcode_atts(array(
            'src'      => 'ibid',
        ), $atts));
        if ($contents == NULL)
        {
            $KneaverSpan = "";
            return do_shortcode( $KneaverSpan );
        }
        else
            return do_shortcode( $contents );
    }

    function kneaver_questions($atts, $contents=NULL) 
    {
        /*
        <ul>
            <li><span class="Ques">Q1: TBC</span></li>
            <li><span class="Ques">Q2: TBC</span></li>
            <li><span class="Ques">Q3: TBC</span></li>
            <li><span class="Ques">Q4: TBC</span></li>
            <li><span class="Ques">Q5: TBC</span></li>
            <li>NB: Questions are subject to change without notice.</li>
        </ul>
        */

        extract(shortcode_atts(array(
            'src'      => 'ibid',
        ), $atts));
        if ($contents == NULL)
        {
            $KneaverSpan = "";
            return do_shortcode( $KneaverSpan );
        }
        else
            return do_shortcode( $contents );
    }

    function kneaver_chatbuttons( $atts, $contents=NULL) 
    { 
        /*
        <strong>Time:</strong> Wednesday, January 21sth, New York 2PM ET, Paris 8PM CET, Sydney Jan 21st 6AM AEDT</h4>
        <a class="btn btn-warning" title="Your Local Time" href="http://www.timeanddate.com/worldclock/fixedtime.html?msg=%23PKMChat&amp;iso=20150121T14&amp;p1=179&amp;ah=1" target="_blank">Click for your local time</a>
        - <a class="btn btn-primary" href="http://www.timeanddate.com/scripts/ics.php?type=utc&amp;p1=179&amp;iso=20150121T14&amp;ah=1&amp;msg=%23PKMChat" target="_blank">add to calendar</a>
        <strong>Time:</strong> Wednesday, March 25th, New York 4PM ET, Paris 9PM CET, Sydney Mar 26th 7AM AEDT</h4>
        */
        extract(shortcode_atts(array(
            'src'      => 'ibid',
        ), $atts));
        // pb when this shortcde is in a page embedded in another using process_template0
        // Option 1
        // $post was not set, so we accessed the containing post, not the child post
        // second drawback is that get_post_meta may refetch the meta on each call and not reuse the WPQuery buffer
        // global $post;
        // $id = $post->ID;
        // Option 1 End
        // Option 2
        // this is more WP style
        $id = get_post()->ID;
        // Option 2 End
        $Strdate = get_post_meta( $id, 'start_time', true);
        $RecapSlug = get_post_meta($id, 'recap_slug', true);
        /*
        <?php echo Strdate ?>
        <?php echo $RecapSlug ?>
        */
        
        if ( $Strdate === false)
        {
            return "missing start_time in post=" . strval( $id);
        }
        else
        {
          $Stamp = strtotime( $Strdate);
          if ( $Stamp === false)
          {
            // TODO: This happens in embedded post page
            return "invalid start_time " . $Strdate;
          }
          else
          {
            $tz1 = new DateTimeZone("America/New_York");
            $tz2 = new DateTimeZone("Europe/Paris");
            $tz3 = new DateTimeZone("Australia/Sydney");
            $DateISO = new DateTime($Strdate);
            $Date1 = new DateTime($Strdate);
            $Date1->setTimezone( $tz1);
            $Date2 = new DateTime($Strdate);
            $Date2->setTimezone( $tz2);
            $Date3 = new DateTime($Strdate);
            $Date3->setTimezone( $tz3);
            // print_r($tz->getLocation());
            // print_r(timezone_location_get($tz));
            // http://php.net/manual/en/function.date.php
            // http://www.google.com/design/spec/patterns/data-formats.html, shld add minutes
            $contents = "<strong>Time:</strong> New York " . $Date1->format( "D, M jS") . ", " . $Date1->format( "g A") . " ET, Paris " . $Date2->format( "g A") . " CET, Sydney " . $Date3->format( "D, M jS") . " " . $Date3->format( "g A") . " AEDT</h4>";
            if ( $RecapSlug)
            {
		// keep style btn ? 
		// href="http://kneaverdemo.kneaver.com/pkmchat-how-we-learn-from-experience-remarkable/">Remarkable Tweets</a></strong>
		if ( preg_match( "/\.htm$/i", $RecapSlug ) )
		{
		    $contents .= "<a class=\"btn btn-warning\" title=\"Recap\" href=\"http://recaps.kneaver.com/" . $RecapSlug . "\" target=\"_blank\">Recap</a>";
		    $contents .= "- <a class=\"btn btn-primary\" href=\"http://recaps.kneaver.com/" . str_replace( '.htm', '-participants.htm', $RecapSlug) . "\" target=\"_blank\">Participants</a>";
		}
		else
		{
		    $contents .= "<a class=\"btn btn-warning\" title=\"Recap\" href=\"http://recaps.kneaver.com/" . $RecapSlug . "/\" target=\"_blank\">Recap</a>";
		    $contents .= "- <a class=\"btn btn-primary\" href=\"http://recaps.kneaver.com/" . $RecapSlug . "-participants/\" target=\"_blank\">Participants</a>";
		}
            }
            else
            {
              $contents .= "<a class=\"btn btn-warning\" title=\"Your Local Time\" href=\"http://www.timeanddate.com/worldclock/fixedtime.html?msg=%23PKMChat&amp;iso=" . $DateISO->format( "Ymd\TH") . "&amp;p1=0&amp;ah=1\" target=\"_blank\">Click for your local time</a>";
              $contents .= "- <a class=\"btn btn-primary\" href=\"http://www.timeanddate.com/scripts/ics.php?type=utc&amp;p1=0&amp;iso=" . $DateISO->format( "Ymd\TH") . "&amp;ah=1&amp;msg=%23PKMChat\" target=\"_blank\">add to calendar</a>";
            }
          }
        }
        if ($contents == NULL)
        {
            $KneaverSpan = "";
            return do_shortcode( $KneaverSpan );
        }
        else
            return do_shortcode( $contents );
    }

    function kneaver_edit($atts, $contents=NULL) 
    { 
        // extract(shortcode_atts(array(
        // ), $atts));
        return "";
    }

    function add_header_xua() 
    {
        // Access-Control-Allow-Credentials: true
        // to send the cookies
        // this must be done on target, not here
        // header( 'Access-Control-Allow-Origin: https://h.kneaver.com' );
        // http://www.html5rocks.com/en/tutorials/cors/#toc-adding-cors-support-to-the-server
    }
    
    function register_standards()
    {
        // kneaver-statics has been added as peerDependencies to reflect the runtime dependency

        // defines can be made in wp-config.php
        if (!defined('WPKneaver_CDN') || (constant( 'WPKneaver_CDN') != 'false'))
        {
          error_log( "'WPKneaver_CDN' was not defined");
          if (!defined('WPKneaver_CDN'))
            define( 'WPKneaver_CDN', 'true' );
          define( 'WPKneaver_ASSET_URI', '//cdn.kneaver.com/1.0.62/assets' );
          // define( 'WPKneaver_ASSET_URI', '/assets' );
          define( 'WPKneaver_CLIENT_URI', plugins_url('/client', __FILE__ ));
          define( 'CDNMin', '.min');
          // Bootstrap in Theme 2019-11-17 define( 'BootstrapCDN', '//netdna.bootstrapcdn.com/bootstrap/4.3.1');
          // Awesome in Theme 2019-11-17 define( 'FontAwesomeCDN', '//netdna.bootstrapcdn.com/font-awesome/4.4.0');
          // Roboto in Theme 2019-11-17 define( 'RobotoCDN', constant( 'WPKneaver_ASSET_URI'));
        }
        else
        {
          // error_log( "'WPKneaver_CDN' is false, using only local files");
          if (!defined('WPKneaver_ASSET_URI'))
            define( 'WPKneaver_ASSET_URI', '//localhost/assets');
              
          define( 'CDNMin', '');
          // Bootstrap in Theme 2019-11-17 define( 'BootstrapCDN', constant( 'WPKneaver_ASSET_URI') . '');
          // Awesome in Theme 2019-11-17 define( 'FontAwesomeCDN', constant( 'WPKneaver_ASSET_URI') . '');
          // Roboto in Theme 2019-11-17 define( 'RobotoCDN', constant( 'WPKneaver_ASSET_URI'));
        }
        // Bootstrap in Theme 2019-11-17 if (!wp_style_is( 'bs_bootstrap', 'registered' ))
        // Bootstrap in Theme 2019-11-17   wp_register_style( 'bs_bootstrap', constant( 'BootstrapCDN') . '/css/bootstrap' . constant( 'CDNMin') . '.css' );
        // Awesome in Theme 2019-11-17 wp_register_style( 'awesome_css', constant( 'FontAwesomeCDN') . '/css/font-awesome' . constant( 'CDNMin') . '.css' );
        // Roboto in Theme 2019-11-17 !! link is wrong wp_register_style( 'roboto_css', constant( 'RobotoCDN') . '/css/font-awesome.css' );
	
        // Bootstrap in Theme 2019-11-17 if (!wp_script_is( 'bs_bootstrap', 'registered' ))
        // Bootstrap in Theme 2019-11-17     wp_register_script( 'bs_bootstrap', constant( 'BootstrapCDN') . '/js/bootstrap' . constant( 'CDNMin') . '.js', array( '' ) );
    
        // site_scripts contains validation/submission by ajax script for forms
        // made in head so that WPKneaverRunForm is available
        // add script in footer, not in <head>
        wp_register_script( 'wp_kneaver_scripts', plugins_url('/client/js/wp-kneaver.js', __FILE__), array( ), '1.0', true );
        // this style is to make it easy for the theme to pick it
        wp_register_script( 'twitter', '//platform.twitter.com/widgets.js', array( ), '1.0', true );
    }
    
    function enqueue_styles() 
    {
        $this->register_standards();
        // the theme css itself, although compulsory on disk will not be served by default
        //!! TODO wp_register_style( 'kneaver_css', get_template_directory_uri() . '/style.css', array( 'bs_bootstrap', 'awesome_css' ) );
        // enqueue could have taken styles without registering them
        //!! TODO wp_enqueue_style( array( 'kneaver_css' ));
        // Add scripts & styles with conditional
        wp_enqueue_style( 'wpkneaver_style', plugins_url( '/client/css/wp-kneaver.css', __FILE__ ) );
    }
    
    function enqueue_scripts() 
    {
        // Adding scripts at end of page
        // Register the script like this for a theme:
        // the jquery at the end indicate that bootstrap requires jquery, so jquery will be indirectly loaded
        // it is the jquery of wordpress itself, the one in "wp-includes\js\jquery\jquery.js"
        // wordpress uses 1.11.0, same as bootstrap !! bootstrap shortcode has 1.7.2, !! there is 1.11.1 we use 2.0.3, there is 2.1.1

    //    // reuse bs_bootstrap like bootstrap shortcode
    //    if (!wp_script_is( 'bs_bootstrap', 'registered' ))
    //        wp_register_script( 'bs_bootstrap', WPKneaver_ASSET_URI . '/js/bootstrap.js', array( 'jquery' ) );

        // For either a plugin or a theme, you can then enqueue the script:
        // Bootstrap in Theme 2019-11-17 
        wp_enqueue_script( array( /*'bs_bootstrap', */ 'wp_kneaver_scripts' ) );
    }

    // https://codex.wordpress.org/TinyMCE_Custom_Buttons
    // https://codex.wordpress.org/Plugin_API/Filter_Reference/mce_buttons,_mce_buttons_2,_mce_buttons_3,_mce_buttons_4
    // http://code.tutsplus.com/tutorials/guide-to-creating-your-own-wordpress-editor-buttons--wp-30182

    // you need a plugin to create a new button
    // Load the TinyMCE plugin : editor_plugin.js (wp2.5)
    function register_tinymce_javascript($plugin_array) {
       $plugin_array['kneaver'] = plugins_url('/client/js/tinymce-plugin.js',__FILE__);
       return $plugin_array;
    }

    // then add the new buttons
    function register_buttons($buttons) {
       array_push( $buttons, 'knvedit', 'knvchatbuttons');
       return $buttons;
    }
     
    // We could also create a dependency on the plugin Scalable Vector Graphics (SVG)
    // We may have more types and specially allow the svg to be posts themselves instead of attachements
    function register_svg_mimetype( $existing_mimes = array() ) 
    {
      $new_mimes = $existing_mimes;
      $new_mimes['svg'] = 'mime/type';
      return $new_mimes;
    }

    function on_loaded() 
    {
      // First hook, same as plugins_loaded
      /*
      if ( !defined('BLA') ) {
          define ( 'BLA', 'http://google.com' );
      }
      */
    }

    function on_init() 
    {
	// second, after loaded, still wp-config define WPKneaver_CDN not yet set
    }
    
    function on_wp_loaded() 
    {
	// second, after loaded, still wp-config define WPKneaver_CDN not yet set
    }
    
    function __construct()
    {
	/* this is too early to use wp-config defines */
	
        add_shortcode( 'kneaver', array(&$this, 'insert_kneaver'));
        add_shortcode( 'kneaverchat', array(&$this, 'kneaverchat'));
        add_shortcode( 'kneaverchatplayer', array(&$this, 'kneaverchatplayer'));
        add_shortcode( 'include_category_posts', array(&$this, 'kneaver_category_posts'));
        add_shortcode( 'include_category_posts_masonry', array(&$this, 'kneaver_category_posts_masonry'));
        // use like [include_category_posts_masonry category="#PKMChat"]
        // http://codex.wordpress.org/Shortcode_API
        // don't forget grunt -watch while debugging
        add_shortcode( 'carousel', array(&$this, 'kneaver_carousel'));
        add_shortcode( 'sections', array(&$this, 'kneaver_sections'));
        add_shortcode( 'sections_nav', array(&$this, 'kneaver_sections_nav'));
        add_shortcode( 'knvtweet', array(&$this, 'kneaver_tweet'));
        add_shortcode( 'knvinclude', array(&$this, 'kneaver_include'));
        add_shortcode( 'knvques', array(&$this, 'kneaver_questions'));
        add_shortcode( 'knvchatbuttons', array(&$this, 'kneaver_chatbuttons'));
        add_shortcode( 'knvedit', array(&$this, 'kneaver_edit'));
        add_shortcode( 'ExtraUserInviteToForm', array(&$this, 'ExtraUserInviteToForm'));
        
        // examples wp-includes\default-filters.php and wp-content\plugins\tha-hooks-interface\tha-hooks-interface.php
        add_action( 'plugins_loaded', array(&$this, 'on_loaded'));
        add_action( 'init', array(&$this, 'on_init'));
        add_action( 'wp_loaded', array(&$this, 'on_wp_loaded'));
	
        add_action( 'send_headers', array(&$this, 'add_header_xua') );
        // there is only one hook wp_enqueue_scripts 
        // see https://codex.wordpress.org/Plugin_API/Action_Reference/wp_enqueue_scripts
        add_action( 'wp_enqueue_scripts', array(&$this, 'enqueue_styles') );
        add_action( 'wp_enqueue_scripts', array(&$this, 'enqueue_scripts') );
        
        add_filter( 'upload_mimes' , array(&$this, 'register_svg_mimetype') );
        add_filter( 'mce_buttons', array(&$this, 'register_buttons') );
        add_filter( 'mce_external_plugins', array(&$this, 'register_tinymce_javascript') );

        add_filter('the_content', 'wp_kneaver_wpautop_filter', 9);
        
        function startsWith ($string, $startString) 
        { 
            $len = strlen($startString); 
            return (substr($string, 0, $len) === $startString); 
        } 
        
        function wp_kneaver_wpautop_filter($content) {
          global $post;

          // Testing at strat only prevent the setting to bubble up into including posts
          if ( startsWith( $post->post_content, "<!-- NoBreaks -->"))
          {
            // Remove the wpautop filter and install a fake one
            // do for both the_content and the_excerpt
            remove_filter('the_content', 'wpautop');
            remove_filter('the_excerpt', 'wpautop');
            add_filter('the_content', function ($data) { return wpautop($data, false); } );
            add_filter('the_excerpt', function ($data) { return wpautop($data, false); } );
          } elseif ( startsWith( $post->post_content, "<!-- NoPs -->") ) {
            // Remove the wpautop filter completely
            remove_filter('the_content', 'wpautop');
            remove_filter('the_excerpt', 'wpautop');
          }
          // I assume filters are reset for each posts, not need to put it back

          return $content;
        }
        

    }
}
new WPKneaver();

?>
