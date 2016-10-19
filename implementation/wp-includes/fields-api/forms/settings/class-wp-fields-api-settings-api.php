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

	public function setup() {

		// Add hook
		add_action( 'admin_init', array( $this, 'register_settings' ) );

	}

	public function register_settings() {

		/**
		 * @var $wp_fields WP_Fields_API
		 */
		global $wp_fields;

		$forms = $wp_fields->get_forms( 'settings' );

		foreach ( $forms as $form ) {
			/**
			 * @var $sections WP_Fields_API_Section[]
			 */
			$sections = $form->get_children();

			foreach ( $sections as $section ) {
				if ( ! $section->check_capabilities() ) {
					continue;
				}

				$form_id = $form->id;
				$section_id = $section->id;
				$section_title = $section->label;

				// Remove our namespace
				$setting_page_id = str_replace( 'settings-', '', $form_id );

				if ( ! $section->display_label ) {
					$section_title = '';
				}

				// Get Setting Controls
				/**
				 * @var $controls WP_Fields_API_Control[]
				 */
				$controls = $section->get_children();

				if ( $controls ) {
					$added_section = false;

					foreach ( $controls as $control ) {
						$field = $control->field;

						if ( empty( $field ) ) {
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
								$setting_page_id
							);

							$added_section = true;
						}

						$sanitize_callback = array( $field, 'sanitize' );

						$field_id = $field->id;

						$settings_args = array(
							'fields_api' => true,
							'label_for'  => $field_id,
							'control'    => $control,
						);

						$control_label   = $control->label;
						$render_callback = array( $this, 'render_control' );

						if ( 'hidden' === $control->type ) {
							$control_label   = null;
							$render_callback = '__return_null';
						}

						// Add Settings API field
						add_settings_field(
							$field_id,
							$control_label,
							$render_callback,
							$setting_page_id,
							$section_id,
							$settings_args
						);

						// Register Setting
						register_setting(
							$setting_page_id,
							$field_id,
							$sanitize_callback
						);
					}
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

		$description = trim( $control->description );

		$control->maybe_render();

		if ( 0 < strlen( $description ) ) {
			?>
			<p class="description"><?php echo wp_kses_post( $description ); ?></p>
			<?php
		}

	}

}