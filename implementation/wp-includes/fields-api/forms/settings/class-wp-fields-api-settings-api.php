<?php
/**
 * This is an integration of the Settings API with the Fields API.
 *
 * @package    WordPress
 * @subpackage Fields_API
 */

/**
 * Class WP_Fields_API_Settings_API
 */
class WP_Fields_API_Settings_API {

	public function __construct() {

		// Add hook
		add_action( 'admin_init', array( $this, 'register_settings' ) );

	}

	public function register_settings() {

		/**
		 * @var $wp_fields WP_Fields_API
		 */
		global $wp_fields;

		$sections = $wp_fields->get_sections( 'settings' );

		foreach ( $sections as $section ) {
			// Get Form
			$form_id = $section->form;
			$section_id = $section->id;

			if ( is_object( $form_id ) ) {
				$form_id = $form_id->id;
			}

			// Get Setting Controls
			$controls = $wp_fields->get_controls( $section->object_type, null, $section_id );

			if ( $controls ) {
				add_settings_section(
					$section_id,
					$section->title,
					array( $section, 'render_description' ),
					$form_id
				);

				foreach ( $controls as $control ) {
					$field_id = $control->fields;

					if ( empty( $field_id ) ) {
						continue;
					}

					$field_id = current( $field_id );

					$sanitize_callback = '';

					if ( is_object( $field_id ) ) {
						$sanitize_callback = array( $field_id, 'sanitize' );

						$field_id = $field_id->id;
					}

					// Add Settings API field
					add_settings_field(
						$field_id, // Field ID
						$control->label, // Label
						array( $control, 'maybe_render' ), // Render Callback
						$form_id, // Page ID
						$section_id // Section ID
					);

					// Register Setting
					register_setting(
						$form_id,
						$field_id,
						$sanitize_callback
					);
				}
			}

		}

	}

}