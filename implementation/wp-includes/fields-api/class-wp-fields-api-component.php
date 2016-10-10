<?php
/**
 * WordPress Fields API Component class
 *
 * @package WordPress
 * @subpackage Fields API
 */

/**
 * Fields API Component class.
 */
class WP_Fields_API_Component {
	/**
	 * Order in which this instance was created in relation to other instances.
	 *
	 * @access public
	 * @var int
	 */
	public $instance_number;

	/**
	 * Unique identifier.
	 *
	 * @access public
	 * @var string
	 */
	public $id;

	/**
	 * Type of container to use by default if no registered
	 * type is specified
	 *
	 * @access public
	 * @var string
	 */
	public $type = 'default';

	/**
	 * Object type.
	 *
	 * @access public
	 * @var string
	 */
	public $object_type = null;

	/**
	 * Object subtype (for post types and taxonomies).
	 *
	 * @access public
	 * @var string
	 */
	public $object_subtype = null;

	/**
	 * Parent container
	 *
	 * @access public
	 * @var WP_Fields_API_Container
	 */
	public $parent = null;

	/**
	 * Capability required for the component.
	 *
	 * @access public
	 * @var string|array
	 */
	public $capability;

	/**
	 * Capabilities Callback.
	 *
	 * @access public
	 *
	 * @see WP_Fields_API_Component::check_capabilities()
	 *
	 * @var callable Callback is called with one argument, the instance of
	 *               WP_Fields_API_Container, and returns bool to indicate whether
	 *               the container has capabilities to be used.
	 */
	public $capabilities_callback;

	/**
	 * Theme feature support for the container.
	 *
	 * @access public
	 * @var string|array
	 */
	public $theme_supports;

	/**
	 * @access public
	 * @var int
	 */
	public $priority = 10;

	/**
	 * True opens up the maybe render method
	 *
	 * @access public
	 * @var boolean
	 */
	public $can_render = true;

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
	public $render_callback;

	/**
	 * Create new component
	 *
	 * @access public
	 * @param string $id              ID for this component
	 * @param array  $args            Additional container args to set
	 */
	public function __construct( $id, $args = array() ) {
		$this->id = $id;

		foreach ( $args as $property => $value ) {
			if ( isset( $this->{$property} ) && is_array( $this->{$property} ) ) {
				$this->{$property} = array_merge( $this->{$property}, $value );
			} else {
				$this->{$property} = $value;
			}
		}

		$this->instance_number = $this->get_next_instance_number();

		/**
		 * Let people implement this method instead of the constructor so they don't have to remember constructor args
		 */
		$this->setup();
	}

	/**
	 * Any component specific setup here
	 */
	public function setup() {

		// Do nothing by default

	}

	/**
	 * Get object type for a container. We might have to check the parent container
	 *
	 * @access public
	 *
	 * @return string|null
	 */
	public function get_object_type() {

		$object_type = null;

		if ( empty( $this->parent ) ) {
			$object_type = $this->object_type;
		} else {
			$object_type = $this->parent->get_object_type();
		}

		return $object_type;

	}

	/**
	 * Get internal instance number to use for a new form component
	 *
	 * @access public
	 *
	 * @return int
	 */
	public function get_next_instance_number() {
		static $instance_count = 0;

		$instance_count++;

		return $instance_count;
	}

	/**
	 * Helper function to compare two objects by priority, ensuring sort stability via instance_number.
	 *
	 * @access public
	 *
	 * @param WP_Fields_API_Container $a Object A.
	 * @param WP_Fields_API_Container $b Object B.
	 *
	 * @return int
	 */
	public function _cmp_priority( $a, $b ) {

		$compare = 0;

		if ( isset( $a->priority ) || isset( $b->priority ) ) {
			$priorities = array(
				'high'    => 0,
				'core'    => 100,
				'default' => 200,
				'low'     => 300,
			);

			// Set defaults
			$a_priority = $priorities['default'];
			$b_priority = $priorities['default'];

			if ( isset( $a->priority ) ) {
				$a_priority = $a->priority;
			}

			if ( isset( $b->priority ) ) {
				$b_priority = $b->priority;
			}

			// Convert string priority
			if ( ! is_int( $a_priority ) ) {
				if ( isset( $priorities[ $a_priority ] ) ) {
					$a_priority = $priorities[ $a_priority ];
				} else {
					$a_priority = $priorities['default'];
				}
			}

			// Convert string priority
			if ( ! is_int( $b_priority ) ) {
				if ( isset( $priorities[ $b_priority ] ) ) {
					$b_priority = $priorities[ $b_priority ];
				} else {
					$b_priority = $priorities['default'];
				}
			}

			// Priority integers
			$compare = $a_priority - $b_priority;

			// Tie breakers can use instance number
			if ( $a_priority === $b_priority && isset( $a->instance_number ) && isset( $b->instance_number ) ) {
				$compare = $a->instance_number - $b->instance_number;
			}
		}

		return $compare;

	}

	/**
	 * Check capabilities and render the container.
	 */
	public function maybe_render() {
		if ( ! $this->check_capabilities() ) {
			return;
		}

		// Enqueue assets
		if ( method_exists( $this, 'enqueue' ) && ! has_action( 'admin_footer', array( $this, 'enqueue' ) ) ) {
			add_action( 'admin_footer', array( $this, 'enqueue' ) );
		}

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

		$access = apply_filters( "fields_api_check_capabilities", $access, $this );
		return $access;
	}
}