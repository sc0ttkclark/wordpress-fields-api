<?php
/**
 * @package    WordPress
 * @subpackage Fields_API
 */

/**
 * Fields API Dropdown Post Format Control class.
 *
 * @see WP_Fields_API_Dropdown_Post_Formats_Control
 */
class WP_Fields_API_Dropdown_Post_Format_Control extends WP_Fields_API_Select_Control {

	/**
	 * {@inheritdoc}
	 */
	public $type = 'dropdown-post-format';

	/**
	 * {@inheritdoc}
	 */
	public function choices() {

		$choices = get_post_format_strings();

		// Make 'standard' be '0' and add to the front
		$choices = array_reverse( $choices, true );
		$choices['0'] = $choices['standard'];
		$choices = array_reverse( $choices, true );

		unset( $choices['standard'] );

		return $choices;
	}

}