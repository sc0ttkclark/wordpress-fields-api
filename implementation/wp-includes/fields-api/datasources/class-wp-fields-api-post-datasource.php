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
	public $hierarchical = true;

	/**
	 * {@inheritdoc}
	 */
	public $hierarchical_fields = array(
		'id'            => 'ID',
		'parent'        => 'post_parent',
		'title'         => 'post_title',
		'default_title' => '',
	);

	/**
	 * @var bool Whether to exclude current item ID from list
	 */
	public $exclude_current_item_id = false;

	/**
	 * @var bool Whether to exclude current item ID and descendants from list
	 */
	public $exclude_tree_current_item_id = false;

	/**
	 * {@inheritdoc}
	 */
	public function setup_data( $args ) {

		/*$item_id = $this->get_item_id();

		if ( $this->exclude_current_item_id && 0 < $item_id ) {
			if ( ! isset( $args['post__not_in'] ) ) {
				$args['post__not_in'] = array();
			}

			$args['post__not_in'][] = $item_id;
		}

		if ( $this->exclude_tree_current_item_id && 0 < $item_id ) {
			if ( ! isset( $args['post__not_in'] ) ) {
				$args['post_parent__not_in'] = array();
			}

			$args['post_parent__not_in'][] = $item_id;
		}*/

		// @todo Revisit limit later
		$args['posts_per_page'] = 100;

		$items = get_posts( $args );

		$data = $this->setup_data_recurse( array(), $items );

		return $data;

	}

}