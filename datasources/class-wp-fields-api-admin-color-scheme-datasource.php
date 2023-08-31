<?php
/**
 * @package    WordPress
 * @subpackage Fields_API
 */

/**
 * Fields API Admin Color Scheme Datasource class.
 *
 * @see WP_Fields_API_Datasource
 */
class WP_Fields_API_Admin_Color_Scheme_Datasource extends WP_Fields_API_Datasource {

	/**
	 * {@inheritdoc}
	 */
	public $type = 'admin-color-scheme';

	/**
	 * {@inheritdoc}
	 */
	protected function setup_data( $args, $control ) {

		$data = array();

		/**
		 * @var $_wp_admin_css_colors object[]
		 */
		global $_wp_admin_css_colors;

		if ( $_wp_admin_css_colors ) {
			ksort( $_wp_admin_css_colors );

			if ( isset( $_wp_admin_css_colors['fresh'] ) ) {
				// Set Default ('fresh') and Light should go first.
				$_wp_admin_css_colors = array_filter( array_merge( array(
					'fresh' => '',
					'light' => ''
				), $_wp_admin_css_colors ) );
			}

			foreach ( $_wp_admin_css_colors as $color => $color_info ) {
				$data[ $color ] = $color_info->name;
			}
		}

		return $data;

	}

	/**
	 * Allow a datasource to override rendering of a control
	 *
	 * @param WP_Fields_API_Control $control
	 *
	 * @return bool Whether rendering of control has been overridden
	 */
	public function render_control( $control ) {

		$user_id = $control->get_item_id();

		/**
		 * Fires in the 'Admin Color Scheme' section of the user editing form.
		 *
		 * The section is only enabled if a callback is hooked to the action,
		 * and if there is more than one defined color scheme for the admin.
		 *
		 * @param int $user_id The user ID.
		 *
		 * @since 3.8.1 Added `$user_id` parameter.
		 *
		 * @since 3.0.0
		 */
		do_action( 'admin_color_scheme_picker', $user_id );

		return true;

	}

}