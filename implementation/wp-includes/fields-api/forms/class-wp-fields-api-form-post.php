<?php
/**
 * This is an implementation for Fields API for the Post editor in the WordPress Dashboard
 *
 * @package    WordPress
 * @subpackage Fields_API
 */

/**
 * Class WP_Fields_API_Form_Post
 */
class WP_Fields_API_Form_Post extends WP_Fields_API_Form {

	/**
	 * {@inheritdoc}
	 */
	public $default_section_type = 'meta-box';

	/**
	 * {@inheritdoc}
	 */
	public function register_fields( $wp_fields ) {

		add_action( 'save_post', array( $this, 'wp_save_post' ), 10, 2 );

		// Sections
		// controls + fields

		// Add example fields (maybe)
		parent::register_fields( $wp_fields );

	}

	/**
	 * Save fields based on the current post
	 *
	 * @param int     $post_id
	 * @param WP_Post $post
	 */
	public function wp_save_post( $post_id, $post ) {

		remove_action( 'save_post', array( $this, 'wp_save_post' ) );

		$this->save_fields( $post->ID, $post->post_type );

		add_action( 'save_post', array( $this, 'wp_save_post' ), 10, 2 );

	}

	/**
	 * {@inheritdoc}
	 */
	public function save_fields( $item_id = null, $object_subtype = null ) {

		// Save additional fields
		return parent::save_fields( $item_id, $object_subtype );

	}

}