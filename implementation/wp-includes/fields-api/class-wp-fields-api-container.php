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
class WP_Fields_API_Container {
	/**
	 * Container type
	 *
	 * @access protected
	 * @var string
	 */
	protected $container_type = 'form';

	/**
	 * Incremented with each new class instantiation, then stored in $instance_number.
	 *
	 * Used when sorting two instances whose priorities are equal.
	 *
	 * @access protected
	 * @var int
	 */
	protected static $instance_count = 0;

	/**
	 * Order in which this instance was created in relation to other instances.
	 *
	 * @access public
	 * @var int
	 */
	public $instance_number = 0;

	/**
	 * Unique identifier.
	 *
	 * @access public
	 * @var string
	 */
	public $id = '';

	/**
	 * Object type.
	 *
	 * @access public
	 * @var string
	 */
	public $object_type = '';

	/**
	 * Object name (for post types and taxonomies).
	 *
	 * @access public
	 * @var string
	 */
	public $object_name = '';

	/**
	 * Priority of the container which informs load order of container.
	 *
	 * @access public
	 * @var integer
	 */
	public $priority = 160;

	/**
	 * Children objects
	 *
	 * @access public
	 * @var WP_Fields_API_Container[]
	 */
	protected $children = array();

	/**
	 * Parent object
	 *
	 * @access public
	 * @var WP_Fields_API_Container
	 */
	protected $parent;

	/**
	 * Label to show in the UI.
	 *
	 * @access public
	 * @var string
	 */
	public $label = '';

	/**
	 * Whether to display the label in the UI.
	 *
	 * @access public
	 * @var bool
	 */
	public $display_label = true;

	/**
	 * Description to show in the UI.
	 *
	 * @access public
	 * @var string
	 */
	public $description = '';

	/**
	 * Type of container.
	 *
	 * @access public
	 * @var string
	 */
	public $type = 'default';

	/**
	 * Whether a container is for internal (WP Core) use
	 *
	 * @access public
	 * @var bool
	 */
	public $internal = false;

	/**
	 * Capability required for the container.
	 *
	 * @access public
	 * @var string|array
	 */
	public $capability;

	/**
	 * Theme feature support for the container.
	 *
	 * @access public
	 * @var string|array
	 */
	public $theme_supports;

	/**
	 * Capabilities Callback.
	 *
	 * @access public
	 *
	 * @see WP_Fields_API_Container::check_capabilities()
	 *
	 * @var callable Callback is called with one argument, the instance of
	 *               WP_Fields_API_Container, and returns bool to indicate whether
	 *               the container has capabilities to be used.
	 */
	public $capabilities_callback = '';

	/**
	 * Render Callback.
	 *
	 * @access public
	 *
	 * @see WP_Fields_API_Container::render()
	 *
	 * @var callable Callback is called with one argument, the instance of
	 *               WP_Fields_API_Container.
	 */
	public $render_callback = '';

	/**
	 * Constructor.
	 *
	 * Parameters are not set to maintain PHP overloading compatibility (strict standards)
	 */
	public function __construct() {

		$args = func_get_args();

		call_user_func_array( array( $this, 'init' ), $args );

	}

	/**
	 * Secondary constructor; Any supplied $args override class property defaults.
	 *
	 * @param string $object_type   Object type.
	 * @param string $id            A specific ID of the container.
	 * @param array  $args          Container arguments.
	 */
	public function init( $object_type, $id, $args = array() ) {

		$this->object_type = $object_type;

		if ( is_array( $id ) ) {
			$args = $id;
		} else {
			$this->id = $id;
		}

		foreach ( $args as $property => $value ) {
			$this->{$property} = $value;
		}

		self::$instance_count += 1;
		$this->instance_number = self::$instance_count;

	}

	/**
	 * Get child objects of container
	 *
	 * @return WP_Fields_API_Container[]
	 */
	public function get_children() {

		return $this->children;

	}

	/**
	 * Add child object to container
	 *
	 * @param WP_Fields_API_Container $child
	 */
	public function add_child( $child ) {

		$this->children[ $child->id ] = $child;

	}

	/**
	 * Remove child object from container
	 *
	 * @param string $child_id
	 */
	public function remove_child( $child_id ) {

		if ( isset( $this->children[ $child_id ] ) ) {
			unset( $this->children[ $child_id ] );
		}

	}

	/**
	 * Get parent object of container
	 *
	 * @return WP_Fields_API_Container
	 */
	public function get_parent() {

		return $this->parent;

	}

