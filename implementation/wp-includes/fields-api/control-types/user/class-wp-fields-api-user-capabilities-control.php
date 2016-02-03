<?php
/**
 * @package    WordPress
 * @subpackage Fields_API
 */

/**
 * Fields API User Capabilities Control class.
 *
 * @see WP_Fields_API_Control
 */
class WP_Fields_API_User_Capabilities_Control extends WP_Fields_API_Control {

	/**
	 * {@inheritdoc}
	 */
	public $type = 'user-capabilities';

	/**
	 * {@inheritdoc}
	 */
	protected function render_content() {

		/**
		 * @var $wp_roles WP_Roles
		 */
		global $wp_roles;

		$profileuser = get_userdata( $this->get_item_id() );

		$output = array();

		foreach ( $profileuser->caps as $cap => $value ) {
			if ( ! $wp_roles->is_role( $cap ) ) {
				if ( ! $value ) {
					$cap = sprintf( __( 'Denied: %s' ), $cap );
				}

				$output[] = $cap;
			}
		}

		$output = implode( ', ', $output );

		echo esc_html( $output );

	}

}