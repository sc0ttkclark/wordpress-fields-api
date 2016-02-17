<?php
/**
 * Register Fields API configuration
 *
 * @param WP_Fields_API $wp_fields
 */
function example_my_user_starter( $wp_fields ) {

	// Object type: User
	$object_type = 'user';

	// Object name: n/a
	$object_name = null;

	// Form: User Edit Profile
	$form_id = 'user-edit';

	/////////////////////////
	// Section: My Section //
	/////////////////////////

	$section_id   = ''; // @todo Fill in section ID
	$section_args = array(
		'label' => __( '', 'my-text-domain' ), // @todo Fill in section heading, update text domain
		'form'  => $form_id,
	);

	$wp_fields->add_section( $object_type, $section_id, $object_name, $section_args );

	// My Field
	$field_id   = '';
	$field_args = array(
		// You can register a control for this field at the same time
		'control' => array(
			'type'        => 'text', // @todo Change control type if needed
			'section'     => $section_id,
			'label'       => __( '', 'my-text-domain' ), // @todo Fill in label, update text domain
			'description' => __( '', 'my-text-domain' ), // @todo Fill in description, update text domain
		),
	);

	$wp_fields->add_field( $object_type, $field_id, $object_name, $field_args );

}

add_action( 'fields_register', 'example_my_user_starter' );