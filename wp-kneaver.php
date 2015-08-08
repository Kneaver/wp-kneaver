<?php
/*
Plugin Name: WP Kneaver
Plugin URI: https://kneaver.com/products/features/wp-kneaver-wordpress-connector.htm
Description: WP Kneaver introduces short codes to reuse Kneaver's contents inside Wordpress posts or pages.
Version: 1.0.52
Author: Bruno Winck at Kneaver Corp
Author URI: http://kneaver.com/BrunoWinck
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
        // we need a http.get, a user/pwd, a cache because it is called at each display of page
        $KneaverSpan = "<span id='$id' >$KneaverContents</span>";
        return do_shortcode($KneaverSpan );
    }
    else
        return do_shortcode($contents );
}

add_shortcode('kneaver', 'insert_kneaver');

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

add_shortcode('kneaverchat', 'kneaverchat');

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

add_shortcode('kneaverchatplayer', 'kneaverchatplayer');


/*
this was taken from rm_wpautop
at http://rolandog.com/archives/2008/11/12/wp-plugin-rm-wpautop/
but too complex to edit pages one after the other to set wpautop='false'
How else ? clean Kneaver output TODO:
function WPKneaver_rm_wpautop($content) {
    global $post;
    // Get the keys and values of the custom fields:
    $rmwpautop = get_post_meta($post->ID, 'wpautop', true);
    // Remove the filter, what if we are in the_excerpt case ? have two filters ?
    remove_filter('the_content', 'wpautop' );
//    if ('false' === $rmwpautop) {
//    } else {
//        add_filter('the_content', 'wpautop');
//    }
    return $content;
}
// Hook into the Plugin API

add_filter('the_content', 'WPKneaver_rm_wpautop', 9);
add_filter('the_excerpt', 'WPKneaver_rm_wpautop', 9);

*/

// We could also create a dependency on the plugin Scalable Vector Graphics (SVG)
// We may have more types and specially allow the svg to be posts themselves instead of attachements
function WPKneaver_i_can_haz_svg( $existing_mimes = array() ) {
$new_mimes = $existing_mimes;
$new_mimes['svg'] = 'mime/type';
return $new_mimes;
}
add_filter( 'upload_mimes' , 'WPKneaver_i_can_haz_svg' );

function WPKneaver_styles() 
{
    // the teme css itself, although compulsory on disk will not be served by default
    //!! TODO wp_register_style( 'kneaver_css', get_template_directory_uri() . '/style.css', array( 'bs_bootstrap', 'awesome_css' ) );
    // enqueue could have taken styles without registrating them
    //!! TODO wp_enqueue_style( array( 'kneaver_css' ));
    // Add scripts & styles with conditional
    wp_enqueue_style( 'kneaver_style', plugins_url( 'client/css/wp-kneaver.css', __FILE__ ) );
}
add_action( 'wp_enqueue_scripts', 'WPKneaver_styles' );

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
function process_template0( $lastposts, $group, $Tpl, $TplElem, $TPLIndicators, $Excerpt)
{
    $elems = "";
    $indicators = "";
    
    $pos = 1;
    foreach ( $lastposts as $post ) :
      setup_postdata( $post );
      $extra_class = "";
      if ( $pos == 1)
          $extra_class .= " active";
      if ( $Excerpt)
        $content = get_the_excerpt();
      else
        $content = get_the_content_with_formatting();
      $title = get_the_title( $post);
      $url = get_permalink( $post);
      {
          $elem = $TplElem;
          $elem = str_replace( '$pos', $pos, $elem);
          $elem = str_replace( '$pos0', $pos-1, $elem);
          $elem = str_replace( '$group', $group, $elem);
          $elem = str_replace( '$extra_class', $extra_class, $elem);
          $elem = str_replace( '$content', $content, $elem);
          $elem = str_replace( '$title', $title, $elem);
          $elem = str_replace( '$url', $url, $elem);
          $elems .= $elem;
      }
      {
          $elem = $TplIndicators;
          $elem = str_replace( '$pos', $pos, $elem);
          $elem = str_replace( '$pos0', $pos-1, $elem);
          $elem = str_replace( '$group', $group, $elem);
          $elem = str_replace( '$extra_class', $extra_class, $elem);
          $elem = str_replace( '$content', $content, $elem);
          $elem = str_replace( '$title', $title, $elem);
          $elem = str_replace( '$url', $url, $elem);
          $indicators .= $elem;
      }
      $pos++;
    endforeach; 
    $contents = $Tpl;
    $contents = str_replace( '$group', $group, $contents);
    $contents = str_replace( '$indicators', $indicators, $contents);
    $contents = str_replace( '$elems', $elems, $contents);
    wp_reset_postdata(); 
    return $contents;
}

