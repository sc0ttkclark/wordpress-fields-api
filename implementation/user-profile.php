<?php
/**
 * This is an implementation for Fields API for the User Profile screen in the WordPress Dashboard
 *
 * @package    WordPress
 * @subpackage Fields_API
 */

/**
 * Display fields in User Profile
 *
 * @param WP_User $user
 */
function wp_fields_api_user_profile_fields( $user ) {

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
add_action( 'show_user_profile', 'wp_fields_api_user_profile_fields' );
add_action( 'edit_user_profile', 'wp_fields_api_user_profile_fields' );

/**
 * Handle saving of user profile fields
 *
 * @param int $user_id
 * @param array $old_user_data
 */
function wp_fields_api_user_profile_save( $user_id, $old_user_data ) {

	if ( ! empty( $_REQUEST['wp_fields_api_fields_save'] ) && false !== wp_verify_nonce( $_REQUEST['wp_fields_api_fields_save'], 'wp_fields_api_user_profile' ) ) {
		// @todo Handle sanitize / update processes
	}

}
add_action( 'profile_update', 'wp_fields_api_user_profile_save', 10, 2 );