<?php
/**
 * @package    WordPress
 * @subpackage Fields_API
 */

/**
 * Fields API Page Datasource class.
 *
 * @see WP_Fields_API_Datasource
 */
class WP_Fields_API_Page_Datasource extends WP_Fields_API_Post_Datasource {

	/**
	 * {@inheritdoc}
	 */
	public $type = 'page';

	/**
	 * {@inheritdoc}
	 */
	public function setup_data( $args ) {

		$items = get_pages( $args );

		$data = $this->setup_data_recurse( array(), $items );

		return $data;

	}

}