<?php
/**
 * This is an implementation for Fields API for the Term forms in the WordPress Dashboard
 *
 * @package    WordPress
 * @subpackage Fields_API
 */

/**
 * Class WP_Fields_API_Form_Term
 *
 * @todo Switch to WP_Fields_API_Form when styles work for divs on all admin pages properly
 */
class WP_Fields_API_Form_Term extends WP_Fields_API_Form {

	/**
	 * {@inheritdoc}
	 */
	public function register_fields( $wp_fields ) {

		// Make sure primary term fields are registered for all term forms
		$this->register_term_fields( $wp_fields );

		////////////////
		// Core: Term //
		////////////////

		$section_id   = $this->id . '-main';
		$section_args = array(
			'label'         => __( 'Term' ),
			'display_label' => false,
			'controls'      => array(),
		);

		// Control: Name
		$section_args['controls'][ $this->id . '-name' ] = array(
			'type'        => 'text',
			'label'       => __( 'Name' ),
			'description' => __( 'The name is how it appears on your site.' ),
			'input_attrs'  => array(
				'name' => 'name',
			),
			'field'       => 'name',
			'internal'    => true,
			'wrap_attr'   => array(
				'class' => 'form-required',
			),
		);

		if ( 'term-add' == $this->id ) {
			// Term Add New form has a different input name
			$section_args['controls'][ $this->id . '-name' ]['input_attrs']['name'] = 'tag-name';
		}

		// Control: Slug
		$section_args['controls'][ $this->id . '-slug' ] = array(
			'type'                  => 'text',
			'label'                 => __( 'Slug' ),
			'description'           => __( 'The "slug" is the URL-friendly version of the name. It is usually all lowercase and contains only letters, numbers, and hyphens.' ),
			'capabilities_callback' => array( $this, 'capability_is_global_terms_disabled' ),
			'input_attrs'  => array(
				'name' => 'slug',
			),
			'field'                 => 'slug',
			'internal'              => true,
		);

		// Control: Parent
		$control_id_parent                              = $this->id . '-parent';
		$section_args['controls'][ $control_id_parent ] = array(
			'type'                         => 'select',
			'label'                        => __( 'Parent' ),
			'datasource'                   => array(
				'type'     => 'term',
				'get_args' => array(
					'taxonomy' => 'category',
				),
			),
			// @todo This description is only shown for 'category' == $object_subtype
			// @todo Generic description for taxonomies or new label for register_taxonomy?
			'description'                  => __( 'Categories, unlike tags, can have a hierarchy. You might have a Jazz category, and under that have children categories for Bebop and Big Band. Totally optional.' ),
			//'description'                  => __( 'This term supports hierarchy. You might have a Jazz term, and under that have children terms for Bebop and Big Band. Totally optional.' ),
			'capabilities_callback'        => array( $this, 'capability_is_taxonomy_hierarchical' ),
			'exclude_tree_current_item_id' => true,
			'placeholder_text'             => __( 'None' ),
			'input_attrs' => array(
				'name' => 'parent',
			),
			'field'                        => 'parent',
			'get_args'                     => array(
				'hide_empty' => false,
			),
			'internal'                     => true,
		);

		// Control: Slug
		$section_args['controls'][ $this->id . '-description' ] = array(
			'type'        => 'textarea',
			'label'       => __( 'Description' ),
			'description' => __( 'The description is not prominent by default; however, some themes may show it.' ),
			'input_attrs' => array(
				'name' => 'description',
				'rows' => '5',
				'cols' => '40',
			),
			'field'       => 'description',
			'internal'    => true,
		);

		// Add section
		$this->add_section( $section_id, $section_args );

		/////////////////
		// Back-compat //
		/////////////////

		add_action( "fields_after_render_section_controls_term_{$section_id}", array( $this, '_compat_section_table_hooks' ) );
		add_filter( "fields_control_datasource_get_args_term_{$control_id_parent}", array( $this, '_compat_control_parent_dropdown_hook' ), 10, 3 );

		// Add example fields (maybe)
		parent::register_fields( $wp_fields );

	}

	/**
	 * {@inheritdoc}
	 */
	public function save_fields( $item_id = null, $object_subtype = null ) {

		// Save term
		$success = wp_update_term( $item_id, $object_subtype, $_POST );

		// Return if not successful
		if ( is_wp_error( $success ) ) {
			return $success;
		}

		// Save additional fields
		return parent::save_fields( $item_id, $object_subtype );

	}

	/**
	 * Register term fields once for all term forms
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

		return is_taxonomy_hierarchical( $this->get_object_subtype() );

	}

	/**
	 * Compatibility hooks needed for within <table> markup of section
	 *
	 * @param WP_Fields_API_Section $section
	 */
	public function _compat_section_table_hooks( $section ) {

		if ( 'term-edit' == $this->id ) {
			$taxonomy = $this->object_subtype;
			$tag      = $this->get_item();

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

	}

	/**
	 * Filter the datasource args used for compatibility purposes
	 *
	 * @param array                    $args
	 * @param WP_Fields_API_Datasource $datasource
	 * @param WP_Fields_API_Control    $control
	 *
	 * @return array
	 */
	public function _compat_control_parent_dropdown_hook( $args, $datasource, $control ) {

		if ( ! in_array( $this->id, array( 'term-add', 'term-edit' ) ) ) {
			return $args;
		}

		// Determine context from form ID
		$context = 'new';

		if ( 'term-edit' == $this->id ) {
			$context = 'edit';
		}

		$dropdown_args = array(
			'hide_empty'       => 0,
			'hide_if_empty'    => false,
			'taxonomy'         => $datasource->get_args['taxonomy'],
			'name'             => 'parent',
			'orderby'          => 'name',
			'hierarchical'     => true,
			'show_option_none' => $control->placeholder_text,
		);

		/**
		 * Filter the taxonomy parent drop-down on the Add / Edit Term page.
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
		$dropdown_args = apply_filters( 'taxonomy_parent_dropdown_args', $dropdown_args, $taxonomy, $context );

		// name not used in args
		unset( $dropdown_args['name'] );

		// Handle show_option_none
		if ( $dropdown_args['show_option_none'] && $control->placeholder_text !== $dropdown_args['show_option_none'] ) {
			$control->placeholder_text = $dropdown_args['show_option_none'];
		}

		// show_option_none not used in args
		unset( $dropdown_args['show_option_none'] );

		// Merge in any other filtered args
		$args = array_merge( $args, $dropdown_args );

		return $args;

	}

}