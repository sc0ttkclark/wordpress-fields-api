<?php
/**
 * General settings administration panel.
 *
 * @package WordPress
 * @subpackage Administration
 */
/** WordPress Administration Bootstrap */

// @todo Remove WP Fields API modification
if ( !defined('ABSPATH') )
	die('-1');
global $submenu_file, $parent_file, $title, $pagenow; // @todo Remove WP Fields API modification
$submenu_file = $parent_file; // @todo Remove WP Fields API modification

/** WordPress Translation Install API */
require_once( ABSPATH . 'wp-admin/includes/translation-install.php' );

if ( ! current_user_can( 'manage_options' ) )
	wp_die( __( 'You do not have sufficient permissions to manage options for this site.' ) );

/**
 * WP Fields API implementation >>>
 */

/**
 * @var $wp_fields WP_Fields_API
 */
global $wp_fields;

// Get form
$form = $wp_fields->get_form( 'settings', 'general' );

/**
 * <<< WP Fields API implementation
 */

$title = __('General Settings');
$parent_file = 'options-general.php';
/* translators: date and time format for exact current time, mainly about timezones, see http://php.net/date */
$timezone_format = _x('Y-m-d H:i:s', 'timezone date format');

add_action('admin_head', 'options_general_add_js');

$options_help = '<p>' . __('The fields on this screen determine some of the basics of your site setup.') . '</p>' .
                '<p>' . __('Most themes display the site title at the top of every page, in the title bar of the browser, and as the identifying name for syndicated feeds. The tagline is also displayed by many themes.') . '</p>';

if ( ! is_multisite() ) {
	$options_help .= '<p>' . __('The WordPress URL and the Site URL can be the same (example.com) or different; for example, having the WordPress core files (example.com/wordpress) in a subdirectory instead of the root directory.') . '</p>' .
	                 '<p>' . __('If you want site visitors to be able to register themselves, as opposed to by the site administrator, check the membership box. A default user role can be set for all new users, whether self-registered or registered by the site admin.') . '</p>';
}

$options_help .= '<p>' . __( 'You can set the language, and the translation files will be automatically downloaded and installed (available if your filesystem is writable).' ) . '</p>' .
                 '<p>' . __( 'UTC means Coordinated Universal Time.' ) . '</p>' .
                 '<p>' . __( 'You must click the Save Changes button at the bottom of the screen for new settings to take effect.' ) . '</p>';

get_current_screen()->add_help_tab( array(
	'id'      => 'overview',
	'title'   => __('Overview'),
	'content' => $options_help,
) );

get_current_screen()->set_help_sidebar(
	'<p><strong>' . __('For more information:') . '</strong></p>' .
	'<p>' . __('<a href="https://codex.wordpress.org/Settings_General_Screen" target="_blank">Documentation on General Settings</a>') . '</p>' .
	'<p>' . __('<a href="https://wordpress.org/support/" target="_blank">Support Forums</a>') . '</p>'
);

include( ABSPATH . 'wp-admin/admin-header.php' );
?>
<div class="wrap">

	<h1><?php echo esc_html( $title ); ?></h1>

	<form method="post" action="options.php" novalidate="novalidate">
		<?php
		/**
		 * WP Fields API implementation >>>
		 */

		// WP_Fields_API Modifications
		// Render form controls
		$form->maybe_render();

		/**
		 * <<< WP Fields API implementation
		 */
		?>

		<?php submit_button(); ?>
	</form>

</div>

<?php include( ABSPATH . 'wp-admin/admin-footer.php' ); ?>
