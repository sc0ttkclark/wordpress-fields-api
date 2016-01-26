<?php
/**
 * Fields API Dropdown Pages Control class.
 *
 * @see WP_Fields_API_Control
 */
class WP_Fields_API_Dropdown_Terms_Control extends WP_Fields_API_Select_Control {

	/**
	 * @var string Taxonomy name
	 */
	public $taxonomy;

	/**
	 * @var array Arguments to send to get_terms
	 */
	public $get_args = array();

	/**
	 * @var bool Whether to exclude current item ID from list
	 */
	public $exclude_current_item_id = false;

	/**
	 * @var bool Whether to exclude current item ID and decendents from list
	 */
	public $exclude_tree_current_item_id = false;

	/**
	 * @var string Placeholder text for choices
	 */
	public $placeholder_text = '';

	/**
	 * Setup term choices for use by control
	 *
	 * @return array
	 */
	public function choices() {

		$placeholder_text = $this->placeholder_text;

		if ( '' === $placeholder_text ) {
			$placeholder_text = __( '&mdash; Select &mdash;' );
		}

		$choices = array(
			'0' => $placeholder_text,
		);

		if ( empty( $this->taxonomy ) ) {
			return $choices;
		}

		$args = $this->get_args;

		if ( ! isset( $args['exclude'] ) && $this->exclude_current_item_id && 0 < $this->item_id ) {
			$args['exclude'] = $this->item_id;
		}

		if ( ! isset( $args['exclude_tree'] ) && $this->exclude_tree_current_item_id && 0 < $this->item_id ) {
			$args['exclude_tree'] = $this->item_id;
		}

		$terms = get_terms( $this->taxonomy, $args );

		if ( $terms && ! is_wp_error( $terms ) ) {
			$choices = $this->get_term_choices_recurse( $choices, $terms );
		}

		return $choices;

	}

	/**
	 * Recursively build choices array the full depth
	 *
	 * @param array     $choices List of choices.
	 * @param WP_Term[] $terms   List of terms.
	 * @param int       $depth   Current depth.
	 * @param int       $parent  Current parent term ID.
	 *
	 * @return array
	 */
	public function get_term_choices_recurse( $choices, $terms, $depth = 0, $parent = 0 ) {

		$pad = str_repeat( '&nbsp;', $depth * 3 );

		foreach ( $terms as $term ) {
			if ( $parent == $term->parent ) {
				$title = $term->name;

				if ( '' === $title ) {
					/* translators: %d: term_id of a term */
					$title = sprintf( __( '#%d (no title)' ), $term->term_id );
				}

				$choices[ $term->term_id ] = $pad . $title;

				$choices = $this->get_term_choices_recurse( $choices, $terms, $depth + 1, $term->term_id );
			}
		}

		return $choices;

	}

}