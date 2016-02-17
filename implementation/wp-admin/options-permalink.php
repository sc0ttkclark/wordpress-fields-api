<?php
/**
 * Permalink Settings Administration Screen.
 *
 * @package WordPress
 * @subpackage Administration
 */

/** WordPress Administration Bootstrap */
// @todo Remove WP Fields API modification
if ( !defined('ABSPATH') )
	die('-1');

global $wp_rewrite, $is_nginx;

if ( ! current_user_can( 'manage_options' ) )
	wp_die( __( 'You do not have sufficient permissions to manage options for this site.' ) );

$title = __('Permalink Settings');
$parent_file = 'options-general.php';

get_current_screen()->add_help_tab( array(
	'id'      => 'overview',
	'title'   => __('Overview'),
	'content' => '<p>' . __('Permalinks are the permanent URLs to your individual pages and blog posts, as well as your category and tag archives. A permalink is the web address used to link to your content. The URL to each post should be permanent, and never change &#8212; hence the name permalink.') . '</p>' .
	             '<p>' . __( 'This screen allows you to choose your permalink structure. You can choose from common settings or create custom URL structures.' ) . '</p>' .
	             '<p>' . __('You must click the Save Changes button at the bottom of the screen for new settings to take effect.') . '</p>',
) );

get_current_screen()->add_help_tab( array(
	'id'      => 'permalink-settings',
	'title'   => __('Permalink Settings'),
	'content' => '<p>' . __( 'Permalinks can contain useful information, such as the post date, title, or other elements. You can choose from any of the suggested permalink formats, or you can craft your own if you select Custom Structure.' ) . '</p>' .
	             '<p>' . __( 'If you pick an option other than Plain, your general URL path with structure tags (terms surrounded by <code>%</code>) will also appear in the custom structure field and your path can be further modified there.' ) . '</p>' .
	             '<p>' . __('When you assign multiple categories or tags to a post, only one can show up in the permalink: the lowest numbered category. This applies if your custom structure includes <code>%category%</code> or <code>%tag%</code>.') . '</p>' .
	             '<p>' . __('You must click the Save Changes button at the bottom of the screen for new settings to take effect.') . '</p>',
) );

get_current_screen()->add_help_tab( array(
	'id'      => 'custom-structures',
	'title'   => __('Custom Structures'),
	'content' => '<p>' . __('The Optional fields let you customize the &#8220;category&#8221; and &#8220;tag&#8221; base names that will appear in archive URLs. For example, the page listing all posts in the &#8220;Uncategorized&#8221; category could be <code>/topics/uncategorized</code> instead of <code>/category/uncategorized</code>.') . '</p>' .
	             '<p>' . __('You must click the Save Changes button at the bottom of the screen for new settings to take effect.') . '</p>',
) );

get_current_screen()->set_help_sidebar(
	'<p><strong>' . __('For more information:') . '</strong></p>' .
	'<p>' . __('<a href="https://codex.wordpress.org/Settings_Permalinks_Screen" target="_blank">Documentation on Permalinks Settings</a>') . '</p>' .
	'<p>' . __('<a href="https://codex.wordpress.org/Using_Permalinks" target="_blank">Documentation on Using Permalinks</a>') . '</p>' .
	'<p>' . __('<a href="https://wordpress.org/support/" target="_blank">Support Forums</a>') . '</p>'
);

add_filter('admin_head', 'options_permalink_add_js');

$home_path = get_home_path();
$iis7_permalinks = iis7_supports_permalinks();
$permalink_structure = get_option( 'permalink_structure' );

$prefix = $blog_prefix = '';
if ( ! got_url_rewrite() )
	$prefix = '/index.php';

/**
 * In a subdirectory configuration of multisite, the `/blog` prefix is used by
 * default on the main site to avoid collisions with other sites created on that
 * network. If the `permalink_structure` option has been changed to remove this
 * base prefix, WordPress core can no longer account for the possible collision.
 */
