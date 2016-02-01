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
	public $id;

	/**
	 * Object type.
	 *
	 * @access public
	 * @var string
	 */
	public $object_type;

	/**
	 * Object name (for post types and taxonomies).
	 *
	 * @access public
	 * @var string
	 */
	public $object_name;

	/**
	 * Item ID of current item
	 *
	 * @access public
	 * @var int|string
	 */
	public $item_id = 0;

	/**
	 * Priority of the container which informs load order of container, shown in order of lowest to highest.
	 *
	 * @access public
	 * @var integer
	 */
	public $priority = 200;

	/**
	 * Children objects
	 *
	 * @access public
	 * @var array
	 */
	protected $children = array(
		'section' => array(), // For forms
		'control' => array(), // For sections
		'field'   => array(), // For controls
	);

	/**
	 * Parent object
	 *
	 * @access public
	 * @var WP_Fields_API_Container|null
	 */
	protected $parent;

	/**
	 * Whether children have been sorted yet
	 *
	 * @access protected
	 * @var bool[]
	 */
	protected $sorted = array();

	/**
	 * Label to show in the UI.
	 *
	 * @access public
	 * @var string
	 */
	public $label;

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
	public $description;

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
	public $capabilities_callback;

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
	 * The primary form (if there is one).
	 *
	 * @access public
	 * @var string|WP_Fields_API_Field
	 */
	public $form;

	/**
	 * The primary section (if there is one).
	 *
	 * @access public
	 * @var string|WP_Fields_API_Field
	 */
	public $section;

	/**
	 * The primary control (if there is one).
	 *
	 * @access public
	 * @var string|WP_Fields_API_Control
	 */
	public $control;

	/**
	 * The primary field (if there is one).
	 *
	 * @access public
	 * @var string|WP_Fields_API_Field
	 */
	public $field;

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
	 * Get the section for this container.
	 *
	 * @return WP_Fields_API_Section[]
	 */
	public function get_sections() {

		return $this->get_children( 'section' );

	}

	/**
	 * Get the controls for this container.
	 *
	 * @return WP_Fields_API_Control[]
	 */
	public function get_controls() {

		return $this->get_children( 'control' );

	}

	/**
	 * Get child objects of container
	 *
	 * @param string|true $child_type
	 *
	 * @return WP_Fields_API_Container[]|array
	 */
	public function get_children( $child_type = 'control' ) {

		/**
		 * @var $wp_fields WP_Fields_API
		 */
		global $wp_fields;

		$children = array();

		// Get children from Fields API configuration
		if ( empty( $this->children[ $child_type ] ) ) {
			$object_children = array();

			if ( 'section' === $child_type ) {
				// Get sections for container
				$object_children = $wp_fields->get_sections( $this->object_type, $this->object_name, $this );
			} elseif ( 'control' === $child_type ) {
				// Get controls for container
				$object_children = $wp_fields->get_controls( $this->object_type, $this->object_name, $this );
			} elseif ( 'field' === $child_type && 'control' === $this->container_type && ! empty( $this->field ) ) {
				// Get field for container
				if ( is_a( $this->field, 'WP_Fields_API_Field' ) ) {
					$object_children = $this->field;
				} else {
					$object_children = $wp_fields->get_field( $this->object_type, $this->field, $this->object_name );
				}
			}

			if ( ! empty( $object_children ) ) {
				if ( ! is_array( $object_children ) ) {
					$object_children = array( $object_children );
				}

				$this->children[ $child_type ] = $object_children;

				if ( isset( $this->sorted[ $child_type ] ) ) {
					unset( $this->sorted[ $child_type ] );
				}
			}
		}

		if ( isset( $this->children[ $child_type ] ) ) {
			// Get children of a specific type
			$children = $this->children[ $child_type ];

			// Handle sorting
			if ( ! isset( $this->sorted[ $child_type ] ) ) {
				uasort( $children, array( 'WP_Fields_API', '_cmp_priority' ) );

				$this->children[ $child_type ] = $children;

				$this->sorted[ $child_type ] = true;
			}
		} elseif ( true === $child_type ) {
			// Get all children
			$children = $this->children;

			// Handle sorting
			foreach ( $children as $child_type => $child_type_children ) {
				if ( ! isset( $this->sorted[ $child_type ] ) ) {
					uasort( $child_type_children, array( 'WP_Fields_API', '_cmp_priority' ) );

					$this->children[ $child_type ] = $child_type_children;

					$this->sorted[ $child_type ] = true;
				}
			}
		}

		return $children;

	}

	/**
	 * Add child object to container
	 *
	 * @param WP_Fields_API_Container $child
	 * @param string                  $child_type
	 */
	public function add_child( $child, $child_type = 'control' ) {

		if ( ! isset( $this->children[ $child_type ] ) ) {
			$this->children[ $child_type ] = array();
		}

		if ( isset( $this->sorted[ $child_type ] ) ) {
			unset( $this->sorted[ $child_type ] );
		}

		// Set parent
		if ( 'field' !== $child->container_type ) {
			$child->set_parent( $this );
		}

		$this->children[ $child_type ][ $child->id ] =& $child;

	}

	/**
	 * Remove child object from container
	 *
	 * @param string $child_id
	 * @param string $child_type
	 */
	public function remove_child( $child_id, $child_type = 'control' ) {

		if ( isset( $this->children[ $child_type ][ $child_id ] ) ) {
			/**
			 * @var $child WP_Fields_API_Container
			 */
			$child = $this->children[ $child_type ][ $child_id ];

			$child->set_parent( null );

			unset( $this->children[ $child_type ][ $child_id ] );
		}

	}

	/**
	 * Remove all child objects from container
	 *
	 * @param string|true $child_type
	 */
	public function remove_children( $child_type = 'control' ) {

		if ( true !== $child_type ) {
			$this->children[ $child_type ] = array();

			if ( isset( $this->sorted[ $child_type ] ) ) {
				unset( $this->sorted[ $child_type ] );
			}
		} else {
			foreach ( $this->children as $child_type => $child_type_children ) {
				$this->children[ $child_type ] = array();

				if ( isset( $this->sorted[ $child_type ] ) ) {
					unset( $this->sorted[ $child_type ] );
				}
			}
		}

	}

	/**
	 * Get parent object of container
	 *
	 * @return WP_Fields_API_Container|null
	 */
	public function get_parent() {

		/**
		 * @var $wp_fields WP_Fields_API
		 */
		global $wp_fields;

		// Get children from Fields API configuration
		if ( empty( $this->parent ) && 'form' !== $this->container_type ) {
			$parent = null;

			if ( ! empty( $this->form ) ) {
				// Get form
				if ( is_a( $this->form, 'WP_Fields_API_Form' ) ) {
					$parent = $this->form;
				} else {
					$parent = $wp_fields->get_form( $this->object_type, $this->form, $this->object_name );
				}
			} elseif ( ! empty( $this->section ) ) {
				// Get section
				if ( is_a( $this->section, 'WP_Fields_API_Section' ) ) {
					$parent = $this->section;
				} else {
					$parent = $wp_fields->get_section( $this->object_type, $this->section, $this->object_name );
				}
			} elseif ( ! empty( $this->control ) ) {
				// Get control
				if ( is_a( $this->control, 'WP_Fields_API_Control' ) ) {
					$parent = $this->control;
				} else {
					$parent = $wp_fields->get_section( $this->object_type, $this->control, $this->object_name );
				}
			}

			if ( ! empty( $parent ) ) {
				$this->parent = $parent;
			}
		}

		return $this->parent;

	}

	/**
	 * Set parent object of container
	 *
	 * @param WP_Fields_API_Container|null $object
	 */
	public function set_parent( $object ) {

		$this->parent =& $object;

	}

	/**
	 * Get object type from container or parent
	 *
	 * @return string|null Object type
	 */
	public function get_object_type() {

		$parent = $this->parent;

		if ( ! $this->object_type && $parent ) {
			// Get object type from any parent that has it
			while ( $parent && $parent = $parent->get_parent() ) {
				if ( ! empty( $parent->object_type ) ) {
					$this->object_type = $parent->object_type;

					break;
				}
			}
		}

		return $this->object_type;

	}

	/**
	 * Get object name from container or parent
	 *
	 * @return string|null Object name
	 */
	public function get_object_name() {

		$parent = $this->parent;

		if ( ! $this->object_name && $parent ) {
			$object_type = $this->get_object_type();

			$default_object_name = '_' . $object_type;

			// Get object type from any parent that has it
			while ( $parent && $parent = $parent->get_parent() ) {
				if ( $parent->object_name && $default_object_name !== $parent->object_name ) {
					$this->object_name = $parent->object_name;

					break;
				}
			}
		}

		return $this->object_name;

	}

	/**
	 * Get parent object of container
	 *
	 * @return int|null
	 */
	public function get_item_id() {

		$parent = $this->parent;

		$item_id = 0;

		if ( ! empty( $this->item_id ) ) {
			// Get Item ID from container
			$item_id = $this->item_id;
		} elseif ( empty( $this->item_id ) && $parent ) {
			// Get Item ID from any parent that has it
			while ( $parent && $parent = $parent->get_parent() ) {
				if ( ! empty( $parent->item_id ) ) {
					$item_id = $parent->item_id;

					break;
				}
			}
		}

		return $item_id;

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