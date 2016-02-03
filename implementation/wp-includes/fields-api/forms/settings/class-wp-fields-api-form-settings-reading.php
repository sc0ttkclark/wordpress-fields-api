<?php
/**
 * This is an implementation for Fields API for the Reading form in the WordPress Dashboard
 *
 * @package    WordPress
 * @subpackage Fields_API
 */

/**
 * Class WP_Fields_API_Form_Settings_Reading
 */
class WP_Fields_API_Form_Settings_Reading extends WP_Fields_API_Form_Settings {

	/**
	 * {@inheritdoc}
	 */
	public function register_fields( $wp_fields ) {

		// Sections
		$wp_fields->add_section( $this->object_type, $this->id . '-options-reading', null, array(
			'label'         => __( 'Reading Settings' ),
			'form'          => $this->id,
			'display_label' => false,
		) );

		// Controls
	}
}