<?php

/**
 * Xpressions sub-theme for Pagelines DMS
 *
 * @author Stacey Vetzal <svetzal@gmail.com>
 * @copyright 2014 Stacey Vetzal
 * @license BSD
 *
 * This code is originally derived from Nick's Base Theme
 * https://github.com/bearded-avenger/nicks-base-theme
 *
 * The latest version of this code released by Stacey can be found at
 * https://github.com/svetzal/xpressions-theme-pagelines
 *
 * This code has been donated to the Xpressions group in Toronto, under 
 * the BSD license, allowing them full rights to use and modify as they 
 * see fit.
 *
 * REVISION HISTORY:
 * 2013-11-28 - initial implementation
 * 2013-12-21 - added new sidebar for members, tweaked page template
 * 2014-01-04 - added widget and shortcode to display member expiry
 * 2014-01-05 - added initial EOT logic for member expiry
 * 2014-01-05 - wrapping things up, preparing for release
 */

// Load Framework
require_once( dirname(__FILE__) . '/setup.php' );

// Load support code for Member EOT
require_once( dirname(__FILE__) . '/eot-logic.php' );

// Class-based theme implementation
class XpressionsTheme {

	function __construct() {

		// Constants
		$this->url = sprintf('%s', PL_CHILD_URL);
		$this->dir = sprintf('/%s', PL_CHILD_DIR);

		// Add a filter so we can build a few custom LESS vars
		add_filter( 'pless_vars', array(&$this,'custom_less_vars'));

		$this->init();
	}

	function init(){

		// Run the theme options
		$this->theme_options();

		// Send the user to the Theme Config panel after they activate.
		add_filter('pl_activate_url', array(&$this,'activation_url'));

		if (function_exists('register_sidebar')) {
			register_sidebar(array(
				'name' => 'Wide Advertisements',
				'id' => 'wide-advertisements',
				'description' => 'Use this for placing advertisements on the site.',
				'before_widget' => '<div style="text-align:center">',
				'after_widget' => '</div>'
			));
      register_sidebar(array(
				'name' => 'Members Sidebar',
				'id' => 'members-sidebar',
				'description' => 'Sidebar for members-only pages.',
				'before_widget' => '<div>',
				'after_widget' => '</div>'
      ));
		}

    // Register our expiry widget
    // @todo get this out of global space
		if (function_exists("register_sidebar_widget")) {
			register_sidebar_widget('Xpressions Member Expiry', 'widget_member_expiration');
		}

    // Register our expiry short-code
    // @todo get this out of global space
    if (function_exists("add_shortcode")) {
      add_shortcode('xpressionsExpiry', 'tag_member_expiration');
    }
	}

  // Send the user to the Theme Config panel after they activate. Note 
  // how link=xpr_theme_config is the same name of the array settings. 
  // This must match.
  function activation_url( $url ){

	    $url = home_url() . '?tablink=theme&tabsublink=xpr_theme_config';
	    return $url;
	}

	// Custom LESS Vars
	function custom_less_vars($less){

    // Adding a custom LESS var, use this in LESS as @my-var. In this 
    // example, its linked to a custom color picker in options. We also 
    // must set a default or else it's going to error. pl_hashify must 
    // be used with color pickers so that it appends the # symbol to the 
    // hex code pl_setting is being used because this is a global option 
    // used in the theme
    $less['my-var']   =  pl_setting('my_custom_color') ?  
      pl_hashify(pl_setting('my_custom_color')) : '#07C';

		return $less;
	}

  // WELCOME MESSAGE - HTML content for the welcome/intro option field
	function welcome(){

		ob_start();

		?><div style="font-size:12px;line-height:14px;color:#444;"><p><?php _e('You can have some custom text here.','nicks-base-theme');?></p></div><?php

		return ob_get_clean();
	}

