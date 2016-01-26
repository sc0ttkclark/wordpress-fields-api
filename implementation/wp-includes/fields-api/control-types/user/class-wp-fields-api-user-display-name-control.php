<?php
/**
 * @package    WordPress
 * @subpackage Fields_API
 */

/**
 * Fields API User Display Name Control class.
 *
 * @see WP_Fields_API_Control
 */
class WP_Fields_API_User_Display_Name_Control extends WP_Fields_API_Select_Control {

	/**
	 * Setup color scheme choices for use by control
	 */
	public function choices() {

		$profileuser = get_userdata( $this->item_id );

		$choices = array();

		$choices['display_nickname'] = $profileuser->nickname;
		$choices['display_username'] = $profileuser->user_login;

		if ( ! empty( $profileuser->first_name ) ) {
			$choices['display_firstname'] = $profileuser->first_name;
		}

		if ( ! empty( $profileuser->last_name ) ) {
			$choices['display_lastname'] = $profileuser->last_name;
		}

		if ( ! empty( $profileuser->first_name ) && ! empty( $profileuser->last_name ) ) {
			$choices['display_firstlast'] = $profileuser->first_name . ' ' . $profileuser->last_name;
			$choices['display_lastfirst'] = $profileuser->last_name . ' ' . $profileuser->first_name;
		}

		// Only add this if it isn't duplicated elsewhere
		if ( ! in_array( $profileuser->display_name, $choices ) ) {
			$first_option = array(
				'display_displayname' => $profileuser->display_name,
			);

			$choices = array_merge( $first_option, $choices );
		}

		$choices = array_map( 'trim', $choices );
		$choices = array_filter( $choices );
		$choices = array_unique( $choices );

		return $choices;

	}

}