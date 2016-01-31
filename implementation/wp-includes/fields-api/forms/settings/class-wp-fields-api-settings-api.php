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
			if ( ! $section->check_capabilities() ) {
				continue;
			}

			// Get Form
			$form_id = $section->form;
			$section_id = $section->id;
			$section_title = $section->title;

			if ( is_object( $form_id ) ) {
				$form_id = $form_id->id;
			}

			if ( ! $section->display_title ) {
				$section_title = '';
			}

			// Get Setting Controls
			$controls = $wp_fields->get_controls( $section->object_type, null, $section_id );

			if ( $controls ) {
				$added_section = false;

				foreach ( $controls as $control ) {
					$field_id = $control->fields;

					if ( empty( $field_id ) ) {
						continue;
					}

					if ( ! $control->check_capabilities() ) {
						continue;
					}

					if ( ! $added_section ) {

						add_settings_section(
							$section_id,
							$section_title,
							array( $section, 'render_description' ),
							$form_id
						);

						$added_section = true;
					}

					$field_id = current( $field_id );

					$sanitize_callback = '';

					if ( is_object( $field_id ) ) {
						$sanitize_callback = array( $field_id, 'sanitize' );

						$field_id = $field_id->id;
					}

					$settings_args = array(
						'fields_api' => true,
						'label_for'  => $field_id,
						'control'    => $control,
					);

					// Add Settings API field
					add_settings_field(
						$field_id,
						$control->label,
						array( $this, 'render_control' ),
						$form_id,
						$section_id,
						$settings_args
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

	/**
	 * Render control for Settings API
	 *
	 * @param array $settings_args Settings args
	 */
	public function render_control( $settings_args ) {

		if ( empty( $settings_args['fields_api'] ) || empty( $settings_args['control'] ) ) {
			return;
		}

		/**
		 * @var $control WP_Fields_API_Control
		 */
		$control = $settings_args['control'];

		if ( ! $control->check_capabilities() ) {
			return;
		}

		$description = trim( $control->description );

		// Avoid outputting them in render_content()
		$control->label       = '';
		$control->description = '';

		$control->render_content();

		if ( 0 < strlen( $description ) ) {
			?>
			<p class="description"><?php echo wp_kses_post( $description ); ?></p>
			<?php
		}

	}

}