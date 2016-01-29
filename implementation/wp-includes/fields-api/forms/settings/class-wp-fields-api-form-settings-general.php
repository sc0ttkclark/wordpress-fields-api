<?php
/**
 * This is an implementation for Fields API for the General Settings form in the WordPress Dashboard
 *
 * @package    WordPress
 * @subpackage Fields_API
 */

/**
 * Class WP_Fields_API_Form_Settings_General
 */
class WP_Fields_API_Form_Settings_General extends WP_Fields_API_Table_Form {

	/**
	 * {@inheritdoc}
	 */
	public function register_fields( $wp_fields ) {

		$wp_fields->add_section( $this->object_type, $this->id . '-options-general', null, array(
				'title'  => __( 'General Settings' ),
				'form' => $this->id,
		) );

		$field_args = array(
				'control' => array(
						'type'        => 'text',
						'section'     => $this->id . '-options-general',
						'label'       => __( 'Site Title' ),
						//'description' => __( 'Usernames cannot be changed.' ),
						//'input_attrs' => array(
						//		'disabled' => 'disabled',
						//),
						'internal'    => true,
				),
		);

		$wp_fields->add_field( $this->object_type, 'blogname', null, $field_args );

	}

}