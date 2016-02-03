<?php
/**
 * @package    WordPress
 * @subpackage Fields_API
 */

/**
 * Fields API User Sessions Control class.
 *
 * @see WP_Fields_API_Control
 */
class WP_Fields_API_User_Sessions_Control extends WP_Fields_API_Control {

	/**
	 * {@inheritdoc}
	 */
	public $type = 'user-sessions';

	/**
	 * {@inheritdoc}
	 */
	protected function render_content() {

		$profileuser = get_userdata( $this->get_item_id() );

		/**
		 * @var WP_User_Meta_Session_Tokens $sessions
		 */
		$sessions = WP_Session_Tokens::get_instance( $profileuser->ID );
?>
	<?php if ( defined( 'IS_PROFILE_PAGE' ) && IS_PROFILE_PAGE && count( $sessions->get_all() ) === 1 ) : ?>
		<div aria-live="assertive">
			<div class="destroy-sessions"><button type="button" disabled class="button button-secondary"><?php _e( 'Log Out Everywhere Else' ); ?></button></div>
			<p class="description">
				<?php _e( 'You are only logged in at this location.' ); ?>
			</p>
		</div>
	<?php elseif ( defined( 'IS_PROFILE_PAGE' ) && IS_PROFILE_PAGE && count( $sessions->get_all() ) > 1 ) : ?>
		<div aria-live="assertive">
			<div class="destroy-sessions"><button type="button" class="button button-secondary" id="destroy-sessions"><?php _e( 'Log Out Everywhere Else' ); ?></button></div>
			<p class="description">
				<?php _e( 'Did you lose your phone or leave your account logged in at a public computer? You can log out everywhere else, and stay logged in here.' ); ?>
			</p>
		</div>
	<?php elseif ( defined( 'IS_PROFILE_PAGE' ) && ! IS_PROFILE_PAGE && $sessions->get_all() ) : ?>
		<p><button type="button" class="button button-secondary" id="destroy-sessions"><?php _e( 'Log Out Everywhere' ); ?></button></p>
		<p class="description">
			<?php
			/* translators: 1: User's display name. */
			printf( __( 'Log %s out of all locations.' ), $profileuser->display_name );
			?>
		</p>
	<?php endif; ?>
<?php

	}

}