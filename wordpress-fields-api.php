<?php
/*
Plugin Name: WordPress Fields API
Plugin URI: https://github.com/sc0ttkclark/wordpress-fields-api
Description: WordPress Fields API prototype and proposal
Version: 0.0.1
Author: Scott Kingsley Clark
Author URI: http://scottkclark.com/
License: GPL2+
GitHub Plugin URI: https://github.com/sc0ttkclark/wordpress-fields-api
GitHub Branch: master
Requires WP: 4.1
*/

/**
 * @package WordPress
 * @subpackage Fields_API
 */

define( 'WP_FIELDS_API_DIR', plugin_dir_path( __FILE__ ) );

/**
 * Include class and setup global
 */
function _wp_fields_api_include() {

    require_once( WP_FIELDS_API_DIR . 'includes/class-wp-fields-api.php' );

	// Init Customize class
	$GLOBALS['wp_fields'] = new WP_Fields_API;

}
add_action( 'plugins_loaded', '_wp_fields_api_include' );