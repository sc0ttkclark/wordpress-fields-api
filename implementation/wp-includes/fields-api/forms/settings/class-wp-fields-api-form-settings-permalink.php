<?php
/**
 * This is an implementation for Fields API for the Permalinks form in the WordPress Dashboard
 *
 * @package    WordPress
 * @subpackage Fields_API
 */

/**
 * Class WP_Fields_API_Form_Settings_Permalinks
 */
class WP_Fields_API_Form_Settings_Permalink extends WP_Fields_API_Form_Settings {

	/**
	 * {@inheritdoc}
	 */
	public function register_fields( $wp_fields ) {

		// Sections
		$wp_fields->add_section( $this->object_type, $this->id . '-options-permalink-common', null, array(
			'label'         => __( 'Common Settings' ),
			'form'          => $this->id,
			'display_label' => true,
			'description'   => sprintf( __( 'WordPress offers you the ability to create a custom URL structure for your permalinks and archives. Custom URL structures can improve the aesthetics, usability, and forward-compatibility of your links. A <a href="%s">number of tags are available</a>, and here are some examples to get you started.' ), 'https://codex.wordpress.org/Using_Permalinks' ),
		) );

		$wp_fields->add_section( $this->object_type, $this->id . '-options-permalink-optional', null, array(
				'label'         => __( 'Optional' ),
				'form'          => $this->id,
				'display_label' => true,
				'description'   => sprintf( __( 'If you like, you may enter custom structures for your category and tag URLs here. For example, using <code>topics</code> as your category base would make your category links like <code>%s</code>. If you leave these blank the defaults will be used.' ), site_url( 'topics/uncategorized' ) ),
		) );

		// Controls
		/**
		 * Permalink Structure
		 */

		// @todo add support for a callback to add nested text field (custom permalink structure)
		$choices = array(
			'Plain' => array(
				'value'         => '',
				'example_text'  => '<code>' . site_url( '?p=123' ) . '</code>',
			),
			'Day and name' => array(
				'value'         => '/%year%/%monthnum%/%day%/%postname%/',
				'example_text'  => '<code>' . site_url( '?p=123' ) . '</code>',
			),
			'Month and name' => array(
				'value'         => '/%year%/%monthnum%/%postname%/',
				'example_text'  => '<code>' . site_url( date( 'm/d' ) . '/sample-post/' ) . '</code>',
			),
			'Numeric' => array(
				'value'         => '/archives/%post_id%/',
				'example_text'  => '<code>' . site_url( 'archives/123/' ) . '</code>',
			),
			'Post name' => array(
				'value'         => '/%postname%/',
				'example_text'  => '<code>' . site_url( 'sample-post/ ' ) . '</code>',
			),
			'Custom Structure' => array(
				'value'         => 'custom',
				'example_text'  => '<code>' . site_url() . '</code>'
			)
		);
		$field_args = array(
			'control' => array(
				'type'        => 'radio-multi-label',
				'section'     => $this->id . '-options-permalink-common',
				'input_attrs' => array(
					'name'  => 'selection',
				),
				'choices'     => $choices,
				'internal'    => true,
			),
		);
		$wp_fields->add_field( $this->object_type, 'selection', null, $field_args );

		/**
		 * Category Base
		 */
		$field_args = array(
				'control' => array(
						'type'        => 'text',
						'section'     => $this->id . '-options-permalink-optional',
						'label'       => __( 'Category base' ),
						'input_attrs' => array(
							'name'      => 'category_base',
							'id'        => 'category_base',
							'class'     => 'regular-text code',
						),
						'internal'    => true,
				),
		);
		$wp_fields->add_field( $this->object_type, 'category_base', null, $field_args );

		/**
		 * Tag Base
		 */
		$field_args = array(
				'control' => array(
						'type'        => 'text',
						'section'     => $this->id . '-options-permalink-optional',
						'label'       => __( 'Tag base' ),
						'input_attrs' => array(
								'name'      => 'tag_base',
								'id'        => 'tag_base',
								'class'     => 'regular-text code',
						),
						'internal'    => true,
				),
		);
		$wp_fields->add_field( $this->object_type, 'tag_base', null, $field_args );

	}
}