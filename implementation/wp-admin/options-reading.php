<?php
/**
 * Reading settings administration panel.
 *
 * @package WordPress
 * @subpackage Administration
 */

/** WordPress Administration Bootstrap */
//require_once( dirname( __FILE__ ) . '/admin.php' ); // @todo Remove WP Fields API modification

// @todo Remove WP Fields API modification
if ( !defined('ABSPATH') )
	die('-1');

if ( ! current_user_can( 'manage_options' ) )
	wp_die( __( 'You do not have sufficient permissions to manage options for this site.' ) );

$title = __( 'Reading Settings' );
$parent_file = 'options-general.php';

add_action('admin_head', 'options_reading_add_js');

get_current_screen()->add_help_tab( array(
	'id'      => 'overview',
	'title'   => __('Overview'),
	'content' => '<p>' . __('This screen contains the settings that affect the display of your content.') . '</p>' .
	             '<p>' . sprintf(__('You can choose what&#8217;s displayed on the front page of your site. It can be posts in reverse chronological order (classic blog), or a fixed/static page. To set a static home page, you first need to create two <a href="%s">Pages</a>. One will become the front page, and the other will be where your posts are displayed.'), 'post-new.php?post_type=page') . '</p>' .
	             '<p>' . __('You can also control the display of your content in RSS feeds, including the maximum numbers of posts to display and whether to show full text or a summary.') . '</p>' .
	             '<p>' . __('You must click the Save Changes button at the bottom of the screen for new settings to take effect.') . '</p>',
) );

get_current_screen()->add_help_tab( array(
	'id'      => 'site-visibility',
	'title'   => has_action( 'blog_privacy_selector' ) ? __( 'Site Visibility' ) : __( 'Search Engine Visibility' ),
	'content' => '<p>' . __( 'You can choose whether or not your site will be crawled by robots, ping services, and spiders. If you want those services to ignore your site, click the checkbox next to &#8220;Discourage search engines from indexing this site&#8221; and click the Save Changes button at the bottom of the screen. Note that your privacy is not complete; your site is still visible on the web.' ) . '</p>' .
	             '<p>' . __( 'When this setting is in effect, a reminder is shown in the At a Glance box of the Dashboard that says, &#8220;Search Engines Discouraged,&#8221; to remind you that your site is not being crawled.' ) . '</p>',
) );

get_current_screen()->set_help_sidebar(
	'<p><strong>' . __('For more information:') . '</strong></p>' .
	'<p>' . __('<a href="https://codex.wordpress.org/Settings_Reading_Screen" target="_blank">Documentation on Reading Settings</a>') . '</p>' .
	'<p>' . __('<a href="https://wordpress.org/support/" target="_blank">Support Forums</a>') . '</p>'
);

include( ABSPATH . 'wp-admin/admin-header.php' );
?>

<div class="wrap">
	<h1><?php echo esc_html( $title ); ?></h1>

	<form method="post" action="options.php">
		<?php
		/**
		 * WP Fields API implementation >>>
		 */

		/**
		 * @var $wp_fields WP_Fields_API
		 */
		global $wp_fields;

		// Get form
		$form = $wp_fields->get_form( 'settings-reading' );

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
