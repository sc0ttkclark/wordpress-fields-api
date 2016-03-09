<?php
/**
 * Plugin Name: Fields API
 * Plugin URI: https://github.com/sc0ttkclark/wordpress-fields-api
 * Description: WordPress Fields API prototype and proposal for WordPress core
 * Version: 0.0.7 Alpha
 * Author: Scott Kingsley Clark
 * Author URI: http://scottkclark.com/
 * License: GPL2+
 * GitHub Plugin URI: https://github.com/sc0ttkclark/wordpress-fields-api
 * GitHub Branch: master
 * Requires WP: 4.4
 */

// @todo Remove this when done testing
if ( defined( 'WP_FIELDS_API_TESTING' ) && WP_FIELDS_API_TESTING && ! empty( $_GET['no-fields-api'] ) ) {
	return;
}

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
define( 'WP_FIELDS_API_URL', plugin_dir_url( __FILE__ ) );

/**
 * On `plugins_loaded`, create an instance of the Fields API manager class.
 */
function _wp_fields_api_include() {

	// Bail if we're already in WP core (depending on the name used)
	if ( class_exists( 'WP_Fields_API' ) || class_exists( 'Fields_API' ) ) {
		return;
	}

	if ( ! defined( 'WP_FIELDS_API_EXAMPLES' ) ) {
		define( 'WP_FIELDS_API_EXAMPLES', false );
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

	// Meta boxes
	add_action( 'add_meta_boxes', array( 'WP_Fields_API_Meta_Box_Section', 'add_meta_boxes' ), 10, 2 );

	// Post
	require_once( $implementation_dir . 'class-wp-fields-api-form-post.php' );

	WP_Fields_API_Form_Post::register( 'post', 'post-edit' );

	// Term
	require_once( $implementation_dir . 'class-wp-fields-api-form-term.php' );
	require_once( $implementation_dir . 'class-wp-fields-api-form-term-add.php' );

	WP_Fields_API_Form_Term::register( 'term', 'term-edit' );
	WP_Fields_API_Form_Term_Add::register( 'term', 'term-add' );

	// User
	require_once( $implementation_dir . 'class-wp-fields-api-form-user-edit.php' );

	WP_Fields_API_Form_User_Edit::register( 'user', 'user-edit' );

	// Comment
	require_once( $implementation_dir . 'class-wp-fields-api-form-comment.php' );

	WP_Fields_API_Form_Comment::register( 'comment', 'comment-edit' );

	// Settings
	require_once( $implementation_dir . 'settings/class-wp-fields-api-form-settings.php' );
	require_once( $implementation_dir . 'settings/class-wp-fields-api-form-settings-general.php' );
	require_once( $implementation_dir . 'settings/class-wp-fields-api-form-settings-writing.php' );
	require_once( $implementation_dir . 'settings/class-wp-fields-api-form-settings-reading.php' );
	require_once( $implementation_dir . 'settings/class-wp-fields-api-form-settings-permalink.php' );

	WP_Fields_API_Form_Settings_General::register( 'settings', 'settings-general' );
	WP_Fields_API_Form_Settings_Writing::register( 'settings', 'settings-writing' );
	WP_Fields_API_Form_Settings_Reading::register( 'settings', 'settings-reading' );
	WP_Fields_API_Form_Settings_Permalink::register( 'settings', 'settings-permalink' );

	// Settings API compatibility
	require_once( $implementation_dir . 'settings/class-wp-fields-api-settings-api.php' );

	// Run Settings API compatibility (has it's own hooks)
	new WP_Fields_API_Settings_API;

}
add_action( 'fields_register', '_wp_fields_api_implementations', 5 );

// Post
add_action( 'load-post.php', '_wp_fields_api_load_include', 999 );

// Term
add_action( 'load-edit-tags.php', '_wp_fields_api_load_include', 999 );

// User
add_action( 'load-user-edit.php', '_wp_fields_api_load_include', 999 );
add_action( 'load-profile.php', '_wp_fields_api_load_include', 999 );

// Comment
add_action( 'load-comment.php', '_wp_fields_api_load_include', 999 );

// Settings
add_action( 'load-options-general.php', '_wp_fields_api_load_include', 999 );
add_action( 'load-options-writing.php', '_wp_fields_api_load_include', 999 );
add_action( 'load-options-reading.php', '_wp_fields_api_load_include', 999 );
add_action( 'load-options-permalink.php', '_wp_fields_api_load_include', 999 );

function _wp_fields_api_load_include() {

	global $pagenow;

	static $overridden;

	if ( empty( $overridden ) ) {
		$overridden = array();
	}

	$load_path = WP_FIELDS_API_DIR . 'implementation/wp-admin/';

	if ( file_exists( $load_path . $pagenow ) && ! in_array( $pagenow, $overridden ) ) {
		$overridden[] = $pagenow;

		_wp_fields_api_override_compatibility();

		// Load our override
		require_once( $load_path . $pagenow );

		// Bail on original core file, don't run the rest
		exit;
	}

}

/**
 * Used to maintain compatibiltiy on all overrides
 */
function _wp_fields_api_override_compatibility() {

	global $typenow, $pagenow, $taxnow;

	/*
	 * The following hooks are fired to ensure backward compatibility.
	 * In all other cases, 'load-' . $pagenow should be used instead.
	 */
	if ( $typenow == 'page' ) {
		if ( $pagenow == 'post-new.php' )
			do_action( 'load-page-new.php' );
		elseif ( $pagenow == 'post.php' )
			do_action( 'load-page.php' );
	}  elseif ( $pagenow == 'edit-tags.php' ) {
		if ( $taxnow == 'category' )
			do_action( 'load-categories.php' );
		elseif ( $taxnow == 'link_category' )
			do_action( 'load-edit-link-categories.php' );
	}

	if ( ! empty( $_REQUEST['action'] ) ) {
		/**
		 * Fires when an 'action' request variable is sent.
		 *
		 * The dynamic portion of the hook name, `$_REQUEST['action']`,
		 * refers to the action derived from the `GET` or `POST` request.
		 *
		 * @since 2.6.0
		 */
		do_action( 'admin_action_' . $_REQUEST['action'] );
	}

}
