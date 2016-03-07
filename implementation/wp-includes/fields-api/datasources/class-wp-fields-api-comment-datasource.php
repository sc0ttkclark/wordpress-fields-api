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
	public $hierarchical = true;

	/**
	 * {@inheritdoc}
	 */
	public $hierarchical_fields = array(
		'id'            => 'comment_ID',
		'parent'        => 'comment_parent',
		'title'         => 'comment_content',
		'default_title' => '',
	);

	/**
	 * @var string Comment type name
	 */
	public $comment_type;

	/**
	 * @var bool Whether to exclude current item ID from list
	 */
	public $exclude_current_item_id = false;

	/**
	 * {@inheritdoc}
	 */
	protected function setup_data( $args, $control ) {

		$data = array();

		if ( $control ) {
			// Handle default comment type
			if ( empty( $this->comment_type ) && 'comment' == $control->object_type && ! empty( $control->object_name ) ) {
				$this->comment_type = $control->object_name;
			}

			// Handle exclusion
			$item_id = $control->get_item_id();

			if ( $this->exclude_current_item_id && 0 < $item_id ) {
				if ( ! isset( $args['comment__not_in'] ) ) {
					$args['comment__not_in'] = array();
				}

				$args['comment__not_in'][] = $item_id;
			}
		}

		if ( $this->comment_type ) {
			// Set comment type
			$args['type'] = $this->comment_type;
		}

		// @todo Revisit limit later
		$args['number'] = 100;

		$items = get_comments( $args );

		$data = $this->setup_data_recurse( $data, $items );

		return $data;

	}

}