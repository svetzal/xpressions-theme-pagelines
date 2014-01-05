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

		if (function_exists("register_sidebar_widget")) {
			register_sidebar_widget('Xpressions Member Expiry', 'widget_member_expiration');
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

add_action('after_setup_theme', 'remove_admin_bar');

function remove_admin_bar() {
	if (!current_user_can('administrator') && !is_admin()) {
		show_admin_bar(false);
	}
}

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
	return $result;
}

function widget_member_expiration($args) {
	extract($args);
	echo $before_widget;
  // We don't really want a title... but if we did... -sv
  // echo $before_title . "Membership Expiry" . $after_title;
	echo extract_member_expiration();
	echo $after_widget;
}

function tag_member_expiration($args) {
	return extract_member_expiration();
}

add_shortcode('xpressionsExpiry', 'tag_member_expiration');
