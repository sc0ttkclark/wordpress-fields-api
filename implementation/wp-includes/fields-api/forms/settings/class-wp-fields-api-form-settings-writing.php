<?php
/**
 * This is an implementation for Fields API for the General Writing form in the WordPress Dashboard
 *
 * @package    WordPress
 * @subpackage Fields_API
 */

/**
 * Class WP_Fields_API_Form_Settings_Writing
 */
class WP_Fields_API_Form_Settings_Writing extends WP_Fields_API_Form_Settings {

	/**
	 * {@inheritdoc}
	 */
	public function register_fields( $wp_fields ) {

		// Sections
		$wp_fields->add_section( $this->object_type, $this->id . '-options-general', null, array(
			'label'         => __( 'Writing Settings' ),
			'form'          => $this->id,
			'display_label' => false,
		) );

		$wp_fields->add_section( $this->object_type, $this->id . '-options-general-update-services', null, array(
			'label'         => __( 'Update Services' ),
			'form'          => $this->id,
			'display_label' => true,
		) );

		// Controls
		/**
		 * Default Post Category
		 */
		$field_args = array(
			'control' => array(
				'type'        => 'dropdown-terms',
				'taxonomy'    => 'category',
				'section'     => $this->id . '-options-general',
				'label'       => __( 'Default Post Category' ),
				'input_attrs' => array(
					'class' => 'postform',
					'id'    => 'default_category',
					'name'  => 'default_category',
				),
				'internal'    => true,
			),
		);
		$wp_fields->add_field( $this->object_type, 'default_category', null, $field_args );

		// Controls
		/**
		 * Default Post Format
		 */
		$field_args = array(
			'control' => array(
				'type'        => 'dropdown-post-format',
				'section'     => $this->id . '-options-general',
				'label'       => __( 'Default Post Format' ),
				'input_attrs' => array(
					'id'    => 'default_post_format',
					'name'  => 'default_post_format',
				),
				'internal'    => true,
			),
		);
		$wp_fields->add_field( $this->object_type, 'default_post_format', null, $field_args );
	}


}