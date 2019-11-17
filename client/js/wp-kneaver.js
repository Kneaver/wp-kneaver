/*
Plugin Name: WP Kneaver
Plugin URI: https://kneaver.com/products/features/wp-kneaver-wordpress-connector/
Description: WP Kneaver introduces short codes to reuse Kneaver's contents inside Wordpress posts or pages.
Version: 1.0.62
Author: Bruno Winck at Kneaver Corp
Author URI: https://kneaver.com/BrunoWinck
License: GPLv2 or later
*/
/*  Copyright 2019 Kneaver Corp  (email : support@kneaver.com)

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
// jQuery and bootstrap expected to be in theme
// http://css-tricks.com/snippets/javascript/get-url-variables/
function getQueryVariable(variable)
{
    var query = window.location.search.substring(1);
    var vars = query.split("&");
    for (var i=0;i<vars.length;i++) {
      var pair = vars[i].split("=");
      if(pair[0] == variable)
      {
        return decodeURIComponent(pair[1]);
      }
    }
    return( false );
}

function WPKneaverRunForm( MsgOK, MsgFailed, Target)
{
jQuery(document).ready(function ()
{
    // after loading the DOM
    // normally not required in calling page setRequestHeader("Access-Control-Allow-Origin","*")
      
    // form can be used only if scripts are enabled
    
    jQuery("form.wp-kneaver.webhooks").find("input[type=submit]").removeAttr('disabled');
    jQuery("form.wp-kneaver.webhooks").find("button[type=submit]").removeAttr('disabled');
     
    jQuery("form.wp-kneaver.webhooks").submit(function ()
    {
        // this points to wp-kneaver forms
        jQuery("input[type=submit]").attr('disabled','disabled');
        jQuery("button[type=submit]").attr('disabled','disabled');
	jQuery("#FormMessage").html("Wait while your request is submitted<br /><i class=\"fa fa-spinner fa-spin fa-5x\"></i>").removeClass('alert-warning').addClass('alert alert-success');
        var str = jQuery(this).serialize(); // Serialize the data for the POST-request
        jQuery.support.cors = true;
        jQuery.ajax(
        {
            type: "POST",
            url: Target || ( 'https://h.kneaver.com'),
            data: str,
            // has to be quite long,
            timeout: 60*1000,
            success: function ( data, textStatus, jqXHR)
            {
                jQuery("input[type=submit]").removeAttr('disabled');
                jQuery("button[type=submit]").removeAttr('disabled');
                jQuery("#FormMessage").ajaxComplete(function (event, request, settings)
                {
		    // result = '<div class="alert alert-success">Message was sent to website administrator, thank you!</div>';
		    result = data;
                    // option 2 : show result and move to space automatically
                    jQuery(this).html( MsgOK || result).removeClass('alert-warning').addClass('alert alert-success');
                    // there is no id=fields, may be use fieldset jQuery("#fields").hide();
                });
            },
            error: function ( jqXHR, textStatus, errorThrown )
            {
                jQuery("input[type=submit]").removeAttr('disabled');
                jQuery("button[type=submit]").removeAttr('disabled');
                if ( jqXHR.status == 200)
                    return;
                
                var msg = "Unknown Error";
                if ( jqXHR.status == 401)
                {
                    jQuery("input[type=submit]").removeAttr('disabled');
                    jQuery("button[type=submit]").removeAttr('disabled');
                    msg = "Credential Errors";
                }
                else
                if ( jqXHR.status == 500)
                    msg = "Your request has been submitted, we have already several requests queued and start your space now. We will send you your invite as soon as spaces more are available";
                else
                {
                    jQuery("input[type=submit]").removeAttr('disabled');
                    jQuery("button[type=submit]").removeAttr('disabled');
                    msg = jqXHR.responseText;
                }

                // TODO dangerous this could return PHP errors
                if ( !msg)
                    msg = textStatus + jqXHR.status;
                // this will be "Error" + 500
                
                if ( !msg)
                    msg = errorThrown + jqXHR.status;
                    
                jQuery("#FormMessage").html( MsgFailed || msg).removeClass('alert-success').addClass('alert alert-warning');
                
            }
        });
        return false;
    });
});
};
