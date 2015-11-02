<?php
/**
 * This is an implementation for Fields API for the User Profile screen in the WordPress Dashboard
 *
 * @package    WordPress
 * @subpackage Fields_API
 */

/**
 * Class WP_Fields_API_User_Profile
 */
class WP_Fields_API_User_Profile {

	public function __construct() {

		add_action( 'show_user_profile', array( $this, 'output_fields' ) );
		add_action( 'edit_user_profile', array( $this, 'output_fields' ) );

		add_action( 'profile_update', array( $this, 'save_fields' ), 10, 2 );

	}

	/**
	 * Display fields in User Profile
	 *
	 * @param WP_User $user
	 */
	public function output_fields( $user ) {

		// @todo Handle pulling fields based on $user->ID

		/**
		 * @var $wp_fields WP_Fields_API
		 */
		global $wp_fields;

		$screen = $wp_fields->get_screen( 'user', 'edit-profile' );

		$nonced = false;

		if ( $screen ) {
			$sections = $wp_fields->get_sections( 'user', null, $screen->id );

			if ( ! empty( $sections ) ) {
				foreach ( $sections as $section ) {
					$controls = $wp_fields->get_controls( 'user', null, $section->id );

					if ( $controls ) {
						$content = $section->get_content();

						if ( $content ) {
							if ( ! $nonced ) {
								$nonced = true;

								wp_nonce_field( 'wp_fields_api_user_profile', 'wp_fields_api_fields_save' );
							}
				?>
					<h3><?php echo $section->get_content(); ?></h3>

					<table class="form-table">
						<?php foreach ( $controls as $control ) { ?>
							<?php
								$label = $control->label;
								$description = $control->description;

								// Avoid outputting them in render_content()
								$control->label = '';
								$control->description = '';

								// Setup field name
								$control->input_attrs['name'] = 'field_' . $control->id;
							?>
							<tr class="field-<?php echo esc_attr( $control->id ); ?>-wrap">
								<th>
									<?php if ( $label ) { ?>
										<label for="field-<?php echo esc_attr( $control->id ); ?>"><?php esc_html( $label ); ?></label>
									<?php } ?>
								</th>
								<td>
									<?php $control->render_content(); ?>

									<?php if ( $description ) { ?>
										<p class="description"><?php echo $description; ?></p>
									<?php } ?>
								</td>
							</tr>
						<?php } ?>
					</table>
				<?php
						}
					}
				}
			}
		}

	}

	/**
	 * Handle saving of user profile fields
	 *
	 * @param int $user_id
	 * @param array $old_user_data
	 */
	public function save_fields( $user_id, $old_user_data ) {

		if ( ! empty( $_REQUEST['wp_fields_api_fields_save'] ) && false !== wp_verify_nonce( $_REQUEST['wp_fields_api_fields_save'], 'wp_fields_api_user_profile' ) ) {
			/**
			 * @var $wp_fields WP_Fields_API
			 */
			global $wp_fields;

			$controls = $wp_fields->get_controls( 'user' );

			foreach ( $controls as $control ) {
				if ( empty( $control->field ) ) {
					continue;
				}

				$field = $control->field;

				// Get value from $_POST
				$value = null;

				if ( ! empty( $_POST[ 'field_' . $control->id ] ) ) {
					$value = $_POST[ 'field_' . $control->id ];
				}

				// Save value
				$field->save( $value, $user_id ); // @todo Follow up on sanitization in WP_Fields_API_Field
			}
		}

	}

	/**
	 * Register controls for User Profiles
	 *
	 * @todo Move out of wp-admin implementation
	 */
	public function register_controls() {

		/**
		 * @var $wp_fields WP_Fields_API
		 */
		global $wp_fields;

		$wp_fields->add_screen( 'user', 'edit-profile' );

		// Add sections
		//$wp_fields->add_section();

		// Add controls and fields
		//$wp_fields->add_field();

	}

}