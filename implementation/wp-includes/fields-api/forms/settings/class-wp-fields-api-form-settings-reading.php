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

		// Sections
		$wp_fields->add_section( $this->object_type, $this->id . '-options-reading', null, array(
			'label'         => __( 'Reading Settings' ),
			'form'          => $this->id,
			'display_label' => false,
		) );

		// Controls
		/**
		 * Default Post Format
		 */
		$field_args = array(
			'control' => array(
				'type'        => 'radio',
				'section'     => $this->id . '-options-reading',
				'label'       => __( 'Front page displays' ),
				'input_attrs' => array(
					'id'    => 'show_on_front',
					'name'  => 'show_on_front',
					'class' => 'tog'
				),
				'choices'     => array(
					'posts' => __( 'Your latest posts' ),
					'pages' => sprintf( __( 'A <a href="%s">static page</a> (select below)' ), get_admin_url( 'edit.php?post_type=page' ) ),
				),
				'internal'    => true,
			),
		);
		$wp_fields->add_field( $this->object_type, 'show_on_front', null, $field_args );

		$field_args = array(
			'control' => array(
				'type'        => 'number-inline-desc',
				'section'     => $this->id . '-options-reading',
				'label'       => __( 'Blog pages show at most' ),
				'inline_text' => 'posts',
				'input_attrs' => array(
					'id'    => 'posts_per_page',
					'name'  => 'posts_per_page',
					'min'   => 1,
					'step'  => 1,
					'class' => 'small-text',
				),
				'internal'    => true,
			),
		);
		$wp_fields->add_field( $this->object_type, 'posts_per_page', null, $field_args );

		$field_args = array(
			'control' => array(
				'type'        => 'number-inline-desc',
				'section'     => $this->id . '-options-reading',
				'label'       => __( 'Syndication feeds show the most recent' ),
				'inline_text' => 'items',
				'input_attrs' => array(
					'id'    => 'posts_per_rss',
					'name'  => 'posts_per_rss',
					'min'   => 1,
					'step'  => 1,
					'class' => 'small-text',
				),
				'internal'    => true,
			),
		);
		$wp_fields->add_field( $this->object_type, 'posts_per_rss', null, $field_args );

		// text-inline-desc
		// @todo we need a control for nested dropdowns for show on front and posts page dropdowns connected to Front Page Display

		// @todo we need a control that is a text control with inline description after for posts per page and posts per page in feeds

		$field_args = array(
			'control' => array(
				'type'        => 'radio',
				'section'     => $this->id . '-options-reading',
				'label'       => __( 'For each article in a feed, show' ),
				'input_attrs' => array(
					'id'    => 'rss_use_excerpt',
					'name'  => 'rss_use_excerpt',
				),
				'choices'     => array(
					'0' => __( 'Full text' ),
					'1' => __( 'Summary' ),
				),
				'internal'    => true,
			),
		);
		$wp_fields->add_field( $this->object_type, 'rss_use_excerpt', null, $field_args );

		$field_args = array(
			'control' => array(
				'type'        => 'checkbox',
				'section'     => $this->id . '-options-reading',
				'label'       => __( 'Search Engine Visibility' ),
				'description' => __( 'It is up to search engines to honor this request.' ),
				'input_attrs' => array(
					'id'    => 'blog_public',
					'name'  => 'blog_public',
				),
				'choices'     => array(
					'0' => __( 'Discourage search engines from indexing this site' ),
				),
				'internal'    => true,
			),
		);
		$wp_fields->add_field( $this->object_type, 'blog_public', null, $field_args );
	}
}