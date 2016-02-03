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

		$choices = array(
			'0'         => __( 'Standard' ),
			'aside'     => __( 'Aside' ),
			'chat'      => __( 'Chat' ),
			'gallery'   => __( 'Gallery' ),
			'link'      => __( 'Link' ),
			'image'     => __( 'Image' ),
			'quote'     => __( 'Quote' ),
			'status'    => __( 'Status' ),
			'video'     => __( 'Video' ),
			'audio'     => __( 'Audio' )
		);

		return $choices;
	}

}