function process_template( $post_type, $group, $Tpl, $TplElem, $TPLIndicators, $count, $offset)
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
    }
    else
    {
        // add a group key in $arguments array if group key available in shortcode
        $arguments['child-group'] = $group;
        $arguments['orderby'] = 'post_title';
	$arguments['order']  = 'ASC';
    }
    return process_template0( get_posts( $arguments ), $group, $Tpl, $TplElem, $TPLIndicators, false);
}

function kneaver_category_posts($atts, $contents=NULL) 
{
    extract(shortcode_atts(array(
        'category'    => '',
        'post_type'    => 'post',
        'item_class'    => 'panel-default',
        'count'    => '5',
        'offset'    => '0',
    ), $atts));

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

    $TplIndicators = 
    '' 
    ;
    
    $contents = process_template( $post_type, $category, $Tpl, $TplElem, $TPLIndicators, $count, $offset);
    return do_shortcode($contents );
}
add_shortcode('include_category_posts', 'kneaver_category_posts');

// take inspiration from this discovered after http://en.support.wordpress.com/display-posts-shortcode/
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

    $TplIndicators = 
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
    $contents = process_template0( get_posts( $arguments ), $category, $Tpl, $TplElem, $TPLIndicators, true);
    return do_shortcode( $contents );
}
add_shortcode('include_category_posts_masonry', 'kneaver_category_posts_masonry');
// use like [include_category_posts_masonry category="#PKMChat"]
// http://codex.wordpress.org/Shortcode_API
// don't forget frunt -wuatch while debugging

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

    $TplIndicators = 
    '<li data-target="#$group" data-slide-to="$pos0" class="$extraclass"></li>' 
    ;
    
    if ( $with_indicators == 'false')
        $Tpl = str_replace( '$IndicatorsOL', '', $Tpl);
    else
        $Tpl = str_replace( '$IndicatorsOL', '<ol class="carousel-indicators">$indicators</ol>', $Tpl);
        
    $contents = process_template( $post_type, $group, $Tpl, $TplElem, $TPLIndicators, 20, 0);
    return do_shortcode($contents );
}
add_shortcode('carousel', 'kneaver_carousel');

function kneaver_sections($atts, $contents=NULL) 
{
    extract(shortcode_atts(array(
        'group'    => '',
        'post_type'    => 'child-post',
    ), $atts));

    $Tpl = 
    '$elems'
    ;
    
    $TplElem = 
    '<section id="Section-$pos"><div class="container">' .
    '$content' .
    '</div></section >'
    ;

    $TplIndicators = 
    '' 
    ;
    
    $contents = process_template( $post_type, $group, $Tpl, $TplElem, $TPLIndicators, 20, 0);
    return do_shortcode($contents );
}
add_shortcode('sections', 'kneaver_sections');

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

    $TplIndicators = 
    '' 
    ;
    
    $contents = process_template( $post_type, $group, $Tpl, $TplElem, $TPLIndicators, 20, 0);
    return do_shortcode($contents );
}
add_shortcode('sections_nav', 'kneaver_sections_nav');

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
add_shortcode( 'knvtweet', 'kneaver_tweet');

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

add_shortcode('knvinclude', 'kneaver_include');

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
add_shortcode('knvques', 'kneaver_questions');

