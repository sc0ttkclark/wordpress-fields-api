<?php
/**
 * This is an implementation for Fields API for the Term Add New form in the WordPress Dashboard
 *
 * @package    WordPress
 * @subpackage Fields_API
 */

/**
 * Class WP_Fields_API_Form_Term_Add
 */
class WP_Fields_API_Form_Term_Add extends WP_Fields_API_Form_Term {

	/**
	 * {@inheritdoc}
	 */
	public $default_section_type = 'default';

	/**
	 * {@inheritdoc}
	 */
	public function save_fields( $item_id = null, $object_subtype = null ) {

		if ( null === $object_subtype ) {
			$object_subtype = $this->get_object_subtype();
		}

		$term_name = '';

		// Get tag name
		if ( isset( $_POST['tag-name'] ) ) {
			$term_name = $_POST['tag-name'];
		}

		// Save new term
		$success = wp_insert_term( $term_name, $object_subtype, $_POST );

		// Return if not successful
		if ( is_wp_error( $success ) ) {
			return $success;
		}

		// Save additional fields
		return parent::save_fields( $item_id, $object_subtype );

	}

}