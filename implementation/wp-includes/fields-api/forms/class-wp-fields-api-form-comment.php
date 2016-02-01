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

		// Add example fields (maybe)
		parent::register_fields( $wp_fields );

	}

	/**
	 * {@inheritdoc}
	 */
	public function save_fields( $item_id = null, $object_name = null ) {

		// Save additional fields
		return parent::save_fields( $item_id, $object_name );

	}

}