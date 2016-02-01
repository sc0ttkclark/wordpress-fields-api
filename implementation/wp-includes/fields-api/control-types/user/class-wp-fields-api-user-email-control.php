<?php
/**
 * @package    WordPress
 * @subpackage Fields_API
 */

/**
 * Fields API User Email Control class.
 *
 * @see WP_Fields_API_Control
 */
class WP_Fields_API_User_Email_Control extends WP_Fields_API_Control {

	/**
	 * {@inheritdoc}
	 */
	public $type = 'user-email';

	/**
	 * {@inheritdoc}
	 */
	protected function render_content() {

		$current_user = get_userdata( get_current_user_id() );

		$profileuser = get_userdata( $this->item_id );

		parent::render_content();

		$new_email = get_option( $current_user->ID . '_new_email' );

		if ( $new_email && $new_email['newemail'] != $current_user->user_email && $profileuser->ID == $current_user->ID ) {
			echo '<div class="updated inline"><p>';

			printf( __( 'There is a pending change of your e-mail to %1$s. <a href="%2$s">Cancel</a>' ), '<code>' . $new_email['newemail'] . '</code>', esc_url( self_admin_url( 'profile.php?dismiss=' . $current_user->ID . '_new_email' ) ) );

			echo '</p></div>';
		}

	}

}