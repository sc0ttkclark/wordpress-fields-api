<?php
/**
 * Register Fields API configuration
 *
 * @param WP_Fields_API $wp_fields
 */
function example_option_form_registration( $wp_fields ) {
	// Object type and Form ID
	$object_type = 'settings';
	$form_id = 'example-form';

	// Set this to a specific post type, taxonomy,
	// or comment type you want to register for
	$object_subtype = null;

	// Register form
	$wp_fields->add_form( $object_type, $form_id, $object_subtype );

	$section_id = 'my-section'; // @todo Fill in section ID
	$section_args = array(
	    'label'  => '',
	    'form' => $form_id,
	    'controls' => array(
	        // List of controls for this section
	        'my-control' => array(
	            'type'        => 'text', // @todo Change control type if needed
	            'label'       => __( 'My Field', 'my-text-domain' ), // @todo Fill in label, update text domain
	            'description' => __( 'Description of My Field', 'my-text-domain' ), // @todo Fill in description, update text domain
	        ),
	    ),
	);

	// Register section
	$wp_fields->add_section( $object_type, $section_id, $object_subtype, $section_args );
}

add_action( 'fields_register', 'example_option_form_registration' );

/**
 * Render options page
 */
function example_option_page() {
	global $wp_fields;

	// Object type and Form ID
	$object_type = 'settings';
	$form_id = 'example-form';

	// Get the form object
	$form = $wp_fields->get_form( $object_type, $form_id );

	$form->save_fields();

	?>
		<div class="wrap">
			<h2><?php _e( 'Example Options Page', 'my-text-domain' ); ?></h2>

			<form method="post">
				<?php $form->maybe_render(); ?>

				<?php submit_button(); ?>
			</form>
		</div>
	<?php
}

/**
 * Set up options page
 */
function example_option_menu() {
	add_options_page( esc_html__( 'Example Option Page', 'my-text-domain' ), esc_html__( 'Example Option Page', 'my-text-domain' ), 'manage_options', 'example-option.php', 'example_option_page' );
}
add_action( 'admin_menu', 'example_option_menu' );