	/**
	 * Set parent object of container
	 *
	 * @param WP_Fields_API_Container $object
	 */
	public function set_parent( $object ) {

		$this->parent = $object;

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

		// Get parent
		$json['parent'] = '';

		if ( $this->parent ) {
			$json['parent'] = $this->parent->id;
		}

		// Get children
		$json['children'] = array();

		if ( $this->children ) {
			$json['children'] = wp_list_pluck( $this->children, 'id' );
		}

		return $json;

	}

	/**
	 * Checks required user capabilities and whether the theme has the
	 * feature support required by the container.
	 *
	 * @return bool False if theme doesn't support the container or user can't change container, otherwise true.
	 */
	public function check_capabilities() {

		if ( $this->capability && ! call_user_func_array( 'current_user_can', (array) $this->capability ) ) {
			return false;
		}

		if ( $this->theme_supports && ! call_user_func_array( 'current_theme_supports', (array) $this->theme_supports ) ) {
			return false;
		}

		$access = true;

		if ( is_callable( $this->capabilities_callback ) ) {
			$access = call_user_func( $this->capabilities_callback, $this );
		}

		/**
		 * Filters to check required user capabilities and whether to render a Fields API container.
		 *
		 * @param bool                    $access Whether to give access to container.
		 * @param WP_Fields_API_Container $this   WP_Fields_API_Container instance.
		 */
		$access = apply_filters( "fields_api_check_capabilities_{$this->container_type}_{$this->object_type}", $access, $this );

		/**
		 * Filters to check required user capabilities and whether to render a Fields API container.
		 *
		 * The dynamic portion of the hook name, `$this->id`, refers to
		 * the ID of the specific Fields API container to be rendered.
		 *
		 * @param bool                    $access Whether to give access to container.
		 * @param WP_Fields_API_Container $this   WP_Fields_API_Container instance.
		 */
		$access = apply_filters( "fields_api_check_capabilities_{$this->container_type}_{$this->object_type}_{$this->object_name}_{$this->id}", $access, $this );

		return $access;

	}

	/**
	 * Get the container label.
	 *
	 * @return string Label of the container.
	 */
	public function render_label() {

		if ( $this->label && $this->display_label ) {
			echo esc_html( $this->label );
		}

	}

	/**
	 * Get the container description.
	 *
	 * @return string Description of the container.
	 */
	public function render_description() {

		if ( $this->description ) {
			echo wp_kses_post( $this->description );
		}

	}

	/**
	 * Render HTML attributes safely to the screen.
	 *
	 * @access public
	 *
	 * @param array $attrs
	 */
	public function render_attrs( $attrs = array() ) {

		foreach ( $attrs as $attr => $value ) {
			echo esc_attr( $attr ) . '="' . esc_attr( $value ) . '" ';
		}

	}

	/**
	 * Enqueue scripts/styles as needed.
	 */
	public function enqueue() {

		// Default is to do nothing

	}

	/**
	 * Get the container contents.
	 *
	 * @return string Contents of the container.
	 */
	final public function get_content() {

		ob_start();

		$this->maybe_render();

		$template = trim( ob_get_contents() );

		ob_end_clean();

		return $template;

	}

	/**
	 * Check capabilities and render the container.
	 */
	final public function maybe_render() {

		$args = func_get_args();

		if ( ! empty( $args[0] ) && isset( $this->item_id ) ) {
			$this->item_id = $args[0];
		}

		if ( ! empty( $args[1] ) ) {
			$this->object_name = $args[1];
		}

		if ( ! $this->check_capabilities() ) {
			return;
		}

		/**
		 * Fires before rendering a Fields API container.
		 *
		 * @param WP_Fields_API_Container $this WP_Fields_API_Container instance.
		 */
		do_action( "fields_api_render_{$this->container_type}_{$this->object_type}", $this );

		/**
		 * Fires before rendering a specific Fields API container.
		 *
		 * The dynamic portion of the hook name, `$this->id`, refers to
		 * the ID of the specific Fields API container to be rendered.
		 *
		 * @param WP_Fields_API_Container $this WP_Fields_API_Container instance.
		 */
		do_action( "fields_api_render_{$this->container_type}_{$this->object_type}_{$this->object_name}_{$this->id}", $this );

		if ( is_callable( $this->render_callback ) ) {
			call_user_func( $this->render_callback, $this );
		} else {
			$this->render();
		}

	}

	/**
	 * Render the container.
	 */
	protected function render() {

		// Default is to do nothing

	}

}