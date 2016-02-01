<?php
/**
 * @package    WordPress
 * @subpackage Fields_API
 */

/**
 * Fields API User Super Admin Control class.
 *
 * @see WP_Fields_API_Control
 */
class WP_Fields_API_User_Super_Admin_Control extends WP_Fields_API_Checkbox_Control {

	/**
	 * {@inheritdoc}
	 */
	public $type = 'user-super-admin';

	/**
	 * {@inheritdoc}
	 */
	protected function render_content() {

		$profileuser = get_userdata( $this->item_id );

		if ( $profileuser->user_email == get_site_option( 'admin_email' ) && is_super_admin( $profileuser->ID ) ) {
			echo '<p>' . __( 'Super admin privileges cannot be removed because this user has the network admin email.' ) . '</p>';
		} else {
			parent::render_content();
		}

	}

}