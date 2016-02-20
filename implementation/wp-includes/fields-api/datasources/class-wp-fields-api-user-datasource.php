<?php
/**
 * @package    WordPress
 * @subpackage Fields_API
 */

/**
 * Fields API User Datasource class.
 *
 * @see WP_Fields_API_Datasource
 */
class WP_Fields_API_User_Datasource extends WP_Fields_API_Datasource {

	/**
	 * {@inheritdoc}
	 */
	public $type = 'user';

	/**
	 * {@inheritdoc}
	 */
	public function setup_data( $args ) {

		$data = array();

		// get_users with $args
		// format key=>value

		return $data;

	}

}