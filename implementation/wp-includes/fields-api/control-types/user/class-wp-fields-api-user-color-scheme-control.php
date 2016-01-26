<?php
/**
 * @package    WordPress
 * @subpackage Fields_API
 */

/**
 * Fields API Color Scheme Control class.
 *
 * @see WP_Fields_API_Control
 */
class WP_Fields_API_User_Color_Scheme_Control extends WP_Fields_API_Radio_Control {

	/**
	 * Setup color scheme choices for use by control
	 */
	public function choices() {

		/**
		 * @var $_wp_admin_css_colors object[]
		 */
		global $_wp_admin_css_colors;

		$choices = array();

		ksort( $_wp_admin_css_colors );

		if ( isset( $_wp_admin_css_colors['fresh'] ) ) {
			// Set Default ('fresh') and Light should go first.
			$_wp_admin_css_colors = array_filter( array_merge( array(
				'fresh' => '',
				'light' => ''
			), $_wp_admin_css_colors ) );
		}

		foreach ( $_wp_admin_css_colors as $color => $color_info ) {
			$choices[ $color ] = $color_info->name;
		}

		return $choices;

	}

	/**
	 * {@inheritdoc}
	 */
	public function render_content() {

		$user_id = $this->item_id;

		/**
		 * Fires in the 'Admin Color Scheme' section of the user editing screen.
		 *
		 * The section is only enabled if a callback is hooked to the action,
		 * and if there is more than one defined color scheme for the admin.
		 *
		 * @since 3.0.0
		 * @since 3.8.1 Added `$user_id` parameter.
		 *
		 * @param int $user_id The user ID.
		 */
		do_action( 'admin_color_scheme_picker', $user_id );

	}

}