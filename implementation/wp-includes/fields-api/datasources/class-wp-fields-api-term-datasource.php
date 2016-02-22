<?php
/**
 * @package    WordPress
 * @subpackage Fields_API
 */

/**
 * Fields API Term Datasource class.
 *
 * @see WP_Fields_API_Datasource
 */
class WP_Fields_API_Term_Datasource extends WP_Fields_API_Datasource {

	/**
	 * {@inheritdoc}
	 */
	public $type = 'term';

	/**
	 * {@inheritdoc}
	 */
	public $hierarchical = true;

	/**
	 * {@inheritdoc}
	 */
	public $hierarchical_fields = array(
		'id'            => 'term_id',
		'parent'        => 'parent',
		'title'         => 'name',
		'default_title' => '',
	);

	/**
	 * @var string Taxonomy name
	 */
	public $taxonomy;

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
	protected function setup_data( $args, $control ) {

		$data = array();

		if ( $control ) {
			// Handle default taxonomy
			if ( empty( $this->taxonomy ) && 'term' == $control->object_type && ! empty( $control->object_name ) ) {
				$this->taxonomy = $control->object_name;
			}

			// Handle exclusion
			$item_id = $control->get_item_id();

			if ( ! isset( $args['exclude'] ) && $this->exclude_current_item_id && 0 < $item_id ) {
				$args['exclude'] = $item_id;
			}

			if ( ! isset( $args['exclude_tree'] ) && $this->exclude_tree_current_item_id && 0 < $item_id ) {
				$args['exclude_tree'] = $item_id;
			}
		}

		if ( empty( $this->taxonomy ) ) {
			return $data;
		}

		// @todo Revisit limit later
		$args['number'] = 100;

		$items = get_terms( $this->taxonomy, $args );

		$data = $this->setup_data_recurse( $data, $items );

		return $data;

	}

}