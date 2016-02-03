<?php
/**
 * @package    WordPress
 * @subpackage Fields_API
 */

/**
 * Fields API User Role Control class.
 *
 * @see WP_Fields_API_Control
 */
class WP_Fields_API_User_Role_Control extends WP_Fields_API_Select_Control {

	/**
	 * {@inheritdoc}
	 */
	public $type = 'user-role';

	/**
	 * {@inheritdoc}
	 */
	public function choices() {

		$choices = array(
			'' => __( '&mdash; No role for this site &mdash;' ),
		);

		$editable_roles = array_reverse( get_editable_roles() );

		foreach ( $editable_roles as $role => $details ) {
			$name = translate_user_role( $details['name'] );

			$choices[ $role ] = $name;
		}

		return $choices;

	}

}