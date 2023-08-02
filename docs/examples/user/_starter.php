<?php
/**
 * Register Fields API configuration
 *
 * @param WP_Fields_API $wp_fields
 */
function example_my_user_starter( $wp_fields ) {

	// Object type: User
	$object_type = 'user';

	// Object subtype: n/a
	$object_subtype = null;

	// Form: User Edit Profile
	$form_id = 'user-edit';

	/////////////////////////
	// Section: My Section //
	/////////////////////////

	$section_id   = 'my-section'; // @todo Update section ID
	$section_args = array(
		'label'    => __( '', 'my-text-domain' ), // @todo Fill in section heading, update text domain
		'form'     => $form_id,
		'controls' => array(), // We will add our controls below
	);

	// My Field
	// @todo Update control ID
	$section_args['controls']['my-field'] = array(
		'type'        => 'text', // @todo Change control type if needed
		'label'       => __( '', 'my-text-domain' ), // @todo Fill in label, update text domain
		'description' => __( '', 'my-text-domain' ), // @todo Fill in description, update text domain
	);

	// Add the section
	$wp_fields->add_section( $object_type, $section_id, $object_subtype, $section_args );

}

add_action( 'fields_register', 'example_my_user_starter' );