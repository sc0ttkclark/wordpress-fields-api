<?php
/**
 * Writing settings administration panel.
 *
 * @package WordPress
 * @subpackage Administration
 */

/** WordPress Administration Bootstrap */
// @todo Remove WP Fields API modification
if ( !defined('ABSPATH') )
	die('-1');

if ( ! current_user_can( 'manage_options' ) )
	wp_die( __( 'You do not have sufficient permissions to manage options for this site.' ) );

$title = __('Writing Settings');
$parent_file = 'options-general.php';

get_current_screen()->add_help_tab( array(
	'id'      => 'overview',
	'title'   => __('Overview'),
	'content' => '<p>' . __('You can submit content in several different ways; this screen holds the settings for all of them. The top section controls the editor within the dashboard, while the rest control external publishing methods. For more information on any of these methods, use the documentation links.') . '</p>' .
	             '<p>' . __('You must click the Save Changes button at the bottom of the screen for new settings to take effect.') . '</p>',
) );

/** This filter is documented in wp-admin/options.php */
if ( apply_filters( 'enable_post_by_email_configuration', true ) ) {
	get_current_screen()->add_help_tab( array(
		'id'      => 'options-postemail',
		'title'   => __( 'Post Via Email' ),
		'content' => '<p>' . __( 'Post via email settings allow you to send your WordPress install an email with the content of your post. You must set up a secret email account with POP3 access to use this, and any mail received at this address will be posted, so it&#8217;s a good idea to keep this address very secret.' ) . '</p>',
	) );
}

/** This filter is documented in wp-admin/options-writing.php */
if ( apply_filters( 'enable_update_services_configuration', true ) ) {
	get_current_screen()->add_help_tab( array(
		'id'      => 'options-services',
		'title'   => __( 'Update Services' ),
		'content' => '<p>' . __( 'If desired, WordPress will automatically alert various services of your new posts.' ) . '</p>',
	) );
}

get_current_screen()->set_help_sidebar(
	'<p><strong>' . __('For more information:') . '</strong></p>' .
	'<p>' . __('<a href="https://codex.wordpress.org/Settings_Writing_Screen" target="_blank">Documentation on Writing Settings</a>') . '</p>' .
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

		/**
		 * @var $wp_fields WP_Fields_API
		 */
		global $wp_fields;

		// Get form
		$form = $wp_fields->get_form( 'settings', 'settings-writing' );

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
