<?php
/**
 * This is an implementation for Fields API for the Term screens in the WordPress Dashboard
 *
 * @package    WordPress
 * @subpackage Fields_API
 */

/**
 * Class WP_Fields_API_Screen_Term
 */
class WP_Fields_API_Screen_Term extends WP_Fields_API_Screen {

	/**
	 * {@inheritdoc}
	 */
	public function register_fields( $wp_fields ) {

		////////////////
		// Core: Term //
		////////////////

		$wp_fields->add_section( $this->object_type, 'term-main', null, array(
			'title' => __( 'Term' ),
			'screen' => $this->id,
			'display_title' => false,
		) );

		$field_args = array(
			// @todo Needs validation callback
			'control' => array(
				'type'        => 'text',
				'section'     => 'term-main',
				'label'       => __( 'Name' ),
				'description' => __( 'The name is how it appears on your site.' ),
			),
		);

		$wp_fields->add_field( $this->object_type, 'name', null, $field_args );


		$field_args = array(
			'control'               => array(
				'type'                  => 'textarea',
				'section'               => 'term-main',
				'label'                 => __( 'Description' ),
				'description'           => __( 'The description is not prominent by default; however, some themes may show it.' ),
				'input_attrs' => array(
					'rows' => '5',
					'cols' => '40',
				),
			),
		);

		$wp_fields->add_field( $this->object_type, 'description', null, $field_args );

	}

}