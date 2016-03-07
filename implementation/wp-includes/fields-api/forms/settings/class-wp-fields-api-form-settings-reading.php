<?php
/**
 * This is an implementation for Fields API for the Reading form in the WordPress Dashboard
 *
 * @package    WordPress
 * @subpackage Fields_API
 */

/**
 * Class WP_Fields_API_Form_Settings_Reading
 */
class WP_Fields_API_Form_Settings_Reading extends WP_Fields_API_Form_Settings {

	/**
	 * {@inheritdoc}
	 */
	public function register_fields( $wp_fields ) {

		////////////////////
		// Core: Optional //
		////////////////////

		$section_id   = $this->id . '-options-reading';
		$section_args = array(
			'label'         => __( 'Reading Settings' ),
			'form'          => $this->id,
			'display_label' => false,
			'controls'      => array(),
		);

		// Control: Front page displays
		$section_args['controls']['show_on_front'] = array(
			'type'        => 'radio',
			'label'       => __( 'Front page displays' ),
			'input_attrs' => array(
				'id'    => 'show_on_front',
				'class' => 'tog'
			),
			'choices'     => array(
				'posts' => __( 'Your latest posts' ),
				'pages' => sprintf( __( 'A <a href="%s">static page</a> (select below)' ), get_admin_url( 'edit.php?post_type=page' ) ),
			),
			'internal'    => true,
		);

		// Control: Front page displays
		$section_args['controls']['posts_per_page'] = array(
			'type'        => 'number-inline-desc',
			'label'       => __( 'Blog pages show at most' ),
			'inline_text' => 'posts',
			'input_attrs' => array(
				'id'    => 'posts_per_page',
				'min'   => 1,
				'step'  => 1,
				'class' => 'small-text',
			),
			'internal'    => true,
		);

		// Control: Syndication feeds show the most recent
		$section_args['controls']['posts_per_rss'] = array(
			'type'        => 'number-inline-desc',
			'label'       => __( 'Syndication feeds show the most recent' ),
			'inline_text' => 'items',
			'input_attrs' => array(
				'id'    => 'posts_per_rss',
				'min'   => 1,
				'step'  => 1,
				'class' => 'small-text',
			),
			'internal'    => true,
		);

		// text-inline-desc
		// @todo we need a control for nested dropdowns for show on front and posts page dropdowns connected to Front Page Display

		// @todo we need a control that is a text control with inline description after for posts per page and posts per page in feeds

		// Control: For each article in a feed, show
		$section_args['controls']['rss_use_excerpt'] = array(
			'type'        => 'radio',
			'label'       => __( 'For each article in a feed, show' ),
			'input_attrs' => array(
				'id'   => 'rss_use_excerpt',
			),
			'choices'     => array(
				'0' => __( 'Full text' ),
				'1' => __( 'Summary' ),
			),
			'internal'    => true,
		);

		// Control: It is up to search engines to honor this request.
		$section_args['controls']['blog_public'] = array(
			'type'        => 'checkbox',
			'label'       => __( 'Search Engine Visibility' ),
			'description' => __( 'It is up to search engines to honor this request.' ),
			'input_attrs' => array(
				'id'   => 'blog_public',
			),
			'choices'     => array(
				'0' => __( 'Discourage search engines from indexing this site' ),
			),
			'internal'    => true,
		);

		$this->add_section( $section_id, $section_args );

	}

}