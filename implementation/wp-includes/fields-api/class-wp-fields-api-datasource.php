<?php
/**
 * Fields API Datasource Class
 *
 * @package WordPress
 * @subpackage Fields_API
 */
class WP_Fields_API_Datasource {

	/**
	 * Datasource type
	 *
	 * @access public
	 * @var string
	 */
	public $type = '';

	/**
	 * Arguments to send to the datasource or callback
	 *
	 * @access public
	 * @var array
	 */
	public $get_args = array();

	/**
	 * Display in hierarchical context (if datasource supports it)
	 *
	 * @access public
	 * @var bool
	 */
	public $hierarchical = false;

	/**
	 * Hierarchical fields for datasource
	 *
	 * @access public
	 * @var array
	 */
	public $hierarchical_fields = array(
		'id'            => 'id',
		'parent'        => 'parent',
		'title'         => 'title',
		'default_title' => '',
	);


	/**
	 * Choices callback
	 *
	 * @access public
	 *
	 * @see WP_Fields_API_Datasource::get_data()
	 *
	 * @var callable Callback is called with two arguments including the Datasource $args
	 *               and the instance of WP_Fields_API_Datasource. It returns an array of key=>value data to use.
	 */
	public $data_callback = null;

	/**
	 * Setup datasource properties
	 *
	 * @param string $type Datasource type.
	 * @param array  $args Datasource arguments.
	 */
	public function __construct( $type = null, $args = array() ) {

		// If source has a type, don't override it
		if ( $type && ! $this->type ) {
			$this->type = $type;
		}

		if ( $args ) {
			foreach ( $args as $property => $value ) {
				if ( isset( $this->{$property} ) && is_array( $this->{$property} ) ) {
					$this->{$property} = array_merge( $this->{$property}, $value );
				} else {
					$this->{$property} = $value;
				}
			}
		}

	}

	/**
	 * Setup and return data from the datasource
	 *
	 * @param array                 $args    Override datasource args values on-the-fly
	 * @param WP_Fields_API_Control $control Control object
	 *
	 * @return array|WP_Error An array of data, or a WP_Error if there was a problem
	 */
	public function get_data( $args = array(), $control = null ) {

		// Allow overriding of $this->get_args values on-the-fly
		$args = array_merge( $this->get_args, $args );

		// Handle callback
		if ( $this->data_callback && is_callable( $this->data_callback ) ) {
			$data = call_user_func( $this->data_callback, $args, $control, $this );
		} else {
			$data = $this->setup_data( $args, $control );
		}

		// @todo Needs hook doc
		$data = apply_filters( 'fields_api_datasource_data', $data, $this->type, $args, $control, $this );

		// @todo Needs hook doc
		$data = apply_filters( "fields_api_datasource_data_{$this->type}", $data, $this->type, $args, $control, $this );

		return $data;

	}

	/**
	 * Get data from the datasource
	 *
	 * @param array                 $args    Datasource args
	 * @param WP_Fields_API_Control $control Control object
	 *
	 * @return array|WP_Error An array of data, or a WP_Error if there was a problem
	 */
	protected function setup_data( $args, $control ) {

		$data = array();

		// Handle built-in types
		switch( $this->type ) {

			case 'post-format':
				$data = get_post_format_strings();

				break;

			case 'post-type':
				$data = get_post_types();

				break;

			case 'post-status':
				$data = get_post_statuses();

				break;

			case 'page-status':
				$data = get_page_statuses();

				break;

			case 'user-role':
				$editable_roles = array_reverse( get_editable_roles() );

				foreach ( $editable_roles as $role => $details ) {
					$name = translate_user_role( $details['name'] );

					$data[ $role ] = $name;
				}

				break;

		}

		return $data;

	}

	/**
	 * Allow a datasource to override rendering of a control
	 *
	 * @param WP_Fields_API_Control $control
	 *
	 * @return bool Whether rendering of control has been overridden
	 */
	public function render_control( $control ) {

		return false;

	}

	/**
	 * Recursively build data array the full depth
	 *
	 * @param array $data   List of data.
	 * @param array $items  List of items.
	 * @param int   $depth  Current depth.
	 * @param int   $parent Current parent item ID.
	 *
	 * @return array|WP_Error
	 */
	public function setup_data_recurse( $data, $items, $depth = 0, $parent = 0 ) {

		if ( is_wp_error( $items ) ) {
			return $items;
		}

		$pad = str_repeat( '&nbsp;', $depth * 3 );

		// Get hierarchical fields for datasource
		$data_id_field = $this->hierarchical_fields['id'];
		$data_parent_field = $this->hierarchical_fields['parent'];
		$data_title_field = $this->hierarchical_fields['title'];
		$data_default_title = $this->hierarchical_fields['default_title'];

		$data_type = '';

		if ( '' === $data_default_title ) {
			/* translators: %d: ID of an item */
			$data_default_title = __( '#%d (no title)' );
		}

		/**
		 * @var $item array|object
		 */
		foreach ( $items as $item ) {
			$item_title = '';
			$is_hierarchical = $this->hierarchical;

			// Disable hierarchical data if current post type / taxonomy is not hierarchical
			if ( $is_hierarchical ) {
				if ( is_a( $item, 'WP_Term' ) ) {
					if ( $data_type !== $item->taxonomy && ! is_taxonomy_hierarchical( $item->taxonomy ) ) {
						$is_hierarchical = false;
					}
				} elseif ( is_a( $item, 'WP_Post' ) ) {
					if ( $data_type !== $item->post_type && ! is_post_type_hierarchical( $item->post_type ) ) {
						$is_hierarchical = false;
					}
				}
			}

			if ( is_object( $item ) && isset( $item->{$data_id_field} ) && isset( $item->{$data_parent_field} ) ) {
				if ( isset( $item->{$data_title_field} ) ) {
					$item_title = $item->{$data_title_field};
				}

				$item_id = $item->{$data_id_field};
				$item_parent = $item->{$data_parent_field};
			} elseif ( is_array( $item ) && isset( $item[ $data_id_field ] ) && isset( $item[ $data_parent_field ] ) ) {
				if ( isset( $item[ $data_title_field ] ) ) {
					$item_title = $item[ $data_title_field ];
				}

				$item_id = $item[ $data_id_field ];
				$item_parent = $item[ $data_parent_field ];
			} else {
				continue;
			}

			if ( $is_hierarchical && $parent != $item_parent ) {
				continue;
			}

			if ( '' === $item_title ) {
				$item_title = sprintf( $data_default_title, $item_id );
			}

			$data[ $item_id ] = $pad . $item_title;

			if ( $is_hierarchical ) {
				$data = $this->setup_data_recurse( $data, $items, $depth + 1, $item_id );
			}
		}

		return $data;

	}

}