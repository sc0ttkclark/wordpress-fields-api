<?php
/**
 * This is an implementation for Fields API for the Edit Tags screen in the WordPress Dashboard
 *
 * @package    WordPress
 * @subpackage Fields_API
 */

/**
 * Class WP_Fields_API_User_Profile
 */
class WP_Fields_API_Edit_Tags {

    public function __construct() {

        $this->register_controls();

    }

    /**
     * Register controls for Edit Tags
     *
     * @todo Move out of wp-admin implementation
     */
    public function register_controls() {

        /**
         * @var $wp_fields WP_Fields_API
         */
        global $wp_fields;

        // Register control types
        //$wp_fields->register_control_type( 'user-color-scheme', 'WP_Fields_API_Color_Scheme_Control' );

        // Add Edit Tags screen
        $wp_fields->add_screen( 'term', 'edit-tags' );

        ////////////////
        // Core: Term //
        ////////////////

        $wp_fields->add_section( 'term', 'term-main', null, array(
            'title' => __( 'Term' ),
            'screen' => 'edit-tags',
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

        $wp_fields->add_field( 'term', 'name', null, $field_args );

        $field_args = array(
            'control' => array(
                'type'                  => 'text',
                'section'               => 'term-main',
                'label'                 => __( 'Slug' ),
                'description'           => __( 'The "slug" is the URL-friendly version of the name. It is usually all lowercase and contains only letters, numbers, and hyphens.' ),
            ),
        );

        $wp_fields->add_field( 'term', 'slug', null, $field_args );

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

        $wp_fields->add_field( 'term', 'description', null, $field_args );

		//////////////
		// Examples //
		//////////////

		// Section
		$wp_fields->add_section( 'term', 'example-my-fields', null, array(
			'title' => __( 'Fields API Example - My Fields' ),
		    'screen' => 'edit-tags',
		) );

		// Add example for each control type
		$control_types = array(
			'text',
			'checkbox',
			'multi-checkbox',
			'radio',
			'select',
			'dropdown-pages',
			'color',
			'media',
			'upload',
			'image',
		);

		$option_types = array(
			'multi-checkbox',
			'radio',
			'select',
		);

		foreach ( $control_types as $control_type ) {
			$id    = 'example_my_' . $control_type . '_field';
			$label = sprintf( __( '%s Field' ), ucwords( str_replace( '-', ' ', $control_type ) ) );

			$field_args = array(
				// Add a control to the field at the same time
				'control' => array(
					'type'    => $control_type,
					'section' => 'example-my-fields',
					'label'   => $label,
				),
			);

			if ( in_array( $control_type, $option_types ) ) {
				$field_args['control']['choices'] = array(
					''         => 'N/A',
					'option-1' => 'Option 1',
					'option-2' => 'Option 2',
					'option-3' => 'Option 3',
					'option-4' => 'Option 4',
					'option-5' => 'Option 5',
				);
			}

			$wp_fields->add_field( 'term', $id, null, $field_args );
		}

    }

}