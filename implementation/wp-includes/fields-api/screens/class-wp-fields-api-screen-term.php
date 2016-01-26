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

		$wp_fields->add_section( $this->object_type, $this->id . '-main', null, array(
			'title'         => __( 'Term' ),
			'screen'        => $this->id,
			'display_title' => false,
		) );

		$field_args = array(
			// @todo Needs validation callback
			'control' => array(
				'type'        => 'text',
				'id'          => $this->id . '-name',
				'section'     => $this->id . '-main',
				'label'       => __( 'Name' ),
				'description' => __( 'The name is how it appears on your site.' ),
			),
		);

		$wp_fields->add_field( $this->object_type, 'name', null, $field_args );

		$field_args = array(
			'control' => array(
				'type'                  => 'text',
				'id'                    => $this->id . '-slug',
				'section'               => $this->id . '-main',
				'label'                 => __( 'Slug' ),
				'description'           => __( 'The "slug" is the URL-friendly version of the name. It is usually all lowercase and contains only letters, numbers, and hyphens.' ),
				'capabilities_callback' => array( $this, 'capability_is_global_terms_disabled' ),
			),
		);

		$wp_fields->add_field( $this->object_type, 'slug', null, $field_args );

		$field_args = array(
			'control' => array(
				'type'                         => 'dropdown-terms',
				'id'                           => $this->id . '-parent',
				'section'                      => $this->id . '-main',
				'label'                        => __( 'Parent' ),
				'description'                  => __( 'Categories, unlike tags, can have a hierarchy. You might have a Jazz category, and under that have children categories for Bebop and Big Band. Totally optional.' ),
				'capabilities_callback'        => array( $this, 'capability_is_taxonomy_hierarchical' ),
				'exclude_tree_current_item_id' => true,
				'placeholder_text'             => __( 'None' ),
			),
		);

		$wp_fields->add_field( $this->object_type, 'parent', null, $field_args );

		$field_args = array(
			'control' => array(
				'type'        => 'textarea',
				'id'          => $this->id . '-description',
				'section'     => $this->id . '-main',
				'label'       => __( 'Description' ),
				'description' => __( 'The description is not prominent by default; however, some themes may show it.' ),
				'input_attrs' => array(
					'rows' => '5',
					'cols' => '40',
				),
			),
		);

		$wp_fields->add_field( $this->object_type, 'description', null, $field_args );

		// Add example fields
		parent::register_fields( $wp_fields );

	}

	/**
	 * Control hidden if global terms is enabled
	 *
	 * @param WP_Fields_API_Control $control
	 *
	 * @return bool
	 */
	public function capability_is_global_terms_disabled( $control ) {

		return ( ! global_terms_enabled() );

	}

	/**
	 * Control hidden if taxonomy is not hierarchical
	 *
	 * @param WP_Fields_API_Control $control
	 *
	 * @return bool
	 */
	public function capability_is_taxonomy_hierarchical( $control ) {

		return is_taxonomy_hierarchical( $this->object_name );

	}

}