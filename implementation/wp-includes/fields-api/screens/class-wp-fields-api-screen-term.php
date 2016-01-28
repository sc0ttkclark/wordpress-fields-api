<?php
/**
 * This is an implementation for Fields API for the Term screens in the WordPress Dashboard
 *
 * @package    WordPress
 * @subpackage Fields_API
 */

/**
 * Class WP_Fields_API_Screen_Term
 */
class WP_Fields_API_Screen_Term extends WP_Fields_API_Screen {

	/**
	 * {@inheritdoc}
	 */
	public function register_fields( $wp_fields ) {

		// Make sure primary term fields are registered
		$this->register_term_fields( $wp_fields );

		// @todo Saving: Figure out compatibility with wp_insert_term / wp_update_term usage in edit-tags.php
		// @todo Saving: Hook into create_term on save, check $object_name
		// @todo Saving: Hook into edit_term on save, check $object_name

		// @todo General: Controls need to output name="...."

		////////////////
		// Core: Term //
		////////////////

		$wp_fields->add_section( $this->object_type, $this->id . '-main', null, array(
			'title'         => __( 'Term' ),
			'screen'        => $this->id,
			'display_title' => false,
		) );

		$control_args = array(
			// @todo Needs validation callback
			// @todo Needs 'form-required' class added to control wrapper somehow
			'input_name'  => 'name',
			'type'        => 'text',
			'section'     => $this->id . '-main',
			'label'       => __( 'Name' ),
			'description' => __( 'The name is how it appears on your site.' ),
			'fields'      => 'name',
			'internal'    => true,
		);

		if ( 'term-add' == $this->id ) {
			$control_args['input_name'] = 'tag-name';
		}

		$wp_fields->add_control( $this->object_type, $this->id . '-name', null, $control_args );

		$control_args = array(
			'input_name'  => 'slug',
			'type'                  => 'text',
			'section'               => $this->id . '-main',
			'label'                 => __( 'Slug' ),
			'description'           => __( 'The "slug" is the URL-friendly version of the name. It is usually all lowercase and contains only letters, numbers, and hyphens.' ),
			'capabilities_callback' => array( $this, 'capability_is_global_terms_disabled' ),
			'fields'                => 'slug',
			'internal'              => true,
		);

		$wp_fields->add_control( $this->object_type, $this->id . '-slug', null, $control_args );

		$control_args = array(
			'input_name'  => 'parent',
			'type'                         => 'dropdown-terms',
			'section'                      => $this->id . '-main',
			'label'                        => __( 'Parent' ),
			// @todo This description is only shown for 'category' == $object_name
			// @todo Generic description for taxonomies, new label for register_taxonomy?
			'description'                  => __( 'Categories, unlike tags, can have a hierarchy. You might have a Jazz category, and under that have children categories for Bebop and Big Band. Totally optional.' ),
			'capabilities_callback'        => array( $this, 'capability_is_taxonomy_hierarchical' ),
			'exclude_tree_current_item_id' => true,
			'placeholder_text'             => __( 'None' ),
			'fields'                       => 'parent',
			'internal'                     => true,
		);

		$wp_fields->add_control( $this->object_type, $this->id . '-parent', null, $control_args );

		$control_args = array(
			'input_name'  => 'description',
			'type'        => 'textarea',
			'section'     => $this->id . '-main',
			'label'       => __( 'Description' ),
			'description' => __( 'The description is not prominent by default; however, some themes may show it.' ),
			'input_attrs' => array(
				'rows' => '5',
				'cols' => '40',
			),
			'fields'      => 'description',
			'internal'    => true,
		);

		$wp_fields->add_control( $this->object_type, $this->id . '-description', null, $control_args );

		// Add example fields
		parent::register_fields( $wp_fields );

		return;

		// @todo Maintain some sort of filter on the $args, see taxonomy_parent_dropdown_args, has to be done during render of control
		$dropdown_args = array(
			'hide_empty'       => 0,
			'hide_if_empty'    => false,
			'taxonomy'         => $taxonomy,
			'name'             => 'parent',
			'orderby'          => 'name',
			'hierarchical'     => true,
			'show_option_none' => __( 'None' ),
		);

		/**
		 * Filter the taxonomy parent drop-down on the Edit Term page.
		 *
		 * @since 3.7.0
		 * @since 4.2.0 Added `$context` parameter.
		 *
		 * @param array   $dropdown_args    {
		 *                                  An array of taxonomy parent drop-down arguments.
		 *
		 * @type int|bool $hide_empty       Whether to hide terms not attached to any posts. Default 0|false.
		 * @type bool     $hide_if_empty    Whether to hide the drop-down if no terms exist. Default false.
		 * @type string   $taxonomy         The taxonomy slug.
		 * @type string   $name             Value of the name attribute to use for the drop-down select element.
		 *                                      Default 'parent'.
		 * @type string   $orderby          The field to order by. Default 'name'.
		 * @type bool     $hierarchical     Whether the taxonomy is hierarchical. Default true.
		 * @type string   $show_option_none Label to display if there are no terms. Default 'None'.
		 * }
		 *
		 * @param string  $taxonomy         The taxonomy slug.
		 * @param string  $context          Filter context. Accepts 'new' or 'edit'.
		 */
		$dropdown_args = apply_filters( 'taxonomy_parent_dropdown_args', $dropdown_args, $taxonomy, 'new' );

		// @todo Need compatibility hooks added for within <table> markup

		// Back compat hooks
		if ( 'category' == $taxonomy ) {
			/**
			 * Fires after the Edit Category form fields are displayed.
			 *
			 * @since      2.9.0
			 * @deprecated 3.0.0 Use {$taxonomy}_edit_form_fields instead.
			 *
			 * @param object $tag Current category term object.
			 */
			do_action( 'edit_category_form_fields', $tag );
		} elseif ( 'link_category' == $taxonomy ) {
			/**
			 * Fires after the Edit Link Category form fields are displayed.
			 *
			 * @since      2.9.0
			 * @deprecated 3.0.0 Use {$taxonomy}_edit_form_fields instead.
			 *
			 * @param object $tag Current link category term object.
			 */
			do_action( 'edit_link_category_form_fields', $tag );
		} else {
			/**
			 * Fires after the Edit Tag form fields are displayed.
			 *
			 * @since      2.9.0
			 * @deprecated 3.0.0 Use {$taxonomy}_edit_form_fields instead.
			 *
			 * @param object $tag Current tag term object.
			 */
			do_action( 'edit_tag_form_fields', $tag );
		}
		/**
		 * Fires after the Edit Term form fields are displayed.
		 *
		 * The dynamic portion of the hook name, `$taxonomy`, refers to
		 * the taxonomy slug.
		 *
		 * @since 3.0.0
		 *
		 * @param object $tag      Current taxonomy term object.
		 * @param string $taxonomy Current taxonomy slug.
		 */
		do_action( "{$taxonomy}_edit_form_fields", $tag, $taxonomy );

	}

