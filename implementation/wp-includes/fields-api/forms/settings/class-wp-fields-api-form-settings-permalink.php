<?php
/**
 * This is an implementation for Fields API for the Permalinks form in the WordPress Dashboard
 *
 * @package    WordPress
 * @subpackage Fields_API
 */

/**
 * Class WP_Fields_API_Form_Settings_Permalinks
 */
class WP_Fields_API_Form_Settings_Permalink extends WP_Fields_API_Form_Settings {

	/**
	 * {@inheritdoc}
	 */
	public function register_fields( $wp_fields ) {

		///////////////////////////
		// Core: Common Settings //
		///////////////////////////

		$section_id   = $this->id . '-options-permalink';
		$section_args = array(
			'label'       => __( 'Common Settings' ),
			'form'        => $this->id,
			'description' => sprintf( __( 'WordPress offers you the ability to create a custom URL structure for your permalinks and archives. Custom URL structures can improve the aesthetics, usability, and forward-compatibility of your links. A <a href="%s">number of tags are available</a>, and here are some examples to get you started.' ), 'https://codex.wordpress.org/Using_Permalinks' ),
			'controls'    => array(),
		);

		// Control: Permalink Structure
		$section_args['controls']['selection'] = array(
			'type'     => 'radio-multi-label',
			'choices'  => array(
				__( 'Plain' )            => array(
					'value'        => '',
					'example_text' => '<code>' . site_url( '?p=123' ) . '</code>',
				),
				__( 'Day and name' )     => array(
					'value'        => '/%year%/%monthnum%/%day%/%postname%/',
					'example_text' => '<code>' . site_url( '?p=123' ) . '</code>',
				),
				__( 'Month and name' )   => array(
					'value'        => '/%year%/%monthnum%/%postname%/',
					'example_text' => '<code>' . site_url( date( 'm/d' ) . '/sample-post/' ) . '</code>',
				),
				__( 'Numeric' )          => array(
					'value'        => '/archives/%post_id%/',
					'example_text' => '<code>' . site_url( 'archives/123/' ) . '</code>',
				),
				__( 'Post name' )        => array(
					'value'        => '/%postname%/',
					'example_text' => '<code>' . site_url( 'sample-post/ ' ) . '</code>',
				),
				__( 'Custom Structure' ) => array(
					'value'        => 'custom',
					'example_text' => '<code>' . site_url() . '</code>'
				)
			),
			'internal' => true,
		);

		$this->add_section( $section_id, $section_args );

		////////////////////
		// Core: Optional //
		////////////////////

		$section_id   = $this->id . '-options-permalink-optional';
		$section_args = array(
			'label'       => __( 'Optional' ),
			'form'        => $this->id,
			'description' => sprintf( __( 'If you like, you may enter custom structures for your category and tag URLs here. For example, using <code>topics</code> as your category base would make your category links like <code>%s</code>. If you leave these blank the defaults will be used.' ), site_url( 'topics/uncategorized' ) ),
			'controls'    => array(),
		);

		// Control: Category base
		$section_args['controls']['category_base'] = array(
			'type'        => 'text',
			'label'       => __( 'Category base' ),
			'input_attrs' => array(
				'id'    => 'category_base',
				'class' => 'regular-text code',
			),
			'internal'    => true,
		);

		// Control: Tag base
		$section_args['controls']['tag_base'] = array(
			'type'        => 'text',
			'label'       => __( 'Tag base' ),
			'input_attrs' => array(
				'id'    => 'tag_base',
				'class' => 'regular-text code',
			),
			'internal'    => true,
		);

		$this->add_section( $section_id, $section_args );

	}
}