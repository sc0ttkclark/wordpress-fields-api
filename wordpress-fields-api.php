<?php
/*
Plugin Name: Fields API
Plugin URI: https://github.com/sc0ttkclark/wordpress-fields-api
Description: WordPress Fields API prototype and proposal for WordPress core
Version: 0.1
Author: Scott Kingsley Clark
Author URI: http://scottkclark.com/
License: GPL2+
GitHub Plugin URI: https://github.com/sc0ttkclark/wordpress-fields-api
GitHub Branch: master
Requires WP: 4.4
*/

/**
 * @package    WordPress
 * @subpackage Fields_API
 *
 * @codeCoverageIgnore
 */

/**
 * The absolute server path to the fields API directory.
 */
define( 'WP_FIELDS_API_DIR', plugin_dir_path( __FILE__ ) );

/**
 * On `plugins_loaded`, create an instance of the Fields API manager class.
 */
function _wp_fields_api_include() {

	// Bail if we're already in WP core (depending on the name used)
	if ( class_exists( 'WP_Fields_API' ) || class_exists( 'Fields_API' ) ) {
		return;
	}

	require_once( WP_FIELDS_API_DIR . 'implementation/wp-includes/fields-api/class-wp-fields-api.php' );

	// Init Fields API class
	$GLOBALS['wp_fields'] = WP_Fields_API::get_instance();

}

add_action( 'plugins_loaded', '_wp_fields_api_include', 8 );

/**
 * Implement Fields API Customizer instead of WP Core Customizer.
 */
function _wp_fields_api_customize_include() {

	if ( ! ( ( isset( $_REQUEST['wp_customize'] ) && 'on' == $_REQUEST['wp_customize'] ) || ( is_admin() && 'customize.php' == basename( $_SERVER['PHP_SELF'] ) ) ) ) {
		return;
	}

	require_once( WP_FIELDS_API_DIR . 'implementation/wp-includes/class-wp-customize-manager.php' );

	// Init Customize class
	$GLOBALS['wp_customize'] = new WP_Customize_Manager;

}

/*remove_action( 'plugins_loaded', '_wp_customize_include' );
add_action( 'plugins_loaded', '_wp_fields_api_customize_include', 9 );*/

/**
 * Include Implementations
 */
function _wp_fields_api_implementations() {

	$implementation_dir = WP_FIELDS_API_DIR . 'implementation/wp-includes/fields-api/forms/';

	// User
	require_once( $implementation_dir . 'class-wp-fields-api-form-user-edit.php' );

	WP_Fields_API_Form_User_Edit::register( 'user', 'user-edit' );

	// Term
	require_once( $implementation_dir . 'class-wp-fields-api-form-term.php' );
	require_once( $implementation_dir . 'class-wp-fields-api-form-term-add.php' );

	WP_Fields_API_Form_Term::register( 'term', 'term-edit' );
	WP_Fields_API_Form_Term_Add::register( 'term', 'term-add' );

	// Settings
	require_once( $implementation_dir . 'settings/class-wp-fields-api-form-settings-general.php' );

	WP_Fields_API_Form_Settings_General::register( 'settings', 'general' );

	// Settings API compatibility
	require_once( $implementation_dir . 'settings/class-wp-fields-api-settings-api.php' );

	// Run Settings API compatibility (has it's own hooks)
	new WP_Fields_API_Settings_API;

	// Post / comment editor support for meta boxes
	add_action( 'add_meta_boxes', array( 'WP_Fields_API_Meta_Box_Section', 'add_meta_boxes' ) );

}
add_action( 'fields_register', '_wp_fields_api_implementations', 5 );

/**
 * Implement Fields API User edit to override WP Core.
 */
function _wp_fields_api_user_edit_include() {

	static $overridden;

	if ( empty( $overridden ) ) {
		$overridden = true;

		// Load our overrides
		//require_once( WP_FIELDS_API_DIR . 'implementation/wp-admin/includes/user.php' );
		require_once( WP_FIELDS_API_DIR . 'implementation/wp-admin/user-edit.php' );

		// Bail on original core file, don't run the rest
		exit;
	}

}
add_action( 'load-user-edit.php', '_wp_fields_api_user_edit_include' );
add_action( 'load-profile.php', '_wp_fields_api_user_edit_include' );


/**
 * Implement Fields API Term to override WP Core.
 */
function _wp_fields_api_term_include() {

	static $overridden;

	if ( empty( $overridden ) ) {
		$overridden = true;

		// Load our overrides
		require_once( WP_FIELDS_API_DIR . 'implementation/wp-admin/edit-tags.php' );

		// Bail on original core file, don't run the rest
		exit;
	}

}
add_action( 'load-edit-tags.php', '_wp_fields_api_term_include' );

/**
 * Implement Fields API Term to override WP Core.
 */
function _wp_fields_api_settings_general_include() {

	static $overridden;

	if ( empty( $overridden ) ) {
		$overridden = true;

		// Load our overrides
		require_once( WP_FIELDS_API_DIR . 'implementation/wp-admin/settings-general.php' );

		// Bail on original core file, don't run the rest
		exit;
	}

}
add_action( 'load-options-general.php', '_wp_fields_api_settings_general_include' );