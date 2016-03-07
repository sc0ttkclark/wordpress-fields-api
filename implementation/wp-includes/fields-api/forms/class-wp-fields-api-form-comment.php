<?php
/**
 * This is an implementation for Fields API for the Comment editor in the WordPress Dashboard
 *
 * @package    WordPress
 * @subpackage Fields_API
 */

/**
 * Class WP_Fields_API_Form_Comment
 */
class WP_Fields_API_Form_Comment extends WP_Fields_API_Form {

	/**
	 * {@inheritdoc}
	 */
	public $default_section_type = 'meta-box';

	/**
	 * {@inheritdoc}
	 */
	public function register_fields( $wp_fields ) {

		add_action( 'edit_comment', array( $this, 'wp_edit_comment' ), 10, 2 );

		// Sections
		// controls + fields

		// Add example fields (maybe)
		parent::register_fields( $wp_fields );

	}

	/**
	 * Save fields based on the current comment
	 *
	 * @param int $comment_ID
	 */
	public function wp_edit_comment( $comment_ID, $post ) {

		remove_action( 'edit_comment', array( $this, 'wp_edit_comment' ) );

		$comment = get_comment( $comment_ID );

		if ( $comment ) {
			$this->save_fields( $comment->comment_ID, $comment->comment_type );
		}

		add_action( 'edit_comment', array( $this, 'wp_edit_comment' ), 10, 2 );

	}

	/**
	 * {@inheritdoc}
	 */
	public function save_fields( $item_id = null, $object_name = null ) {

		// Save additional fields
		return parent::save_fields( $item_id, $object_name );

	}

}