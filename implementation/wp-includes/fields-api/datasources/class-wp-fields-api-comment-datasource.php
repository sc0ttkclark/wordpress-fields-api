<?php
/**
 * @package    WordPress
 * @subpackage Fields_API
 */

/**
 * Fields API Comment Datasource class.
 *
 * @see WP_Fields_API_Datasource
 */
class WP_Fields_API_Comment_Datasource extends WP_Fields_API_Datasource {

	/**
	 * {@inheritdoc}
	 */
	public $type = 'comment';

	/**
	 * {@inheritdoc}
	 */
	protected function setup_data( $args, $control ) {

		$data = array();

		// get_comments with $args
		// format key=>value

		return $data;

	}

}