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

		// Make sure primary post fields are registered
		$this->register_term_fields( $wp_fields );

		$object_name = 'post';

		// Section: Post Title
		$section_id   = 'post_title';

		$section_args = array(
			'label'         => __( 'Title' ),
			'form'          => $this->id,
			'display_label' => false,
			'controls'      => array(),
		);

		$section_args['controls'][$this->id . '-post_title'] = array(
			'type'                  => 'text',
			'field'                 => 'post_title',
			'capabilities_callback' => array(), // @todo Add capabilities
			'internal'              => true,
			'display_label' => false,
		);

		$wp_fields->add_section( $this->object_type, $section_id, $object_name, $section_args );

		// Section: Post Content
		$section_id   = 'post_content';

		$section_args = array(
			'label'         => __( 'Post Content' ),
			'form'          => $this->id,
			'display_label' => false,
			'controls'      => array(),
		);

		$section_args['controls'][$this->id . '-post_content'] = array(
			'type'                  => 'wysiwyg',
			'field'                 => 'post_content',
			'capabilities_callback' => array(), // @todo Add capabilities
			'internal'              => true,
		);

		$wp_fields->add_section( $this->object_type, $section_id, $object_name, $section_args );

		// Section: Post Excerpt
		$section_id   = 'post_excerpt';

		$section_args = array(
			'label'         => __( 'Excerpt' ),
			'form'          => $this->id,
			'controls'      => array(),
		);

		$section_args['controls'][$this->id . '-post_excerpt'] = array(
			'type'                  => 'textarea',
			'field'                 => 'post_excerpt',
			'capabilities_callback' => array(), // @todo Add capabilities
			'internal'              => true,
			'description'           => sprintf( '%s <a href="https://codex.wordpress.org/Excerpt" target="_blank">%s</a>',
												__( 'Excerpts are optional hand-crafted summaries of your content that can be used in your theme.' ),
												__( 'Learn more about manual excerpts.')
										),
		);

		$wp_fields->add_section( $this->object_type, $section_id, $object_name, $section_args );

		// Section: Send Trackbacks
		$section_id   = 'trackback_url';

		$section_args = array(
			'label'         => __( 'Send Trackbacks' ),
			'form'          => $this->id,
			'display_label' => false,
			'controls'      => array(),
		);

		$section_args['controls'][$this->id . '-trackback_url'] = array(
			'label'                 => __( 'Send trackbacks to:' ),
			'type'                  => 'text',
			'field'                 => 'trackback_url',
			'capabilities_callback' => array(), // @todo Add capabilities
			'internal'              => true,
			'description'           => sprintf( '%s<br /><br />%s <a href="https://codex.wordpress.org/Introduction_to_Blogging#Managing_Comments" target="_blank">%s</a>%s',
												__( '(Separate multiple URLs with spaces)' ),
												__( 'Trackbacks are a way to notify legacy blog systems that you’ve linked to them. If you link other WordPress sites, they’ll be notified automatically using' ),
												__( 'pingbacks' ),
												__( ', no other action necessary.' )
										),
		);

		$wp_fields->add_section( $this->object_type, $section_id, $object_name, $section_args );

		// Section: Custom Fields
		$section_id   = 'custom_meta';

		$section_args = array(
			'label'         => __( 'Custom Fields' ),
			'form'          => $this->id,
			'controls'      => array(),
		);

		$section_args['controls'][$this->id . '-custom_meta'] = array(
			'type'                  => 'custom_meta', // @todo Create custom meta control
			'field'                 => 'custom_meta',
			'capabilities_callback' => array(), // @todo Add capabilities
			'internal'              => true,
			'description'           => sprintf( '%s <a href="https://codex.wordpress.org/Using_Custom_Fields" target="_blank">%s</a>',
												__( 'Custom fields can be used to add extra metadata to a post that you can' ),
												__( 'use in your theme.')
										),
		);

		$wp_fields->add_section( $this->object_type, $section_id, $object_name, $section_args );

		// Section: Discussion
		$section_id   = 'discussion';

		$section_args = array(
			'label'         => __( 'Discussion' ),
			'form'          => $this->id,
			'display_label' => false,
			'controls'      => array(),
		);

		$section_args['controls'][$this->id . '-comment_status'] = array(
			'type'                  => 'checkbox',
			'field'                 => 'comment_status',
			'checkbox_value'        => 'open',
			'capabilities_callback' => array(), // @todo Add capabilities
			'internal'              => true,
			'description'           => __( 'Allow comments.' ),
		);

		$section_args['controls'][$this->id . '-ping_status'] = array(
			'type'                  => 'checkbox',
			'field'                 => 'ping_status',
			'checkbox_value'        => 'open',
			'capabilities_callback' => array(), // @todo Add capabilities
			'internal'              => true,
			'description'           => sprintf( '%s <a href="https://codex.wordpress.org/Excerpt" target="_blank">%s</a> %s',
												__( 'Allow' ),
												__( 'trackbacks and pingbacks'),
												__( 'on this page.')
										),
		);

		$wp_fields->add_section( $this->object_type, $section_id, $object_name, $section_args );

		// Section: Post Name
		$section_id   = 'post_name';

		$section_args = array(
			'label'         => __( 'Slug' ),
			'form'          => $this->id,
			'controls'      => array(),
		);

		$section_args['controls'][$this->id . '-post_name'] = array(
			'type'                  => 'text',
			'field'                 => 'post_name',
			'capabilities_callback' => array(), // @todo Add capabilities
			'internal'              => true,
		);

		$wp_fields->add_section( $this->object_type, $section_id, $object_name, $section_args );

		// Section: Post Author
		$section_id   = 'post_author';

		$section_args = array(
			'label'         => __( 'Author' ),
			'form'          => $this->id,
			'controls'      => array(),
		);

		$section_args['controls'][$this->id . '-post_author'] = array(
			'type'                  => 'select',
			'field'                 => 'post_author',
			'capabilities_callback' => array(), // @todo Add capabilities
			'internal'              => true,
			'datasource'            => array(
											'type'     => 'user',
											'get_args' => array(
												'role' => array( 'Author', 'Editor', 'Administrator' ),
										),
			),
		);

		$wp_fields->add_section( $this->object_type, $section_id, $object_name, $section_args );

		// Section: Post Format
		$section_id   = 'post_format';

		$section_args = array(
			'label'         => __( 'Format' ),
			'form'          => $this->id,
			'controls'      => array(),
		);

		$section_args['controls'][$this->id . '-post_format'] = array(
			'type'                  => 'radio',
			'field'                 => 'post_format',
			'capabilities_callback' => array(), // @todo Add capabilities
			'internal'              => true,
			'choices'               => array(
										'0'         => __( 'Standard' ),
										'aside'     => __( 'Aside' ),
										'image'     => __( 'Image' ),
										'video'     => __( 'Video' ),
										'quote'     => __( 'Quote' ),
										'link'      => __( 'Link' ),
										'gallery'   => __( 'Gallery' ),
										'audio'     => __( 'Audio' ),
										'chat'      => __( 'Chat' ),
									),
			'context'               => 'side'
		);

		$wp_fields->add_section( $this->object_type, $section_id, $object_name, $section_args );


	}

	/**
	 * Register post fields once for all post forms
	 *
	 * @param WP_Fields_API $wp_fields
	 */
	public function register_term_fields( $wp_fields ) {

		static $registered;

		if ( $registered ) {
			return;
		}

		$registered = true;

		$wp_fields->add_field( $this->object_type, 'post_title' );
		$wp_fields->add_field( $this->object_type, 'post_content' );
		$wp_fields->add_field( $this->object_type, 'post_excerpt' );
		$wp_fields->add_field( $this->object_type, 'trackback_url' );
		$wp_fields->add_field( $this->object_type, 'custom_meta' );
		$wp_fields->add_field( $this->object_type, 'comment_status' );
		$wp_fields->add_field( $this->object_type, 'ping_status' );
		$wp_fields->add_field( $this->object_type, 'post_name' );
		$wp_fields->add_field( $this->object_type, 'post_author' );
		$wp_fields->add_field( $this->object_type, 'post_format' );

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