if ( is_multisite() && ! is_subdomain_install() && is_main_site() && 0 === strpos( $permalink_structure, '/blog/' ) ) {
	$blog_prefix = '/blog';
}

if ( isset($_POST['permalink_structure']) || isset($_POST['category_base']) ) {
	check_admin_referer('update-permalink');

	if ( isset( $_POST['permalink_structure'] ) ) {
		if ( isset( $_POST['selection'] ) && 'custom' != $_POST['selection'] )
			$permalink_structure = $_POST['selection'];
		else
			$permalink_structure = $_POST['permalink_structure'];

		if ( ! empty( $permalink_structure ) ) {
			$permalink_structure = preg_replace( '#/+#', '/', '/' . str_replace( '#', '', $permalink_structure ) );
			if ( $prefix && $blog_prefix )
				$permalink_structure = $prefix . preg_replace( '#^/?index\.php#', '', $permalink_structure );
			else
				$permalink_structure = $blog_prefix . $permalink_structure;
		}
		$wp_rewrite->set_permalink_structure( $permalink_structure );
	}

	if ( isset( $_POST['category_base'] ) ) {
		$category_base = $_POST['category_base'];
		if ( ! empty( $category_base ) )
			$category_base = $blog_prefix . preg_replace('#/+#', '/', '/' . str_replace( '#', '', $category_base ) );
		$wp_rewrite->set_category_base( $category_base );
	}

	if ( isset( $_POST['tag_base'] ) ) {
		$tag_base = $_POST['tag_base'];
		if ( ! empty( $tag_base ) )
			$tag_base = $blog_prefix . preg_replace('#/+#', '/', '/' . str_replace( '#', '', $tag_base ) );
		$wp_rewrite->set_tag_base( $tag_base );
	}

	wp_redirect( admin_url( 'options-permalink.php?settings-updated=true' ) );
	exit;
}

$category_base       = get_option( 'category_base' );
$tag_base            = get_option( 'tag_base' );
$update_required     = false;

if ( $iis7_permalinks ) {
	if ( ( ! file_exists($home_path . 'web.config') && win_is_writable($home_path) ) || win_is_writable($home_path . 'web.config') )
		$writable = true;
	else
		$writable = false;
} elseif ( $is_nginx ) {
	$writable = false;
} else {
	if ( ( ! file_exists( $home_path . '.htaccess' ) && is_writable( $home_path ) ) || is_writable( $home_path . '.htaccess' ) ) {
		$writable = true;
	} else {
		$writable = false;
		$existing_rules  = array_filter( extract_from_markers( $home_path . '.htaccess', 'WordPress' ) );
		$new_rules       = array_filter( explode( "\n", $wp_rewrite->mod_rewrite_rules() ) );
		$update_required = ( $new_rules !== $existing_rules );
	}
}

if ( $wp_rewrite->using_index_permalinks() )
	$usingpi = true;
else
	$usingpi = false;

flush_rewrite_rules();

require( ABSPATH . 'wp-admin/admin-header.php' );

if ( ! empty( $_GET['settings-updated'] ) ) : ?>
	<div id="message" class="updated notice is-dismissible"><p><?php
			if ( ! is_multisite() ) {
				if ( $iis7_permalinks ) {
					if ( $permalink_structure && ! $usingpi && ! $writable ) {
						_e('You should update your web.config now.');
					} elseif ( $permalink_structure && ! $usingpi && $writable ) {
						_e('Permalink structure updated. Remove write access on web.config file now!');
					} else {
						_e('Permalink structure updated.');
					}
				} elseif ( $is_nginx ) {
					_e('Permalink structure updated.');
				} else {
					if ( $permalink_structure && ! $usingpi && ! $writable && $update_required ) {
						_e('You should update your .htaccess now.');
					} else {
						_e('Permalink structure updated.');
					}
				}
			} else {
				_e('Permalink structure updated.');
			}
			?>
		</p></div>
<?php endif; ?>

