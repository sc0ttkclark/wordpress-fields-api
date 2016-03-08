<?php
/**
 * Register Fields API configuration
 *
 * @param WP_Fields_API $wp_fields
 */
function example_my_user_address( $wp_fields ) {

	// Object type: User
	$object_type = 'user';

	// Object subtype: n/a
	$object_subtype = null;

	// Form: User Edit Profile
	$form_id = 'user-edit';

	//////////////////////
	// Section: Address //
	//////////////////////

	$section_id   = 'address';
	$section_args = array(
		'label'    => __( 'Address', 'my-text-domain' ), // @todo Update text domain
		'form'     => $form_id,
		'controls' => array(), // We will add our controls below
	);

	// Address Line 1
	$section_args['controls']['address_1'] = array(
		'type'    => 'text',
		'label'   => __( 'Address 1', 'my-text-domain' ), // @todo Update text domain
	);

	// Address Line 2
	$section_args['controls']['address_2'] = array(
		'type'    => 'text',
		'label'   => __( 'Address 2', 'my-text-domain' ), // @todo Update text domain
	);

	// City
	$section_args['controls']['address_city'] = array(
		'type'    => 'text',
		'label'   => __( 'City', 'my-text-domain' ), // @todo Update text domain
	);

	// State / Region
	$section_args['controls']['address_state'] = array(
		'type'    => 'text',
		'label'   => __( 'State / Region', 'my-text-domain' ), // @todo Update text domain
		// You could also use 'select' type and set the 'datasource' or 'choices' option
	);

	// Zip / Postal Code
	$section_args['controls']['address_zip'] = array(
		'type'    => 'text',
		'label'   => __( 'Zip / Postal Code', 'my-text-domain' ), // @todo Update text domain
	);

	// Zip / Postal Code
	$section_args['controls']['address_country'] = array(
		'type'       => 'select',
		'label'      => __( 'Country', 'my-text-domain' ), // @todo Update text domain
		'datasource' => array(
			// Get list of Countries from taxonomy datasource
			'type'     => 'term',
			'get_args' => array(
				'taxonomy' => 'country'
			),
		),
		// You could also pass in all countries in 'choices' option
		// with array( 'US' => 'United States' )
	);

	// Add the section
	$wp_fields->add_section( $object_type, $section_id, $object_subtype, $section_args );

}

add_action( 'fields_register', 'example_my_user_address' );