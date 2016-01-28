<?php
/**
 * Edit tag form for inclusion in administration panels.
 *
 * @package WordPress
 * @subpackage Administration
 */
// don't load directly
if ( !defined('ABSPATH') )
	die('-1');

if ( empty($tag_ID) ) { ?>
	<div id="message" class="updated notice is-dismissible"><p><strong><?php _e( 'You did not select an item for editing.' ); ?></strong></p></div>
	<?php
	return;
}

// Back compat hooks
if ( 'category' == $taxonomy ) {
	/**
	 * Fires before the Edit Category form.
	 *
	 * @since 2.1.0
	 * @deprecated 3.0.0 Use {$taxonomy}_pre_edit_form instead.
	 *
	 * @param object $tag Current category term object.
	 */
	do_action( 'edit_category_form_pre', $tag );
} elseif ( 'link_category' == $taxonomy ) {
	/**
	 * Fires before the Edit Link Category form.
	 *
	 * @since 2.3.0
	 * @deprecated 3.0.0 Use {$taxonomy}_pre_edit_form instead.
	 *
	 * @param object $tag Current link category term object.
	 */
	do_action( 'edit_link_category_form_pre', $tag );
} else {
	/**
	 * Fires before the Edit Tag form.
	 *
	 * @since 2.5.0
	 * @deprecated 3.0.0 Use {$taxonomy}_pre_edit_form instead.
	 *
	 * @param object $tag Current tag term object.
	 */
	do_action( 'edit_tag_form_pre', $tag );
}

/**
 * Use with caution, see http://codex.wordpress.org/Function_Reference/wp_reset_vars
 */
wp_reset_vars( array( 'wp_http_referer' ) );

global $wp_http_referer;
$wp_http_referer = remove_query_arg( array( 'action', 'message', 'tag_ID' ), $wp_http_referer );

/** Also used by Edit Tags */
require_once( ABSPATH . 'wp-admin/includes/edit-tag-messages.php' );

/**
 * Fires before the Edit Term form for all taxonomies.
 *
 * The dynamic portion of the hook name, `$taxonomy`, refers to
 * the taxonomy slug.
 *
 * @since 3.0.0
 *
 * @param object $tag      Current taxonomy term object.
 * @param string $taxonomy Current $taxonomy slug.
 */
do_action( "{$taxonomy}_pre_edit_form", $tag, $taxonomy ); ?>

	<div class="wrap">
		<h1><?php echo $tax->labels->edit_item; ?></h1>

		<?php if ( $message ) : ?>
			<div id="message" class="updated">
				<p><strong><?php echo $message; ?></strong></p>
				<?php if ( $wp_http_referer ) { ?>
					<p><a href="<?php echo esc_url( $wp_http_referer ); ?>"><?php printf( __( '&larr; Back to %s' ), $tax->labels->name ); ?></a></p>
				<?php } else { ?>
					<p><a href="<?php echo esc_url( wp_get_referer() ); ?>"><?php printf( __( '&larr; Back to %s' ), $tax->labels->name ); ?></a></p>
				<?php } ?>
			</div>
		<?php endif; ?>

		<div id="ajax-response"></div>

		<form name="edittag" id="edittag" method="post" action="edit-tags.php" class="validate"
			<?php
			/**
			 * Fires inside the Edit Term form tag.
			 *
			 * The dynamic portion of the hook name, `$taxonomy`, refers to
			 * the taxonomy slug.
			 *
			 * @since 3.7.0
			 */
			do_action( "{$taxonomy}_term_edit_form_tag" );
			?>>
			<input type="hidden" name="action" value="editedtag" />
			<input type="hidden" name="tag_ID" value="<?php echo esc_attr($tag->term_id) ?>" />
			<input type="hidden" name="taxonomy" value="<?php echo esc_attr($taxonomy) ?>" />
			<?php wp_original_referer_field(true, 'previous'); wp_nonce_field('update-tag_' . $tag_ID); ?>


			<?php
			/**
			 * WP Fields API implementation >>>
			 */

			/**
			 * @var $wp_fields WP_Fields_API
			 */
			global $wp_fields;

			// Get form
			$form = $wp_fields->get_form( 'term', 'term-edit' );

			// Set taxonomy object name
			$form->object_name = $taxonomy;

			// Render form controls
			$form->maybe_render( $tag_ID, $taxonomy );

			/**
			 * <<< WP Fields API implementation
			 */
			?>

			<?php
			// Back compat hooks
			if ( 'category' == $taxonomy ) {
				/** This action is documented in wp-admin/edit-tags.php */
				do_action( 'edit_category_form', $tag );
			} elseif ( 'link_category' == $taxonomy ) {
				/** This action is documented in wp-admin/edit-tags.php */
				do_action( 'edit_link_category_form', $tag );
			} else {
				/**
				 * Fires at the end of the Edit Term form.
				 *
				 * @since 2.5.0
				 * @deprecated 3.0.0 Use {$taxonomy}_edit_form instead.
				 *
				 * @param object $tag Current taxonomy term object.
				 */
				do_action( 'edit_tag_form', $tag );
			}
			/**
			 * Fires at the end of the Edit Term form for all taxonomies.
			 *
			 * The dynamic portion of the hook name, `$taxonomy`, refers to the taxonomy slug.
			 *
			 * @since 3.0.0
			 *
			 * @param object $tag      Current taxonomy term object.
			 * @param string $taxonomy Current taxonomy slug.
			 */
			do_action( "{$taxonomy}_edit_form", $tag, $taxonomy );

			submit_button( __('Update') );
			?>
		</form>
	</div>

<?php if ( ! wp_is_mobile() ) : ?>
	<script type="text/javascript">
		try{document.forms.edittag.name.focus();}catch(e){}
	</script>
<?php endif;
