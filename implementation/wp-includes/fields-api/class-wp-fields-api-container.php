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
	 * Object subtype (for post types and taxonomies).
	 *
	 * @access public
	 * @var string
	 */
	public $object_subtype;

	/**
	 * Item ID of current item
	 *
	 * @access public
	 * @var int|string
	 */
	public $item_id = 0;

	/**
	 * Item data of current item
	 *
	 * @access public
	 * @var mixed
	 */
	public $item;

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
	 * @var WP_Fields_API_Container[]|string[]
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
			if ( isset( $this->{$property} ) && is_array( $this->{$property} ) ) {
				$this->{$property} = array_merge( $this->{$property}, $value );
			} else {
				$this->{$property} = $value;
			}
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
	 * Add section to container
	 *
	 * @param string                      $id
	 * @param array|WP_Fields_API_Section $args
	 *
	 * @return bool|WP_Error True on success or return error
	 */
	public function add_section( $id, $args = array() ) {

		/**
		 * @var $wp_fields WP_Fields_API
		 */
		global $wp_fields;

		// Set parent
		if ( is_a( $args, 'WP_Fields_API_Section' ) ) {
			$args->{$this->container_type} = $this->id;
		} else {
			$args[ $this->container_type ] = $this->id;

			// Set default section type
			if ( ! empty( $this->default_section_type ) && empty( $args['type'] ) ) {
				$args['type'] = $this->default_section_type;
			}
		}

		$object_type = $this->get_object_type();
		$object_subtype = $this->get_object_subtype();

		$added = $wp_fields->add_section( $object_type, $id, $object_subtype, $args );

		if ( $added && ! is_wp_error( $added ) ) {
			if ( $id && ! is_wp_error( $id ) ) {
				// Add child
				$this->add_child( $id, 'section' );

				return true;
			} else {
				return $id;
			}
		}

		return $added;

	}

	/**
	 * Add sections to container
	 *
	 * @param array|WP_Fields_API_Section[] $sections
	 */
	public function add_sections( $sections ) {

		foreach ( $sections as $section ) {
			if ( is_a( $section, 'WP_Fields_API_Section' ) ) {
				$id = $section->id;

				$section = array();
			} elseif ( is_array( $section ) ) {
				$id = $section['id'];

				unset( $section['id'] );
			} else {
				// Invalid section
				continue;
			}

			$this->add_section( $id, $section );
		}

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
	 * Add control to container
	 *
	 * @param string                      $id
	 * @param array|WP_Fields_API_Control $args
	 *
	 * @return bool|WP_Error True on success or return error
	 */
	public function add_control( $id, $args = array() ) {

		/**
		 * @var $wp_fields WP_Fields_API
		 */
		global $wp_fields;

		$object_type = $this->get_object_type();
		$object_subtype = $this->get_object_subtype();

		// Set parent
		if ( is_a( $args, 'WP_Fields_API_Control' ) ) {
			$args->{$this->container_type} = $this->id;
		} else {
			$args[ $this->container_type ] = $this->id;
		}

		$added = $wp_fields->add_control( $object_type, $id, $object_subtype, $args );

		if ( $added && ! is_wp_error( $added ) ) {
			if ( $id && ! is_wp_error( $id ) ) {
				// Add child
				$this->add_child( $id, 'control' );

				return true;
			} else {
				return $id;
			}
		}

		return $added;

	}

	/**
	 * Add controls to container
	 *
	 * @param array|WP_Fields_API_Control[] $controls
	 */
	public function add_controls( $controls ) {

		foreach ( $controls as $control ) {
			if ( is_a( $control, 'WP_Fields_API_Control' ) ) {
				$id = $control->id;

				$control = array();
			} elseif ( is_array( $control ) ) {
				$id = $control['id'];

				unset( $control['id'] );
			} else {
				// Invalid control
				continue;
			}

			$this->add_control( $id, $control );
		}

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

		$setup = true;

		// Get children from Fields API configuration
		if ( empty( $this->children[ $child_type ] ) ) {
			$object_type = $this->get_object_type();
			$object_subtype = $this->get_object_subtype();

			$object_children = array();

			if ( 'section' === $child_type ) {
				// Get sections for container
				$object_children = $wp_fields->get_sections( $object_type, $object_subtype, $this );
			} elseif ( 'control' === $child_type ) {
				// Get controls for container
				$object_children = $wp_fields->get_controls( $object_type, $object_subtype, $this );
			} elseif ( 'field' === $child_type && 'control' === $this->container_type && ! empty( $this->field ) ) {
				// Get field for container
				$object_children = $wp_fields->get_field( $object_type, $this->field, $object_subtype );
			}

			if ( ! empty( $object_children ) ) {
				if ( ! is_array( $object_children ) ) {
					$object_children = array( $object_children );
				}

				$this->children[ $child_type ] = $object_children;

				if ( 1 == count( $this->children ) ) {
					// No sorting necessary
					$this->sorted[ $child_type ] = true;
				} else {
					// Needs sorting
					$this->sorted[ $child_type ] = false;
				}

				$setup = false;
			}
		}

		if ( isset( $this->children[ $child_type ] ) ) {
			// Get children of a specific type
			$children = $this->children[ $child_type ];

			if ( $setup ) {
				// Setup of children is needed
				$children = $this->setup_children( $children, $child_type );
			}

			// Handle sorting
			if ( empty( $this->sorted[ $child_type ] ) ) {
				uasort( $children, array( 'WP_Fields_API', '_cmp_priority' ) );

				$this->children[ $child_type ] = $children;

				$this->sorted[ $child_type ] = true;
			}
		} elseif ( true === $child_type ) {
			// Get all children
			$children = $this->children;

			// Handle sorting
			foreach ( $children as $child_type => $child_type_children ) {
				if ( empty( $this->sorted[ $child_type ] ) ) {
					uasort( $child_type_children, array( 'WP_Fields_API', '_cmp_priority' ) );

					$this->children[ $child_type ] = $child_type_children;

					$this->sorted[ $child_type ] = true;
				}
			}
		}

		return $children;

	}

	/**
	 * Setup children objects
	 *
	 * @param WP_Fields_API_Container[]|string $children
	 * @param string|true                      $child_type
	 *
	 * @return WP_Fields_API_Container[]
	 */
	protected function setup_children( $children, $child_type ) {

		/**
		 * @var $wp_fields WP_Fields_API
		 */
		global $wp_fields;

		$object_type = $this->get_object_type();
		$object_subtype = $this->get_object_subtype();

		foreach ( $children as $k => $child ) {
			if ( is_string( $child ) ) {
				if ( 'section' === $child_type ) {
					// Get sections for container
					$child = $wp_fields->get_section( $object_type, $child, $object_subtype );
				} elseif ( 'control' === $child_type ) {
					// Get controls for container
					$child = $wp_fields->get_control( $object_type, $child, $object_subtype );
				} elseif ( 'field' === $child_type ) {
					$child = $wp_fields->get_field( $object_type, $child, $object_subtype );
				}
			}

			if ( $child ) {
				$children[ $k ] = $child;
			} else {
				unset( $children[ $k ] );
			}
		}

		return $children;

	}

	/**
	 * Add child object to container
	 *
	 * @param WP_Fields_API_Container|string $child
	 * @param string                         $child_type
	 */
	public function add_child( $child, $child_type = 'control' ) {

		if ( ! isset( $this->children[ $child_type ] ) ) {
			$this->children[ $child_type ] = array();
		}

		$this->sorted[ $child_type ] = false;

		if ( is_a( $child, 'WP_Fields_API_Container' ) ) {
			// Set parent
			if ( 'field' !== $child->container_type ) {
				$child->set_parent( $this );
			}

			$this->children[ $child_type ][ $child->id ] =& $child;
		} else {
			$this->children[ $child_type ][ $child ] = $child;
		}

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
			 * @var $child WP_Fields_API_Container|string
			 */
			$child = $this->children[ $child_type ][ $child_id ];

			if ( is_a( $child, 'WP_Fields_API_Container' ) ) {
				$child->set_parent( null );
			}

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

			$this->sorted[ $child_type ] = false;
		} else {
			foreach ( $this->children as $child_type => $child_type_children ) {
				$this->children[ $child_type ] = array();

				$this->sorted[ $child_type ] = false;
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
			$object_type = $this->get_object_type();
			$object_subtype = $this->get_object_subtype();

			$parent = null;

			if ( ! empty( $this->form ) ) {
				// Get form
				if ( is_a( $this->form, 'WP_Fields_API_Form' ) ) {
					$parent = $this->form;
				} else {
					$parent = $wp_fields->get_form( $object_type, $this->form, $object_subtype );
				}
			} elseif ( ! empty( $this->section ) ) {
				// Get section
				if ( is_a( $this->section, 'WP_Fields_API_Section' ) ) {
					$parent = $this->section;
				} else {
					$parent = $wp_fields->get_section( $object_type, $this->section, $object_subtype );
				}
			} elseif ( ! empty( $this->control ) ) {
				// Get control
				if ( is_a( $this->control, 'WP_Fields_API_Control' ) ) {
					$parent = $this->control;
				} else {
					$parent = $wp_fields->get_section( $object_type, $this->control, $object_subtype );
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
	 * Get Object subtype from container or parent
	 *
	 * @return string|null Object subtype
	 */
	public function get_object_subtype() {

		$parent = $this->parent;

		if ( ! $this->object_subtype && $parent ) {
			$object_type = $this->get_object_type();

			$default_object_subtype = '_' . $object_type;

			// Get object type from any parent that has it
			while ( $parent && $parent = $parent->get_parent() ) {
				if ( $parent->object_subtype && $default_object_subtype !== $parent->object_subtype ) {
					$this->object_subtype = $parent->object_subtype;

					break;
				}
			}
		}

		return $this->object_subtype;

	}

	/**
	 * Get item id of container
	 *
	 * @return int|null
	 */
	public function get_item_id() {

		$parent = $this->parent;

		$item_id = 0;

		if ( ! empty( $this->item_id ) ) {
			// Get Item ID from container
			$item_id = $this->item_id;
		} elseif ( $parent ) {
			// Get Item ID from a parent container that has it
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
	 * Get item of container
	 *
	 * @return mixed|null
	 */
	public function get_item() {

		$parent = $this->parent;

		$item = null;

		if ( ! empty( $this->item ) ) {
			// Get Item from container
			$item = $this->item;
		} elseif ( $parent ) {
			// Get Item from a parent container that has it
			while ( $parent && $parent = $parent->get_parent() ) {
				if ( ! empty( $parent->item ) ) {
					$item = $parent->item;

					break;
				}
			}
		}

		return $item;

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

		$object_type = $this->get_object_type();
		$object_subtype = $this->get_object_subtype();

		/**
		 * Filters to check required user capabilities and whether to render a Fields API container.
		 *
		 * @param bool                    $access Whether to give access to container.
		 * @param WP_Fields_API_Container $this   WP_Fields_API_Container instance.
		 */
		$access = apply_filters( "fields_api_check_capabilities_{$this->container_type}_{$object_type}", $access, $this );

		/**
		 * Filters to check required user capabilities and whether to render a Fields API container.
		 *
		 * The dynamic portion of the hook name, `$this->id`, refers to
		 * the ID of the specific Fields API container to be rendered.
		 *
		 * @param bool                    $access Whether to give access to container.
		 * @param WP_Fields_API_Container $this   WP_Fields_API_Container instance.
		 */
		$access = apply_filters( "fields_api_check_capabilities_{$this->container_type}_{$object_type}_{$object_subtype}_{$this->id}", $access, $this );

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

		if ( ! $this->check_capabilities() ) {
			return;
		}

		// Enqueue assets
		if ( method_exists( $this, 'enqueue' ) && ! has_action( 'admin_footer', array( $this, 'enqueue' ) ) ) {
			add_action( 'admin_footer', array( $this, 'enqueue' ) );
		}

		$object_type = $this->get_object_type();
		$object_subtype = $this->get_object_subtype();

		/**
		 * Fires before rendering a Fields API container.
		 *
		 * @param WP_Fields_API_Container $this WP_Fields_API_Container instance.
		 */
		do_action( "fields_render_{$this->container_type}_{$object_type}", $this );

		/**
		 * Fires before rendering a specific Fields API container.
		 *
		 * The dynamic portion of the hook name, `$this->id`, refers to
		 * the ID of the specific Fields API container to be rendered.
		 *
		 * @param WP_Fields_API_Container $this WP_Fields_API_Container instance.
		 */
		do_action( "fields_render_{$this->container_type}_{$object_type}_{$object_subtype}_{$this->id}", $this );

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