<div class="wrap">
	<h1><?php echo esc_html( $title ); ?></h1>

	<form name="form" action="options-permalink.php" method="post">
		<?php
		/**
		 * WP Fields API implementation >>>
		 */

		/**
		 * @var $wp_fields WP_Fields_API
		 */
		global $wp_fields;

		// Get form
		$form = $wp_fields->get_form( 'settings', 'settings-permalink' );

		// Render form controls
		$form->maybe_render();

		/**
		 * <<< WP Fields API implementation
		 */
		?>

		<?php submit_button(); ?>
	</form>

	<?php if ( !is_multisite() ) { ?>
		<?php if ( $iis7_permalinks ) :
			if ( isset($_POST['submit']) && $permalink_structure && ! $usingpi && ! $writable ) :
				if ( file_exists($home_path . 'web.config') ) : ?>
					<p><?php _e('If your <code>web.config</code> file were <a href="https://codex.wordpress.org/Changing_File_Permissions">writable</a>, we could do this automatically, but it isn&#8217;t so this is the url rewrite rule you should have in your <code>web.config</code> file. Click in the field and press <kbd>CTRL + a</kbd> to select all. Then insert this rule inside of the <code>/&lt;configuration&gt;/&lt;system.webServer&gt;/&lt;rewrite&gt;/&lt;rules&gt;</code> element in <code>web.config</code> file.') ?></p>
					<form action="options-permalink.php" method="post">
						<?php wp_nonce_field('update-permalink') ?>
						<p><textarea rows="9" class="large-text readonly" name="rules" id="rules" readonly="readonly"><?php echo esc_textarea( $wp_rewrite->iis7_url_rewrite_rules() ); ?></textarea></p>
					</form>
					<p><?php _e('If you temporarily make your <code>web.config</code> file writable for us to generate rewrite rules automatically, do not forget to revert the permissions after rule has been saved.') ?></p>
				<?php else : ?>
					<p><?php _e('If the root directory of your site were <a href="https://codex.wordpress.org/Changing_File_Permissions">writable</a>, we could do this automatically, but it isn&#8217;t so this is the url rewrite rule you should have in your <code>web.config</code> file. Create a new file, called <code>web.config</code> in the root directory of your site. Click in the field and press <kbd>CTRL + a</kbd> to select all. Then insert this code into the <code>web.config</code> file.') ?></p>
					<form action="options-permalink.php" method="post">
						<?php wp_nonce_field('update-permalink') ?>
						<p><textarea rows="18" class="large-text readonly" name="rules" id="rules" readonly="readonly"><?php echo esc_textarea( $wp_rewrite->iis7_url_rewrite_rules(true) ); ?></textarea></p>
					</form>
					<p><?php _e('If you temporarily make your site&#8217;s root directory writable for us to generate the <code>web.config</code> file automatically, do not forget to revert the permissions after the file has been created.') ?></p>
				<?php endif; ?>
			<?php endif; ?>
		<?php elseif ( $is_nginx ) : ?>
			<p><?php _e( '<a href="https://codex.wordpress.org/Nginx">Documentation on Nginx configuration</a>.' ); ?></p>
		<?php else:
			if ( $permalink_structure && ! $usingpi && ! $writable && $update_required ) : ?>
				<p><?php _e('If your <code>.htaccess</code> file were <a href="https://codex.wordpress.org/Changing_File_Permissions">writable</a>, we could do this automatically, but it isn&#8217;t so these are the mod_rewrite rules you should have in your <code>.htaccess</code> file. Click in the field and press <kbd>CTRL + a</kbd> to select all.') ?></p>
				<form action="options-permalink.php" method="post">
					<?php wp_nonce_field('update-permalink') ?>
					<p><textarea rows="6" class="large-text readonly" name="rules" id="rules" readonly="readonly"><?php echo esc_textarea( $wp_rewrite->mod_rewrite_rules() ); ?></textarea></p>
				</form>
			<?php endif; ?>
		<?php endif; ?>
	<?php } // multisite ?>

</div>

<?php require( ABSPATH . 'wp-admin/admin-footer.php' ); ?>
