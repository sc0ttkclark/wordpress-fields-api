<?php
/**
 * Fields API Dropdown Pages Control class.
 *
 * @see WP_Fields_API_Control
 */
class WP_Fields_API_Dropdown_Pages_Control extends WP_Fields_API_Select_Control {

	/**
	 * Setup page choices for use by control
	 *
	 * @return array
	 */
	public function choices() {

		$choices = array(
			'0' => __( '&mdash; Select &mdash;' ),
		);

		$pages = get_pages();

		$choices = $this->get_page_choices_recurse( $choices, $pages );

		return $choices;

	}

	/**
	 * Recursively build choices array the full depth
	 *
	 * @param array     $choices List of choices.
	 * @param WP_Post[] $pages   List of pages.
	 * @param int       $depth   Current depth.
	 * @param int       $parent  Current parent page ID.
	 *
	 * @return array
	 */
	public function get_page_choices_recurse( $choices, $pages, $depth = 0, $parent = 0 ) {

		$pad = str_repeat( '&nbsp;', $depth * 3 );

		foreach ( $pages as $page ) {
			if ( $parent == $page->post_parent ) {
				$title = $page->post_title;

				if ( '' === $title ) {
					/* translators: %d: ID of a post */
					$title = sprintf( __( '#%d (no title)' ), $page->ID );
				}

				/**
				 * Filter the page title when creating an HTML drop-down list of pages.
				 *
				 * @since 3.1.0
				 *
				 * @param string $title Page title.
				 * @param object $page  Page data object.
				 */
				$title = apply_filters( 'list_pages', $title, $page );

				$choices[ $page->ID ] = $pad . $title;

				$choices = $this->get_page_choices_recurse( $choices, $pages, $depth + 1, $page->ID );
			}
		}

		return $choices;

	}

}