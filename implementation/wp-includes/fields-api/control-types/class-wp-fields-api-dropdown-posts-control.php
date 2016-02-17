<?php
/**
 * @package    WordPress
 * @subpackage Fields_API
 */

/**
 * Fields API Dropdown Posts Control class.
 *
 * @see WP_Fields_API_Control
 */
class WP_Fields_API_Dropdown_Posts_Control extends WP_Fields_API_Select_Control {

	/**
	 * {@inheritdoc}
	 */
	public $type = 'dropdown-posts';

	/**
	 * @var string Post type
	 */
	public $post_type;

	/**
	 * @var array Arguments to send to get_posts
	 */
	public $get_args = array();

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
	public function choices() {

		$choices = array();

		// Handle default post type
		if ( empty( $this->post_type ) && 'post' == $this->object_type && ! empty( $this->object_name ) ) {
			$this->post_type = $this->object_name;
		}

		if ( empty( $this->post_type ) ) {
			return $choices;
		}

		$args = $this->get_args;

		$args['post_type'] = $this->post_type;

		$item_id = $this->get_item_id();

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
		}

		// @todo Revisit limit later
		$args['posts_per_page'] = 100;

		$posts = get_posts( $args );

		if ( $posts && ! is_wp_error( $posts ) ) {
			$choices = $this->get_post_choices_recurse( $choices, $posts );
		}

		return $choices;

	}

	/**
	 * Recursively build choices array the full depth
	 *
	 * @param array     $choices List of choices.
	 * @param WP_Post[] $posts   List of posts.
	 * @param int       $depth   Current depth.
	 * @param int       $parent  Current parent post ID.
	 *
	 * @return array
	 */
	public function get_post_choices_recurse( $choices, $posts, $depth = 0, $parent = 0 ) {

		$pad = str_repeat( '&nbsp;', $depth * 3 );

		$post_type = '';

		foreach ( $posts as $post ) {
			$is_hierarchical = false;

			if ( $post_type !== $post->post_type && is_post_type_hierarchical( $post->post_type ) ) {
				$is_hierarchical = true;
			}

			// Avoid multiple calls to is_post_type_hierarchical when posts are using the same post type
			$post_type = $post->post_type;

			if ( ! $is_hierarchical || $parent == $post->post_parent ) {
				$title = $post->post_title;

				if ( '' === $title ) {
					/* translators: %d: term_id of a term */
					$title = sprintf( __( '#%d (no title)' ), $post->ID );
				}

				$choices[ $post->ID ] = $pad . $title;

				if ( $is_hierarchical ) {
					$choices = $this->get_post_choices_recurse( $choices, $posts, $depth + 1, $post->ID );
				}
			}
		}

		return $choices;

	}

}