function kneaver_chatbuttons($atts, $contents=NULL) 
{ 
    /*
    <strong>Time:</strong> Wednesday, January 21sth, New York 2PM ET, Paris 8PM CET, Sydney Jan 21st 6AM AEDT</h4>
    <a class="btn btn-warning" title="Your Local Time" href="http://www.timeanddate.com/worldclock/fixedtime.html?msg=%23PKMChat&amp;iso=20150121T14&amp;p1=179&amp;ah=1" target="_blank">Click for your local time</a>
    - <a class="btn btn-primary" href="http://www.timeanddate.com/scripts/ics.php?type=utc&amp;p1=179&amp;iso=20150121T14&amp;ah=1&amp;msg=%23PKMChat" target="_blank">add to calendar</a>
    <strong>Time:</strong> Wednesday, March 25th, New York 4PM ET, Paris 9PM CET, Sydney Mar 26th 7AM AEDT</h4>
<?php echo get_post_meta($post->ID, 'start_time', true); ?>
<?php echo get_post_meta($post->ID, 'recap_slug', true); ?>
    */
    extract(shortcode_atts(array(
        'src'      => 'ibid',
    ), $atts));
    global $post;
    if ( get_post_meta( $post->ID, 'start_time', true) === false)
    {
        return "missing start_time in post=" . strval( $post->ID);
    }
    else
    {
      $Stamp = strtotime( get_post_meta( $post->ID, 'start_time', true));
      if ( $Stamp === false)
      {
        // TODO: This happens in embedded post page
        return "invalid start_time " . get_post_meta( $post->ID, 'start_time', true);
      }
      else
      {
        $Strdate = get_post_meta( $post->ID, 'start_time', true);
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
        if ( get_post_meta($post->ID, 'recap_slug', true))
        {
          // keep style btn ? 
          // href="http://kneaverdemo.kneaver.com/pkmchat-how-we-learn-from-experience-remarkable/">Remarkable Tweets</a></strong>
          $RecapSlug = get_post_meta($post->ID, 'recap_slug', true);
          $contents .= "<a class=\"btn btn-warning\" title=\"Recap\" href=\"http://recaps.kneaver.com/" . $RecapSlug . "/\" target=\"_blank\">Recap</a>";
          $contents .= "- <a class=\"btn btn-primary\" href=\"http://recaps.kneaver.com/" . $RecapSlug . "-participants/\" target=\"_blank\">Participants</a>";
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
add_shortcode('knvchatbuttons', 'kneaver_chatbuttons');

class WPKneaver
{
    function posts_where($where)
    {
        // if I leave the is_search I get the normal search added (OR) 
        if(is_search())
        {
            $s = $_GET['s'];
            
            if(!empty($s))
            {
                $request = new http\Client\Request("GET",
                    "http://localhost:3058/scriptjs/query?q=" . $s ,
                    ["User-Agent"=>"My Client/0.1"]
                );
                $request->setOptions(["timeout"=>1]);

                $client = new http\Client;
                $client->enqueue($request)->send();

                // pop the last retrieved response
                $response = $client->getResponse();
                $list = $response->getBody();
                if( $s != "empty")
                if( $s != "none")
                {
                    global $wpdb;
                    
                    $where = str_replace(
                      '(' . $wpdb->posts . '.post_title LIKE', 
                      '(' . $wpdb->posts . '.ID in (' . $list . ')) OR (' . $wpdb->posts . '.post_title LIKE', 
                      $where);
                      
                    $where = ' and (' . $wpdb->posts . '.ID in (' . $list . '))';
                    // SELECT SQL_CALC_FOUND_ROWS  ptbopq6_posts.ID FROM ptbopq6_posts  WHERE 1=1 (ptbopq6_posts.ID in (4365,4401))  ORDER BY ptbopq6_posts.post_title LIKE '%xapi%' DESC, ptbopq6_posts.post_date DESC LIMIT 0, 5 made by require('C:\inetpub\kneaver.com\wp-blog-header.php'), wp, WP->main, WP->query_posts, WP_Query->query, WP_Query->get_posts
                    // made by parse-search(q) called by get_posts() all in query.php
                }
            }
        }
        
        return $where;
    }
    
    function __construct()
    {
        // was posts_filters but for me it's better posts_search
        add_filter('posts_search', array(&$this, 'posts_where'), 1500);
    }
}
new WPKneaver();

?>