	/**
	 * {@inheritdoc}
	 */
	public function save_fields( $item_id = null, $object_name = null ) {

		// Save new term
		$success = wp_update_term( $item_id, $object_name, $_POST );

		// Return if not successful
		if ( is_wp_error( $success ) ) {
			return $success;
		}

		// Save additional fields
		return parent::save_fields( $item_id, $object_name );

	}

	/**
	 * Register term fields once for all term screens
	 *
	 * @param WP_Fields_API $wp_fields
	 */
	public function register_term_fields( $wp_fields ) {

		static $registered;

		if ( $registered ) {
			return;
		}

		$registered = true;

		$wp_fields->add_field( $this->object_type, 'name' );
		$wp_fields->add_field( $this->object_type, 'slug' );
		$wp_fields->add_field( $this->object_type, 'parent' );
		$wp_fields->add_field( $this->object_type, 'description' );

	}

	/**
	 * Control hidden if global terms is enabled
	 *
	 * @param WP_Fields_API_Control $control
	 *
	 * @return bool
	 */
	public function capability_is_global_terms_disabled( $control ) {

		return ( ! global_terms_enabled() );

	}

	/**
	 * Control hidden if taxonomy is not hierarchical
	 *
	 * @param WP_Fields_API_Control $control
	 *
	 * @return bool
	 */
	public function capability_is_taxonomy_hierarchical( $control ) {

		return is_taxonomy_hierarchical( $this->object_name );

	}

}