	// Theme Options
	function theme_options(){

		$options = array();

		$options['xpr_theme_config'] = array(
		   'pos'                  => 50,
		   'name'                 => __('Xpressions Base Theme','xpr-base-theme'),
		   'icon'                 => 'icon-rocket',
		   'opts'                 => array(
		   		array(
		       	    'type'        => 'template',
            		'title'       => __('Welcome to Xpressions Theme','xpr-base-theme'),
            		'template'    => $this->welcome()
		       	),
		       	array(
		           'type'         => 'color',
		           'title'        => __('Sample Color','xpr-base-theme'),
		           'key'          => 'my_custom_color',
		           'label'        => __('Sample Color','xpr-base-theme'),
		           'default'      =>'#FFFFFF'
		       	),
		   )
		);
		pl_add_theme_tab($options);
	}

}
new XpressionsTheme;

/*
 * Remove the top admin bar for non-administrative users
 */
add_action('after_setup_theme', 'remove_admin_bar');

function remove_admin_bar() {
	if (!current_user_can('administrator') && !is_admin()) {
		show_admin_bar(false);
	}
}
/* END Remove the top admin bar for non-administrative users */

/*
 * Elements to output human-friendly EOT dates
 */
function extract_member_expiration() {
	$result = "";
	if (function_exists('get_user_field')) {
		$eot = get_user_field("s2member_auto_eot_time");
		if ($eot) {
			$expiry = new DateTime("@$eot");
			$result = "Membership expires " . $expiry->format('d-M-Y');
		} else {
			$result = "Your membership does not expire";
		}
	}

  // HACK! This needs to be removed some time in 2014 once all members 
  // have logged in at least once and had their EOT adjusted
  adjust_member_eot_if_blank();

	return $result;
}

// Widget implementation for sidebars
function widget_member_expiration($args) {
	extract($args);
	echo $before_widget;
  // We don't really want a title... but if we did... -sv
  // echo $before_title . "Membership Expiry" . $after_title;
	echo extract_member_expiration();
	echo $after_widget;
}

// Shortcode implementation for content areas
function tag_member_expiration($args) {
	return extract_member_expiration();
}
/* END Elements to output human-friendly EOT dates */

/*
 * Hook to adjust current user EOT
 *
 * Will only adjust if the current EOT is blank and the user is an 
 * s2member level 2 through 4.
 */
function adjust_member_eot_if_blank() {
  $user = wp_get_current_user();
  if ($user && (in_array('s2member_level2', $user->roles) || in_array('s2member_level3', $user->roles) || in_array('s2member_level4', $user->roles))) {
    $retriever = new XprS2MemberEOTRetriever();
    if ($retriever->hasNoEOT()) {
      $adjuster = new XprEOTAdjuster($retriever);
      if ($adjuster->adjustedEOT()) {
        //update_user_option($user->ID, "s2member_auto_eot_time", $adjuster->adjustedEOT());
      }
    }
  }
}
/* END Hook to adjust member EOT */

/*
 * Hook to adjust user EOT on renewal / changes
 * TODO: Watch that these hooks continue to exist, and their behaviour 
 * doesn't change on future versions of the s2Member plugin
 */
function s2_hooked_adjust_member_eot($args) {
  // Calculate adjusted renewal with base of today + 1 year
  $renew = (new DateTime())->add(new DateInterval("P1Y"));
  $retriever = new XprFixedEOTRetriever($renew);
  $adjuster = new XprEOTAdjuster($retriever);
  update_user_option($args['user_id'], "s2member_auto_eot_time", $adjuster->adjustedEOT());
}

add_action ("ws_plugin__s2member_during_configure_user_registration_front_side", "s2_hooked_adjust_member_eot");
add_action ("ws_plugin__s2member_during_paypal_notify_during_subscr_signup_w_update_vars", "s2_hooked_adjust_member_eot");
add_action ("ws_plugin__s2member_during_paypal_notify_during_subscr_modify", "s2_hooked_adjust_member_eot");
/* END Hook to adjust user EOT on renewal / changes */
