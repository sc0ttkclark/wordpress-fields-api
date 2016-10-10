<?php
/**
 * WordPress Fields API Container class
 *
 * @package WordPress
 * @subpackage Fields API
 */

/**
 * Fields API Container class.
 */
class WP_Fields_API_Container extends WP_Fields_API_Component {

	/**
	 * Type of container to use by default if no registered
	 * type is specified
	 *
	 * @access public
	 * @var string
	 */
	public $type = 'default';

	/**
	 * Hold children form components
	 *
	 * @access public
	 * @var array
	 */
	public $children = array();

	/**
	 * Type of container
	 *
	 * @var string
	 */
	public $container_type;

	/**
	 * Type of child container
	 * @var string
	 */
	public $child_container_type;

	/**
	 * Get a child component by id
	 *
	 * @access public
	 * @param  string $id ID for child component
	 * @return object|false
	 */
	public function get_child( $id ) {

		if ( empty( $this->children[ $id ] ) ) {
			return false;
		}

		return $this->children[ $id ];

	}

	/**
	 * Get all child components and optionally filter by subtype
	 *
	 * @access public
	 * @param string $object_subtype Object subtype (for post types and taxonomies).
	 * @return array
	 */
	public function get_children( $object_subtype = null ) {

		$children = $this->children;

		if ( ! empty( $object_subtype ) ) {
			foreach ( $children as $key => $child ) {
				if ( $object_subtype !== $child->object_subtype ) {
					unset( $children[ $key ] );
				}
			}
		}

		return $children;

	}

	/**
	 * Add a child component to the container
	 *
	 * @access public
	 *
	 * @param  string                        $id   ID for this component
	 * @param  array|WP_Fields_API_Container $args Additional container args to set
	 *
	 * @return WP_Fields_API_Container|WP_Error
	 */
	public function add_child( $id, $args = array() ) {

		/**
		 * @var $wp_fields WP_Fields_API
		 */
		global $wp_fields;

		if ( empty( $id ) ) {
			// @todo Need WP_Error code
			return new WP_Error( '', __( 'ID is required.', 'fields-api' ) );
		}

		if ( ! empty( $this->children[ $id ] ) ) {
			// @todo Need WP_Error code
			return new WP_Error( '', __( 'ID already exists.', 'fields-api' ) );
		}

		$class = $wp_fields->get_registered_type( $this->child_container_type, 'default' );

		if ( is_a( $args, $class ) ) {
			/**
			 * @var $child WP_Fields_API_Container
			 */
			$child = $args;
		} else {
			if ( is_object( $args ) ) {
				// @todo Need WP_Error code
				return new WP_Error( '', __( 'Unexpected object type.', 'fields-api' ) );
			}

			if ( ! empty( $args['type'] ) ) {
				$class = $wp_fields->get_registered_type( $this->child_container_type, $args['type'] );
			}

			if ( isset( $args['parent'] ) ) {
				unset( $args['parent'] );
			}

			$child = new $class( $id, $args );
		}

		$child->parent = $this;

		$this->children[ $id ] = $child;

		return $child;

	}

	/**
	 * Remove a child component by id
	 *
	 * @access public
	 *
	 * @param string $id ID for child component
	 */
	public function remove_child( $id ) {

		foreach ( $this->children as $key => $child ) {
			if ( $id === $child->id || ( is_object( $id ) && $id === $child ) ) {
				unset( $this->children[ $key ] );
			}
		}

	}

	/**
	 * Remove child components
	 *
	 * @access public
	 */
	public function remove_children() {

		$this->children = array();

	}

	/**
	 * Gather the parameters passed to client JavaScript via JSON.
	 *
	 * @return array The array to be exported to the client as JSON.
	 */
	public function json() {

		$json = wp_array_slice_assoc( (array) $this, array( 'container_type', 'id', 'type', 'priority' ) );
		$json['label'] = html_entity_decode( $this->label, ENT_QUOTES, get_bloginfo( 'charset' ) );
		$json['description'] = wp_kses_post( $this->description );
		$json['content'] = $this->get_content();
		$json['instanceNumber'] = $this->instance_number;

		$json['objectType'] = $this->get_object_type();
		$json['objectSubtype'] = $this->get_object_subtype();

		// Get parent
		$json['parent'] = '';
		$json['parentType'] = '';

		$parent = $this->get_parent();

		if ( $parent ) {
			$json['parent'] = $parent->id;
			$json['parentType'] = $parent->container_type;
		}

		// Get children
		$json['children'] = array();

		$children = $this->get_children( null );

		if ( $children ) {
			/**
			 * @var $child_type_children array
			 */
			foreach ( $children as $child_type => $child_type_children ) {
				$json['children'][ $child_type ] = wp_list_pluck( $child_type_children, 'id' );
			}
		}

		return $json;

	}
}