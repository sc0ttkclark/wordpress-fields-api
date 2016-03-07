<?php
/**
 * This is an implementation for Fields API for the Post editor in the WordPress Dashboard
 *
 * @package    WordPress
 * @subpackage Fields_API
 */

/**
 * Class WP_Fields_API_Form_Post
 */
class WP_Fields_API_Form_Post extends WP_Fields_API_Form {

	/**
	 * {@inheritdoc}
	 */
	public $default_section_type = 'meta-box';

	/**
	 * {@inheritdoc}
	 */
	public function register_fields( $wp_fields ) {

		add_action( 'save_post', array( $this, 'wp_save_post' ), 10, 2 );

		//////////////////////
		// Core: Post Title //
		//////////////////////

		$section_id   = 'post_title';
		$section_args = array(
			'label'         => __( 'Title' ),
			'form'          => $this->id,
			'display_label' => false,
			'controls'      => array(),
		);

		// Control: Title
		$section_args['controls']['post_title'] = array(
			'type'                  => 'text',
			'capabilities_callback' => array(), // @todo Add capabilities
			'display_label'         => false,
			'internal'              => true,
		);

		$this->add_section( $section_id, $section_args );

		////////////////////////
		// Core: Post Content //
		////////////////////////

		$section_id   = $this->id . '_post_content';
		$section_args = array(
			'label'         => __( 'Post Content' ),
			'form'          => $this->id,
			'display_label' => false,
			'controls'      => array(),
		);

		// Control: Content
		$section_args['controls']['post_content'] = array(
			'type'                  => 'wysiwyg',
			'capabilities_callback' => array(), // @todo Add capabilities
			'internal'              => true,
		);

		$this->add_section( $section_id, $section_args );

		////////////////////////
		// Core: Post Excerpt //
		////////////////////////

		$section_id   = $this->id . '_post_excerpt';
		$section_args = array(
			'label'    => __( 'Excerpt' ),
			'form'     => $this->id,
			'controls' => array(),
		);

		// Control: Excerpt
		$section_args['controls']['post_excerpt'] = array(
			'type'                  => 'textarea',
			'capabilities_callback' => array(), // @todo Add capabilities
			'description'           => sprintf( '%s <a href="https://codex.wordpress.org/Excerpt" target="_blank">%s</a>', __( 'Excerpts are optional hand-crafted summaries of your content that can be used in your theme.' ), __( 'Learn more about manual excerpts.' ) ),
			'internal'              => true,
		);

		$this->add_section( $section_id, $section_args );

		///////////////////////////
		// Core: Send Trackbacks //
		///////////////////////////

		$section_id   = $this->id . '_trackback_url';
		$section_args = array(
			'label'         => __( 'Send Trackbacks' ),
			'form'          => $this->id,
			'display_label' => false,
			'controls'      => array(),
		);

		// Control: Send trackbacks to
		$section_args['controls']['trackback_url'] = array(
			'label'                 => __( 'Send trackbacks to:' ),
			'type'                  => 'text',
			'capabilities_callback' => array(), // @todo Add capabilities
			'description'           => sprintf( '%s<br /><br />%s <a href="https://codex.wordpress.org/Introduction_to_Blogging#Managing_Comments" target="_blank">%s</a>%s', __( '(Separate multiple URLs with spaces)' ), __( 'Trackbacks are a way to notify legacy blog systems that you’ve linked to them. If you link other WordPress sites, they’ll be notified automatically using' ), __( 'pingbacks' ), __( ', no other action necessary.' ) ),
			'internal'              => true,
		);

		$this->add_section( $section_id, $section_args );

		/////////////////////////
		// Core: Custom Fields //
		/////////////////////////

		$section_id   = $this->id . '_custom_meta';
		$section_args = array(
			'label'    => __( 'Custom Fields' ),
			'form'     => $this->id,
			'controls' => array(),
		);

		// Control: Custom Meta
		$section_args['controls']['custom_meta'] = array(
			'type'                  => 'custom_meta', // @todo Create custom meta control
			'capabilities_callback' => array(), // @todo Add capabilities
			'description'           => sprintf( '%s <a href="https://codex.wordpress.org/Using_Custom_Fields" target="_blank">%s</a>', __( 'Custom fields can be used to add extra metadata to a post that you can' ), __( 'use in your theme.' ) ),
			'internal'              => true,
		);

		$this->add_section( $section_id, $section_args );

		//////////////////////
		// Core: Discussion //
		//////////////////////

		$section_id   = $this->id . '_discussion';
		$section_args = array(
			'label'         => __( 'Discussion' ),
			'form'          => $this->id,
			'display_label' => false,
			'controls'      => array(),
		);

		// Control: Comment Status
		$section_args['controls']['comment_status'] = array(
			'type'                  => 'checkbox',
			'checkbox_value'        => 'open',
			'capabilities_callback' => array(), // @todo Add capabilities
			'description'           => __( 'Allow comments.' ),
			'internal'              => true,
			// @todo Default should be based on site settings
		);

		// Control: Ping Status
		$section_args['controls']['ping_status'] = array(
			'type'                  => 'checkbox',
			'checkbox_value'        => 'open',
			'capabilities_callback' => array(), // @todo Add capabilities
			'description'           => sprintf( '%s <a href="https://codex.wordpress.org/Excerpt" target="_blank">%s</a> %s', __( 'Allow' ), __( 'trackbacks and pingbacks' ), __( 'on this page.' ) ),
			'internal'              => true,
			// @todo Default should be based on site settings
		);

		$this->add_section( $section_id, $section_args );

		/////////////////////
		// Core: Post Name //
		/////////////////////

		$section_id   = $this->id . '_post_name';
		$section_args = array(
			'label'    => __( 'Slug' ),
			'form'     => $this->id,
			'controls' => array(),
		);

		// Control: Post Name
		$section_args['controls']['post_name'] = array(
			'type'                  => 'text',
			'capabilities_callback' => array(), // @todo Add capabilities
			'internal'              => true,
		);

		$this->add_section( $section_id, $section_args );

		//////////////////
		// Core: Author //
		//////////////////

		$section_id   = $this->id . '_post_author';
		$section_args = array(
			'label'    => __( 'Author' ),
			'form'     => $this->id,
			'controls' => array(),
		);

		// Control: Author
		$section_args['controls']['post_author'] = array(
			'type'                  => 'select',
			'datasource'            => array(
				'type'     => 'user',
				'get_args' => array(
					'role' => array(
						'Author',
						'Editor',
						'Administrator'
					),
				),
			),
			'capabilities_callback' => array(), // @todo Add capabilities
			'internal'              => true,
		);

		$this->add_section( $section_id, $section_args );

		//////////////////
		// Core: Format //
		//////////////////

		$section_id   = $this->id . '_post_format';
		$section_args = array(
			'label'    => __( 'Format' ),
			'form'     => $this->id,
			'context'  => 'side',
			'controls' => array(),
		);

		// Control: Format
		$section_args['controls']['post_format'] = array(
			'type'                  => 'radio',
			'datasource'            => 'post_format',
			'capabilities_callback' => array(), // @todo Add capabilities
			'internal'              => true,
		);

		$this->add_section( $section_id, $section_args );

	}

	/**
	 * Save fields based on the current post
	 *
	 * @param int     $post_id
	 * @param WP_Post $post
	 */
	public function wp_save_post( $post_id, $post ) {

		remove_action( 'save_post', array( $this, 'wp_save_post' ) );

		$this->save_fields( $post->ID, $post->post_type );

		add_action( 'save_post', array( $this, 'wp_save_post' ), 10, 2 );

	}

	/**
	 * {@inheritdoc}
	 */
	public function save_fields( $item_id = null, $object_name = null ) {

		// Save additional fields
		return parent::save_fields( $item_id, $object_name );

	}

}