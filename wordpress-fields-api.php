<?php
/**
 * Plugin Name: Fields API
 * Plugin URI: https://github.com/sc0ttkclark/wordpress-fields-api
 * Description: WordPress Fields API prototype and proposal for WordPress core
 * Version: 0.1.0 Alpha
 * Author: Scott Kingsley Clark
 * Author URI: http://scottkclark.com/
 * License: GPL2+
 * GitHub Plugin URI: https://github.com/sc0ttkclark/wordpress-fields-api
 * GitHub Branch: develop
 * Requires WP: 4.6
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

class WP_Fields_API_v_0_1_0 {
	const VERSION = '0.1.0';
	const PRIORITY = 9999;

	public static $instance = null;
	public static function initialize() {
		if( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	// Force creation of this class (effectively try to load this class again)
	// for debug (specifically for testing version handling)
	public static function _debug_force_initialize( $version = self::VERSION, $priority = self::PRIORITY ) {
		return new self( $version, $priority );
	}

	public $version = '';
	public $priority = '';

	private function __construct( $version = self::VERSION, $priority = self::PRIORITY ) {
		$this->version = $version;
		$this->priority = $priority;

		add_action( 'plugins_loaded', array( $this, 'attempt_include' ), $this->priority );
	}

	public function attempt_include() {
		if ( class_exists( 'WP_Fields_API' ) || class_exists( 'Fields_API' ) ) {
			add_action( 'admin_notices', array( $this, 'warn_about_multiple_copies' ) );
		}
	}

	public function warn_about_multiple_copies() {
		?>
		<div class="notice notice-warning">
			<p>
				A plugin is trying to include an older version
				(<?php echo $this->version; ?> <= <?php echo WP_FIELDS_API_PLUGIN_VERSION; ?>)
				of the <strong>WP Fields API</strong>.
			</p>
			<p>
				This might not cause problems, but
				you should contact the plugin author and ask
				them to update their plugin (trying to load the Fields API from
				<code><?php echo __FILE__; ?></code>).
			</p>
		</div>
		<?php
	}
}

function _wp_fields_api_warn_multiple_copies() {
	$version = '0.1.0';
	?>
	<div class="notice notice-warning">
		<p>
			A plugin is trying to include an older version
			(<?php echo $version; ?> <= <?php echo WP_FIELDS_API_PLUGIN_VERSION; ?>)
			of the <strong>WP Fields API</strong>.
		</p>
		<p>
			This might not cause problems, but
			you should contact the plugin author and ask
			them to update their plugin (trying to load the Fields API from
			<code><?php echo __FILE__; ?></code>).
		</p>
	</div>
	<?php
}

/**
 * On `plugins_loaded`, create an instance of the Fields API manager class.
 */
function _wp_fields_api_include( $api_version = '0.1.0' ) {

	// Bail if we're already in WP core (depending on the name used)
	if ( class_exists( 'WP_Fields_API' ) || class_exists( 'Fields_API' ) ) {
		add_action( 'admin_notices', '_wp_fields_api_warn_multiple_copies' );

		return;
	}

	// Set version number
	define( 'WP_FIELDS_API_PLUGIN_VERSION', $api_version );

	require_once( WP_FIELDS_API_DIR . 'implementation/wp-includes/fields-api/class-wp-fields-api.php' );

	// Init Fields API class
	$GLOBALS['wp_fields'] = WP_Fields_API::get_instance();

	if ( defined( 'WP_FIELDS_API_EXAMPLES' ) && WP_FIELDS_API_EXAMPLES ) {
		include_once( WP_FIELDS_API_DIR . 'docs/examples/option/_starter.php' );
		include_once( WP_FIELDS_API_DIR . 'docs/examples/term/_starter.php' );
		include_once( WP_FIELDS_API_DIR . 'docs/examples/user/_starter.php' );
		include_once( WP_FIELDS_API_DIR . 'docs/examples/user/address.php' );
	}

}

add_action( 'plugins_loaded', '_wp_fields_api_include', 8, 0 );

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
	global $wp_fields;

	$implementation_dir = WP_FIELDS_API_DIR . 'implementation/wp-includes/fields-api/forms/';

	// Meta boxes
	add_action( 'add_meta_boxes', array( 'WP_Fields_API_Meta_Box_Section', 'add_meta_boxes' ), 10, 2 );

	// Post
	require_once( $implementation_dir . 'class-wp-fields-api-form-post.php' );

	$wp_fields->register_form_type( 'post-edit', 'WP_Fields_API_Form_Post' );
	$wp_fields->add_form( 'post', 'post-edit', array(
		'type' => 'post-edit',
		'object_subtype' => 'post-edit',
	) );

	// Term
	require_once( $implementation_dir . 'class-wp-fields-api-form-term.php' );
	require_once( $implementation_dir . 'class-wp-fields-api-form-term-add.php' );

	$wp_fields->register_form_type( 'term-edit', 'WP_Fields_API_Form_Term' );
	$wp_fields->register_form_type( 'term-add', 'WP_Fields_API_Form_Term_Add' );

	$wp_fields->add_form( 'term', 'term-edit', array(
		'type' => 'term-edit',
		'object_subtype' => 'term-edit',
	) );

	$wp_fields->add_form( 'term', 'term-add', array(
		'type' => 'term-add',
		'object_subtype' => 'term-add',
	) );

	// User
	require_once( $implementation_dir . 'class-wp-fields-api-form-user-edit.php' );

	$wp_fields->register_form_type( 'user-edit', 'WP_Fields_API_Form_User_Edit' );
	$wp_fields->add_form( 'user', 'user-edit', array(
		'type' => 'user-edit',
		'object_subtype' => 'user-edit',
	) );

	// Comment
	require_once( $implementation_dir . 'class-wp-fields-api-form-comment.php' );

	$wp_fields->register_form_type( 'comment-edit', 'WP_Fields_API_Form_Comment' );
	$wp_fields->add_form( 'comment', 'comment-edit', array(
		'type' => 'comment-edit',
		'object_subtype' => 'comment-edit',
	) );



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
