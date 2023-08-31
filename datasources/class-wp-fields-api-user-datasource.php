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
	protected function setup_data( $args, $control ) {

		$data = array();

		$fields = array(
			'ID',
			'user_login'
		);

		if ( ! empty( $args['fields'] ) ) {
			$args['fields'] = array_merge( $fields, (array) $args['fields'] );
		} else {
			$args['fields']   = $fields;
			$args['fields'][] = 'display_name';
		}

		$last_field = end( $args['fields'] );

		$items = get_users( $args );

		foreach ( $items as $item ) {
			$display = $item->user_login;

			if ( ! empty( $item->{$last_field} ) ) {
				$display = $item->{$last_field};
			}

			$data[ $item->ID ] = $display;
		}

		return $data;

	}

}