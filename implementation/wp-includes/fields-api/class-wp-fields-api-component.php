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
	 * @var WP_Fields_API_Component
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
	 * @access public
	 * @var int
	 */
	public $priority = 10;

	/**
	 * Create new component
	 * 
	 * @access public
	 * @param string $id              ID for this component
	 * @param array  $args            Additional container args to set
	 */
	public function __construct( $id, $args = array() ) {
		global $wp_fields;

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

	public function setup() {
		// Nothing todo by default
	}


	/**
	 * Get object type for a container. We might have to check the parent container
	 *
	 * @access public
	 * @return string
	 */
	public function get_object_type() {
		if ( empty( $parent ) ) {
			return $this->object_type;
		} else {
			return $parent->get_object_type();
		}
	}

	/**
	 * Get internal instance number to use for a new form component
	 *
	 * @access public
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
}