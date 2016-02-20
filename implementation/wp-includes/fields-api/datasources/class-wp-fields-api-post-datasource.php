<?php
/**
 * @package    WordPress
 * @subpackage Fields_API
 */

/**
 * Fields API Post Datasource class.
 *
 * @see WP_Fields_API_Datasource
 */
class WP_Fields_API_Post_Datasource extends WP_Fields_API_Datasource {

	/**
	 * {@inheritdoc}
	 */
	public $type = 'post';

	/**
	 * {@inheritdoc}
	 */
	public function setup_data( $args ) {

		$data = array();

		// get_posts with $args
		// format key=>value

		return $data;

	}

}