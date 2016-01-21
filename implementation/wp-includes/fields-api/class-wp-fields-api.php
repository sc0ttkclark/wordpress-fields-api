<?php

/**
 * This is a manager for the Fields API, based on the WP_Customize_Manager.
 *
 * @package    WordPress
 * @subpackage Fields_API
 */
final class WP_Fields_API {

	/**
	 * @var WP_Fields_API
	 */
	private static $instance;

	/**
	 * Registered Containers
	 *
	 * @access protected
	 * @var array
	 */
	protected static $containers = array();

	/**
	 * Registered Screens
	 *
	 * @access protected
	 * @var array
	 */
	protected static $screens = array();

	/**
	 * Registered Sections
	 *
	 * @access protected
	 * @var array
	 */
	protected static $sections = array();

	/**
	 * Registered Fields
	 *
	 * @access protected
	 * @var array
	 */
	protected static $fields = array();

	/**
	 * Registered Controls
	 *
	 * @access protected
	 * @var array
	 */
	protected static $controls = array();

	/**
	 * Screen types that may be rendered.
	 *
	 * @access protected
	 * @var array
	 */
	protected static $registered_screen_types = array();

	/**
	 * Section types that may be rendered.
	 *
	 * @access protected
	 * @var array
	 */
	protected static $registered_section_types = array();

	/**
	 * Field types that may be rendered.
	 *
	 * @access protected
	 * @var array
	 */
	protected static $registered_field_types = array();

	/**
	 * Control types that may be rendered.
	 *
	 * @access protected
	 * @var array
	 */
	protected static $registered_control_types = array();

	/**
	 * IDs for Screens, Sections, and Controls which are valid and have been prepared.
	 *
	 * @access protected
	 * @var array
	 */
	protected static $prepared_ids = array();

	/**
	 * Include the library and bootstrap.
	 *
	 * @constructor
	 * @access public
	 */
	private function __construct() {

		require_once( WP_FIELDS_API_DIR . 'implementation/wp-includes/fields-api/class-wp-fields-api-field.php' );
		require_once( WP_FIELDS_API_DIR . 'implementation/wp-includes/fields-api/class-wp-fields-api-control.php' );
		require_once( WP_FIELDS_API_DIR . 'implementation/wp-includes/fields-api/class-wp-fields-api-section.php' );
		require_once( WP_FIELDS_API_DIR . 'implementation/wp-includes/fields-api/class-wp-fields-api-screen.php' );
		require_once( WP_FIELDS_API_DIR . 'implementation/wp-includes/fields-api/fields-api-controls.php' );

		// Register our wp_loaded() first before WP_Customize_Manage::wp_loaded()
		add_action( 'wp_loaded', array( $this, 'wp_loaded' ), 9 );

		add_action( 'fields_register', array( $this, 'register_controls' ) );

	}

	/**
	 * Setup instance for singleton
	 *
	 * @return WP_Fields_API
	 */
	public static function get_instance() {

		if ( empty( self::$instance ) ) {
			self::$instance = new self;
		}

		return self::$instance;

	}

	/**
	 * Trigger the `fields_register` action hook on `wp_loaded`.
	 *
	 * Fields, Sections, Screens, and Controls should be registered on this hook.
	 *
	 * @access public
	 */
	public function wp_loaded() {

		/**
		 * Fires when the Fields API is avaiable, and components can be registered.
		 *
		 * @param WP_Fields_API $this The Fields manager object.
		 */
		do_action( 'fields_register', $this );

	}

	/**
	 * Get the registered containers.
	 *
	 * @access public
	 *
	 * @param string|null         $object_type   Object type. Null for all containers for all object types.
	 * @param string|boolean|null $object_name   Object name (for post types and taxonomies).
	 *                                           True for all containers for all object names.
	 *
	 * @return WP_Fields_API_Screen[]|WP_Fields_API_Section[]
	 */
	public function get_containers( $object_type = null, $object_name = null ) {

		// $object_name defaults to '_{$object_type}' for internal handling.
		if ( empty( $object_name ) && ! empty( $object_type ) ) {
			$object_name = '_' . $object_type;
		}

		// Setup containers.
		if ( empty( self::$containers ) ) {
			if ( true === $object_name ) {
				$this->prepare_controls( $object_type );
			} else {
				$this->prepare_object_controls( $object_type, $object_name );
			}
		}

		$containers = array();

		if ( null === $object_type ) {
			// Get all containers.
			$containers = self::$containers;
		} elseif ( isset( self::$containers[ $object_type ][ $object_name ] ) ) {
			// Get all containers by object name.
			$containers = self::$containers[ $object_type ][ $object_name ];
		} elseif ( true === $object_name ) {
			// Get all containers by object type.
			foreach ( self::$containers[ $object_type ] as $object_name => $object_containers ) {
				$containers = array_merge( $containers, array_values( $object_containers ) );
			}
		}

		return $containers;

	}

	/**
	 * Get the registered screens.
	 *
	 * @access public
	 *
	 * @param string $object_type Object type.
	 * @param string $object_name Object name (for post types and taxonomies).
	 *
	 * @return WP_Fields_API_Screen[]
	 */
	public function get_screens( $object_type = null, $object_name = null ) {

		// $object_name defaults to '_{$object_type}' for internal handling.
		if ( empty( $object_name ) && ! empty( $object_type ) ) {
			$object_name = '_' . $object_type;
		}

		$screens = array();

		if ( null === $object_type ) {
			// Late init.
			foreach ( self::$screens as $object_type => $object_names ) {
				foreach ( $object_names as $object_name => $screens ) {
					$this->get_screens( $object_type, $object_name );
				}
			}

			$screens = self::$screens;
		} elseif ( isset( self::$screens[ $object_type ][ $object_name ] ) ) {
			// Late init.
			foreach ( self::$screens[ $object_type ][ $object_name ] as $id => $screen ) {
				// Late init
				self::$screens[ $object_type ][ $object_name ][ $id ] = $this->setup_screen( $object_type, $id, $object_name, $screen );
			}

			$screens = self::$screens[ $object_type ][ $object_name ];
		} elseif ( true === $object_name ) {
			// Get all screens.
			// Late init.
			foreach ( self::$screens[ $object_type ] as $object_name => $object_screens ) {
				$screens = array_merge( $screens, array_values( $this->get_screens( $object_type, $object_name ) ) );
			}
		}

		return $screens;

	}

	/**
	 * Add a field screen.
	 *
	 * @access public
	 *
	 * @param string                      $object_type Object type.
	 * @param WP_Fields_API_Screen|string $id          Field Screen object, or Screen ID.
	 * @param string                      $object_name Object name (for post types and taxonomies).
	 * @param array                       $args        Optional. Screen arguments. Default empty array.
	 */
	public function add_screen( $object_type, $id, $object_name = null, $args = array() ) {

		if ( empty( $id ) && empty( $args ) ) {
			return;
		}

		if ( is_a( $id, 'WP_Fields_API_Screen' ) ) {
			$screen = $id;

			$id = $screen->id;
		} else {
			// Save for late init.
			$screen = $args;
		}

		// $object_name defaults to '_{$object_type}' for internal handling.
		if ( empty( $object_name ) && ! empty( $object_type ) ) {
			$object_name = '_' . $object_type;
		}

		if ( ! isset( self::$screens[ $object_type ] ) ) {
			self::$screens[ $object_type ] = array();
		}

		if ( ! isset( self::$screens[ $object_type ][ $object_name ] ) ) {
			self::$screens[ $object_type ][ $object_name ] = array();
		}

		self::$screens[ $object_type ][ $object_name ][ $id ] = $screen;

	}

	/**
	 * Retrieve a field screen.
	 *
	 * @access public
	 *
	 * @param string $object_type Object type.
	 * @param string $id          Screen ID to get.
	 * @param string $object_name Object name (for post types and taxonomies).
	 *
	 * @return WP_Fields_API_Screen|null Requested screen instance.
	 */
	public function get_screen( $object_type, $id, $object_name = null ) {

		if ( empty( $object_name ) && ! empty( $object_type ) ) {
			$object_name = '_' . $object_type; // Default to _object_type for internal handling
		}

		$screen = null;

		if ( isset( self::$screens[ $object_type ][ $object_name ][ $id ] ) ) {
			// Late init
			self::$screens[ $object_type ][ $object_name ][ $id ] = $this->setup_screen( $object_type, $id, $object_name, self::$screens[ $object_type ][ $object_name ][ $id ] );

			$screen = self::$screens[ $object_type ][ $object_name ][ $id ];
		}

		return $screen;

	}

	/**
	 * Setup the screen.
	 *
	 * @access public
	 *
	 * @param string $object_type Object type.
	 * @param string $id          ID of the screen.
	 * @param string $object_name Object name (for post types and taxonomies).
	 * @param array  $args        Screen arguments.
	 *
	 * @return WP_Fields_API_Screen|null $screen The screen object.
	 */
	public function setup_screen( $object_type, $id, $object_name = null, $args = null ) {

		$screen = null;

		$screen_class = 'WP_Fields_API_Screen';

		if ( is_a( $args, $screen_class ) ) {
			$screen = $args;
		} elseif ( is_array( $args ) ) {
			$args['object_name'] = $object_name;

			if ( ! empty( $args['type'] ) ) {
				if ( ! empty( self::$registered_screen_types[ $args['type'] ] ) ) {
					$screen_class = self::$registered_screen_types[ $args['type'] ];
				} elseif ( in_array( $args['type'], self::$registered_screen_types ) ) {
					$screen_class = $args['type'];
				}
			}

			$screen = new $screen_class( $object_type, $id, $args );
		}

		return $screen;

	}

	/**
	 * Remove a screen.
	 *
	 * @access public
	 *
	 * @param string $object_type Object type, set true to remove all screens.
	 * @param string $id          Screen ID to remove, set true to remove all screens from an object.
	 * @param string $object_name Object name (for post types and taxonomies), set true to remove to all objects from an object type.
	 */
	public function remove_screen( $object_type, $id, $object_name = null ) {

		if ( true === $object_type ) {
			// Remove all screens
			self::$screens = array();
		} elseif ( true === $object_name ) {
			// Remove all screens for an object type
			if ( isset( self::$screens[ $object_type ] ) ) {
				unset( self::$screens[ $object_type ] );
			}
		} else {
			if ( empty( $object_name ) && ! empty( $object_type ) ) {
				$object_name = '_' . $object_type; // Default to _object_type for internal handling
			}

			if ( true === $id && null !== $object_name ) {
				// Remove all screens for an object type
				if ( isset( self::$screens[ $object_type ][ $object_name ] ) ) {
					unset( self::$screens[ $object_type ][ $object_name ] );
				}
			} elseif ( isset( self::$screens[ $object_type ][ $object_name ][ $id ] ) ) {
				// Remove screen from object type and name
				unset( self::$screens[ $object_type ][ $object_name ][ $id ] );
			}
		}

	}

	/**
	 * Register a screen type.
	 *
	 * @access public
	 *
	 * @see    WP_Fields_API_Screen
	 *
	 * @param string $type         Screen type ID.
	 * @param string $screen_class Name of a custom screen which is a subclass of WP_Fields_API_Screen.
	 */
	public function register_screen_type( $type, $screen_class = null ) {

		if ( null === $screen_class ) {
			$screen_class = $type;
		}

		self::$registered_screen_types[ $type ] = $screen_class;

	}

	/**
	 * Render JS templates for all registered screen types.
	 *
	 * @access public
	 */
	public function render_screen_templates() {

		/**
		 * @var WP_Fields_API_Screen $screen
		 */
		foreach ( self::$registered_screen_types as $screen_type => $screen_class ) {
			$screen = $this->setup_screen( null, 'temp', null, array( 'type' => $screen_type ) );

			$screen->print_template();
		}

	}

	/**
	 * Get the registered sections.
	 *
	 * @access public
	 *
	 * @param string $object_type Object type.
	 * @param string $object_name Object name (for post types and taxonomies).
	 * @param string $screen      Screen ID.
	 *
	 * @return WP_Fields_API_Section[]
	 */
	public function get_sections( $object_type = null, $object_name = null, $screen = null ) {

		if ( empty( $object_name ) && ! empty( $object_type ) ) {
			$object_name = '_' . $object_type; // Default to _object_type for internal handling
		}

		$sections = array();

		if ( null === $object_type ) {
			// Late init
			foreach ( self::$sections as $object_type => $object_names ) {
				foreach ( $object_names as $object_name => $sections ) {
					$this->get_sections( $object_type, $object_name );
				}
			}

			$sections = self::$sections;

			// Get only sections for a specific screen
			if ( null !== $screen ) {
				$screen_sections = array();

				foreach ( $sections as $object_type => $object_names ) {
					foreach ( $object_names as $object_name => $object_sections ) {
						foreach ( $object_sections as $id => $section ) {
							if ( $screen == $section->screen->id ) {
								if ( ! isset( $screen_sections[ $object_type ] ) ) {
									$screen_sections[ $object_type ] = array();
								}

								if ( ! isset( $screen_sections[ $object_type ][ $object_name ] ) ) {
									$screen_sections[ $object_type ][ $object_name ] = array();
								}

								$screen_sections[ $object_type ][ $object_name ][ $id ] = $screen;
							}
						}
					}
				}

				$sections = $screen_sections;
			}
		} elseif ( isset( self::$sections[ $object_type ][ $object_name ] ) ) {
			// Late init
			foreach ( self::$sections[ $object_type ][ $object_name ] as $id => $section ) {
				// Late init
				self::$sections[ $object_type ][ $object_name ][ $id ] = $this->setup_section( $object_type, $id, $object_name, $section );
			}

			$sections = self::$sections[ $object_type ][ $object_name ];

			// Get only sections for a specific screen
			if ( null !== $screen ) {
				$screen_sections = array();

				foreach ( $sections as $id => $section ) {
					if ( $screen == $section->screen ) {
						$screen_sections[ $id ] = $section;
					}
				}

				$sections = $screen_sections;
			}
		} elseif ( true === $object_name ) {
			// Get all sections

			// Late init
			foreach ( self::$sections[ $object_type ] as $object_name => $object_sections ) {
				$sections = array_merge( $sections, array_values( $this->get_sections( $object_type, $object_name, $screen ) ) );
			}
		}

		return $sections;

	}

	/**
	 * Add a field section.
	 *
	 * @access public
	 *
	 * @param string                       $object_type Object type.
	 * @param WP_Fields_API_Section|string $id          Field Section object, or Section ID.
	 * @param string                       $object_name Object name (for post types and taxonomies).
	 * @param array                        $args        Section arguments.
	 */
	public function add_section( $object_type, $id, $object_name = null, $args = array() ) {

		if ( empty( $id ) && empty( $args ) ) {
			return;
		}

		if ( is_a( $id, 'WP_Fields_API_Section' ) ) {
			$section = $id;

			$id = $section->id;
		} else {
			// Save for late init
			$section = $args;
		}

		if ( empty( $object_name ) && ! empty( $object_type ) ) {
			$object_name = '_' . $object_type; // Default to _object_type for internal handling
		}

		if ( ! isset( self::$sections[ $object_type ] ) ) {
			self::$sections[ $object_type ] = array();
		}

		if ( ! isset( self::$sections[ $object_type ][ $object_name ] ) ) {
			self::$sections[ $object_type ][ $object_name ] = array();
		}

		self::$sections[ $object_type ][ $object_name ][ $id ] = $section;

	}

	/**
	 * Retrieve a field section.
	 *
	 * @access public
	 *
	 * @param string $object_type Object type.
	 * @param string $id          Section ID to get.
	 * @param string $object_name Object name (for post types and taxonomies).
	 *
	 * @return WP_Fields_API_Section|null Requested section instance.
	 */
	public function get_section( $object_type, $id, $object_name = null ) {

		if ( empty( $object_name ) && ! empty( $object_type ) ) {
			$object_name = '_' . $object_type; // Default to _object_type for internal handling
		}

		$section = null;

		if ( isset( self::$sections[ $object_type ][ $object_name ][ $id ] ) ) {
			// Late init
			self::$sections[ $object_type ][ $object_name ][ $id ] = $this->setup_section( $object_type, $id, $object_name, self::$sections[ $object_type ][ $object_name ][ $id ] );

			$section = self::$sections[ $object_type ][ $object_name ][ $id ];
		}

		return $section;

	}

	/**
	 * Setup the section.
	 *
	 * @access public
	 *
	 * @param string $object_type Object type.
	 * @param string $id          ID of the section.
	 * @param string $object_name Object name (for post types and taxonomies).
	 * @param array  $args        Section arguments.
	 *
	 * @return WP_Fields_API_Section|null $section The section object.
	 */
	public function setup_section( $object_type, $id, $object_name = null, $args = null ) {

		$section = null;

		$section_class = 'WP_Fields_API_Section';

		if ( is_a( $args, $section_class ) ) {
			$section = $args;
		} elseif ( is_array( $args ) ) {
			$args['object_name'] = $object_name;

			if ( ! empty( $args['type'] ) ) {
				if ( ! empty( self::$registered_section_types[ $args['type'] ] ) ) {
					$section_class = self::$registered_section_types[ $args['type'] ];
				} elseif ( in_array( $args['type'], self::$registered_section_types ) ) {
					$section_class = $args['type'];
				}
			}

			$section = new $section_class( $object_type, $id, $args );
		}

		return $section;

	}

	/**
	 * Remove a section.
	 *
	 * @access public
	 *
	 * @param string $object_type Object type, set true to remove all sections.
	 * @param string $id          Section ID to remove, set true to remove all sections from an object.
	 * @param string $object_name Object name (for post types and taxonomies), set true to remove to all objects from an object type.
	 */
	public function remove_section( $object_type, $id, $object_name = null ) {

		if ( true === $object_type ) {
			// Remove all sections
			self::$sections = array();
		} elseif ( true === $object_name ) {
			// Remove all sections for an object type
			if ( isset( self::$sections[ $object_type ] ) ) {
				unset( self::$sections[ $object_type ] );
			}
		} else {
			if ( empty( $object_name ) && ! empty( $object_type ) ) {
				$object_name = '_' . $object_type; // Default to _object_type for internal handling
			}

			if ( true === $id && null !== $object_name ) {
				// Remove all sections for an object type
				if ( isset( self::$sections[ $object_type ][ $object_name ] ) ) {
					unset( self::$sections[ $object_type ][ $object_name ] );
				}
			} elseif ( isset( self::$sections[ $object_type ][ $object_name ][ $id ] ) ) {
				// Remove section from object type and name
				unset( self::$sections[ $object_type ][ $object_name ][ $id ] );
			}
		}

	}

	/**
	 * Register a section type.
	 *
	 * @access public
	 *
	 * @see    WP_Fields_API_Section
	 *
	 * @param string $type          Section type ID.
	 * @param string $section_class Name of a custom section which is a subclass of WP_Fields_API_Section.
	 */
	public function register_section_type( $type, $section_class = null ) {

		if ( null === $section_class ) {
			$section_class = $type;
		}

		self::$registered_section_types[ $type ] = $section_class;

	}

	/**
	 * Render JS templates for all registered section types.
	 *
	 * @access public
	 */
	public function render_section_templates() {

		/**
		 * @var $section WP_Fields_API_Section
		 */
		foreach ( self::$registered_control_types as $section_type => $section_class ) {
			$section = $this->setup_section( null, 'temp', null, array( 'type' => $section_type ) );

			$section->print_template();
		}

	}

	/**
	 * Get the registered fields.
	 *
	 * @access public
	 *
	 * @param string $object_type Object type.
	 * @param string $object_name Object name (for post types and taxonomies).
	 *
	 * @return WP_Fields_API_Field[]
	 */
	public function get_fields( $object_type = null, $object_name = null ) {

		if ( empty( $object_name ) && ! empty( $object_type ) ) {
			$object_name = '_' . $object_type; // Default to _object_type for internal handling
		}

		$fields = array();

		if ( null === $object_type ) {
			// Late init
			foreach ( self::$fields as $object_type => $object_names ) {
				foreach ( $object_names as $object_name => $fields ) {
					$this->get_fields( $object_type, $object_name );
				}
			}

			$fields = self::$fields;
		} elseif ( isset( self::$fields[ $object_type ][ $object_name ] ) ) {
			// Late init
			foreach ( self::$fields[ $object_type ][ $object_name ] as $id => $field ) {
				// Late init
				self::$fields[ $object_type ][ $object_name ][ $id ] = $this->setup_field( $object_type, $id, $object_name, $field );
			}

			$fields = self::$fields[ $object_type ][ $object_name ];
		} elseif ( true === $object_name ) {
			// Get all fields

			// Late init
			foreach ( self::$fields[ $object_type ] as $object_name => $object_fields ) {
				$fields = array_merge( $fields, array_values( $this->get_fields( $object_type, $object_name ) ) );
			}
		}

		return $fields;

	}

	/**
	 * Add a field.
	 *
	 * @access public
	 *
	 * @param string                     $object_type Object type.
	 * @param WP_Fields_API_Field|string $id          Fields API Field object, or ID.
	 * @param string                     $object_name Object name (for post types and taxonomies).
	 * @param array                      $args        Field arguments; passed to WP_Fields_API_Field
	 *                                                constructor.
	 */
	public function add_field( $object_type, $id, $object_name = null, $args = array() ) {

		if ( empty( $id ) && empty( $args ) ) {
			return;
		}

		$control = array();

		if ( is_a( $id, 'WP_Fields_API_Field' ) ) {
			$field = $id;

			$id = $field->id;
		} else {
			// Save for late init
			$field = $args;

			if ( isset( $field['control'] ) ) {
				$control = $field['control'];

				// Remove from field args
				unset( $field['control'] );
			}
		}

		if ( empty( $object_name ) && ! empty( $object_type ) ) {
			$object_name = '_' . $object_type; // Default to _object_type for internal handling
		}

		if ( ! isset( self::$fields[ $object_type ] ) ) {
			self::$fields[ $object_type ] = array();
		}

		if ( ! isset( self::$fields[ $object_type ][ $object_name ] ) ) {
			self::$fields[ $object_type ][ $object_name ] = array();
		}

		self::$fields[ $object_type ][ $object_name ][ $id ] = $field;

		// Control handling
		if ( ! empty( $control ) ) {
			// Generate Control ID if not set
			if ( empty( $control['id'] ) ) {
				$control['id'] = 'control_' . sanitize_key( $object_type ) . '_' . sanitize_key( $object_name ) . '_' . sanitize_key( $id );
			}

			// Get Control ID
			$control_id = $control['id'];

			// Remove ID from control args
			unset( $control['id'] );

			// Add field
			$control['fields'] = $id;

			// Add control for field
			$this->add_control( $object_type, $control_id, $object_name, $control );
		}

	}

	/**
	 * Retrieve a field.
	 *
	 * @access public
	 *
	 * @param string $object_type Object type.
	 * @param string $id          Field ID.
	 * @param string $object_name Object name (for post types and taxonomies).
	 *
	 * @return WP_Fields_API_Field|null
	 */
	public function get_field( $object_type, $id, $object_name = null ) {

		if ( empty( $object_name ) && ! empty( $object_type ) ) {
			$object_name = '_' . $object_type; // Default to _object_type for internal handling
		}

		$field = null;

		if ( isset( self::$fields[ $object_type ][ $object_name ][ $id ] ) ) {
			// Late init
			self::$fields[ $object_type ][ $object_name ][ $id ] = $this->setup_field( $object_type, $id, $object_name, self::$fields[ $object_type ][ $object_name ][ $id ] );

			$field = self::$fields[ $object_type ][ $object_name ][ $id ];
		}

		return $field;

	}

	/**
	 * Setup the field.
	 *
	 * @access public
	 *
	 * @param string $object_type Object type.
	 * @param string $id          ID of the field.
	 * @param string $object_name Object name (for post types and taxonomies).
	 * @param array  $args        Field arguments.
	 *
	 * @return WP_Fields_API_Field|null $field The field object.
	 */
	public function setup_field( $object_type, $id, $object_name = null, $args = null ) {

		$field = null;

		$field_class = 'WP_Fields_API_Field';

		if ( is_a( $args, $field_class ) ) {
			$field = $args;
		} elseif ( is_array( $args ) ) {
			$args['object_name'] = $object_name;

			if ( ! empty( $args['type'] ) ) {
				if ( ! empty( self::$registered_field_types[ $args['type'] ] ) ) {
					$field_class = self::$registered_field_types[ $args['type'] ];
				} elseif ( in_array( $args['type'], self::$registered_field_types ) ) {
					$field_class = $args['type'];
				}
			}

			$field = new $field_class( $object_type, $id, $args );
		}

		return $field;

	}

	/**
	 * Remove a field.
	 *
	 * @access public
	 *
	 * @param string $object_type Object type, set true to remove all fields.
	 * @param string $id          Field ID to remove, set true to remove all fields from an object.
	 * @param string $object_name Object name (for post types and taxonomies), set true to remove to all objects from an object type.
	 */
	public function remove_field( $object_type, $id, $object_name = null ) {

		if ( true === $object_type ) {
			// Remove all fields
			self::$fields = array();
		} elseif ( true === $object_name ) {
			// Remove all fields for an object type
			if ( isset( self::$fields[ $object_type ] ) ) {
				unset( self::$fields[ $object_type ] );
			}
		} else {
			if ( empty( $object_name ) && ! empty( $object_type ) ) {
				$object_name = '_' . $object_type; // Default to _object_type for internal handling
			}

			if ( true === $id && null !== $object_name ) {
				// Remove all fields for an object type
				if ( isset( self::$fields[ $object_type ][ $object_name ] ) ) {
					unset( self::$fields[ $object_type ][ $object_name ] );
				}
			} elseif ( isset( self::$fields[ $object_type ][ $object_name ][ $id ] ) ) {
				// Remove field from object type and name
				unset( self::$fields[ $object_type ][ $object_name ][ $id ] );
			}
		}

	}

	/**
	 * Register a field type.
	 *
	 * @access public
	 *
	 * @see    WP_Fields_API_Field
	 *
	 * @param string $type         Field type ID.
	 * @param string $screen_class Name of a custom field type which is a subclass of WP_Fields_API_Field.
	 */
	public function register_field_type( $type, $field_class = null ) {

		if ( null === $field_class ) {
			$field_class = $type;
		}

		self::$registered_field_types[ $type ] = $field_class;

	}

	/**
	 * Get the registered controls.
	 *
	 * @access public
	 *
	 * @param string $object_type Object type.
	 * @param string $object_name Object name (for post types and taxonomies).
	 * @param string $section     Section ID.
	 *
	 * @return WP_Fields_API_Control[]
	 */
	public function get_controls( $object_type = null, $object_name = null, $section = null ) {

		if ( empty( $object_name ) && ! empty( $object_type ) ) {
			$object_name = '_' . $object_type; // Default to _object_type for internal handling
		}

		$controls = array();

		if ( null === $object_type ) {
			// Late init
			foreach ( self::$controls as $object_type => $object_names ) {
				foreach ( $object_names as $object_name => $controls ) {
					$this->get_controls( $object_type, $object_name );
				}
			}

			$controls = self::$controls;

			// Get only controls for a specific section
			if ( null !== $section ) {
				$section_controls = array();

				foreach ( $controls as $object_type => $object_names ) {
					foreach ( $object_names as $object_name => $object_controls ) {
						foreach ( $object_controls as $id => $control ) {
							if ( $section == $control->section->id ) {
								if ( ! isset( $section_controls[ $object_type ] ) ) {
									$section_controls[ $object_type ] = array();
								}

								if ( ! isset( $section_controls[ $object_type ][ $object_name ] ) ) {
									$section_controls[ $object_type ][ $object_name ] = array();
								}

								$section_controls[ $object_type ][ $object_name ][ $id ] = $control;
							}
						}
					}
				}

				$controls = $section_controls;
			}
		} elseif ( isset( self::$controls[ $object_type ][ $object_name ] ) ) {
			// Late init
			foreach ( self::$controls[ $object_type ][ $object_name ] as $id => $control ) {
				// Late init
				self::$controls[ $object_type ][ $object_name ][ $id ] = $this->setup_control( $object_type, $id, $object_name, $control );
			}

			$controls = self::$controls[ $object_type ][ $object_name ];

			// Get only controls for a specific section
			if ( null !== $section ) {
				$section_controls = array();

				foreach ( $controls as $id => $control ) {
					// $control->section is not an object, like $control->field is
					if ( $section == $control->section ) {
						$section_controls[ $id ] = $control;
					}
				}

				$controls = $section_controls;
			}
		} elseif ( true === $object_name ) {
			// Get all fields

			// Late init
			foreach ( self::$controls[ $object_type ] as $object_name => $object_controls ) {
				$controls = array_merge( $controls, array_values( $this->get_controls( $object_type, $object_name, $section ) ) );
			}
		}

		return $controls;

	}

	/**
	 * Add a field control.
	 *
	 * @access public
	 *
	 * @param string                       $object_type Object type.
	 * @param WP_Fields_API_Control|string $id          Field Control object, or ID.
	 * @param string                       $object_name Object name (for post types and taxonomies).
	 * @param array                        $args        Control arguments; passed to WP_Fields_API_Control
	 *                                                  constructor.
	 */
	public function add_control( $object_type, $id, $object_name = null, $args = array() ) {

		if ( empty( $id ) && empty( $args ) ) {
			return;
		}

		if ( is_a( $id, 'WP_Fields_API_Control' ) ) {
			$control = $id;

			$id = $control->id;
		} else {
			// Save for late init
			$control = $args;
		}

		if ( empty( $object_name ) && ! empty( $object_type ) ) {
			$object_name = '_' . $object_type; // Default to _object_type for internal handling
		}

		if ( ! isset( self::$controls[ $object_type ] ) ) {
			self::$controls[ $object_type ] = array();
		}

		if ( ! isset( self::$controls[ $object_type ][ $object_name ] ) ) {
			self::$controls[ $object_type ][ $object_name ] = array();
		}

		self::$controls[ $object_type ][ $object_name ][ $id ] = $control;

	}

	/**
	 * Retrieve a field control.
	 *
	 * @access public
	 *
	 * @param string $object_type Object type.
	 * @param string $id          ID of the control.
	 * @param string $object_name Object name (for post types and taxonomies).
	 *
	 * @return WP_Fields_API_Control|null $control The control object.
	 */
	public function get_control( $object_type, $id, $object_name = null ) {

		if ( empty( $object_name ) && ! empty( $object_type ) ) {
			$object_name = '_' . $object_type; // Default to _object_type for internal handling
		}

		$control = null;

		if ( isset( self::$controls[ $object_type ][ $object_name ][ $id ] ) ) {
			// Late init
			self::$controls[ $object_type ][ $object_name ][ $id ] = $this->setup_control( $object_type, $id, $object_name, self::$controls[ $object_type ][ $object_name ][ $id ] );

			$control = self::$controls[ $object_type ][ $object_name ][ $id ];
		}

		return $control;

	}

	/**
	 * Setup the field control.
	 *
	 * @access public
	 *
	 * @param string $object_type Object type.
	 * @param string $id          ID of the control.
	 * @param string $object_name Object name (for post types and taxonomies).
	 * @param array  $args        Control arguments.
	 *
	 * @return WP_Fields_API_Control|null $control The control object.
	 */
	public function setup_control( $object_type, $id, $object_name = null, $args = null ) {

		$control = null;

		$control_class = 'WP_Fields_API_Control';

		if ( is_a( $args, $control_class ) ) {
			$control = $args;
		} elseif ( is_array( $args ) ) {
			$args['object_name'] = $object_name;

			if ( ! empty( $args['type'] ) ) {
				if ( ! empty( self::$registered_control_types[ $args['type'] ] ) ) {
					$control_class = self::$registered_control_types[ $args['type'] ];
				} elseif ( in_array( $args['type'], self::$registered_control_types ) ) {
					$control_class = $args['type'];
				}
			}

			$control = new $control_class( $object_type, $id, $args );
		}

		return $control;

	}

	/**
	 * Remove a field control.
	 *
	 * @access public
	 *
	 * @param string $object_type Object type, set true to remove all controls.
	 * @param string $id          Control ID to remove, set true to remove all controls from an object.
	 * @param string $object_name Object name (for post types and taxonomies), set true to remove to all objects from an object type.
	 */
	public function remove_control( $object_type, $id, $object_name = null ) {

		if ( true === $object_type ) {
			// Remove all controls
			self::$controls = array();
		} elseif ( true === $object_name ) {
			// Remove all controls for an object type
			if ( isset( self::$controls[ $object_type ] ) ) {
				unset( self::$controls[ $object_type ] );
			}
		} else {
			if ( empty( $object_name ) && ! empty( $object_type ) ) {
				$object_name = '_' . $object_type; // Default to _object_type for internal handling
			}

			if ( true === $id && null !== $object_name ) {
				// Remove all controls for an object type
				if ( isset( self::$controls[ $object_type ][ $object_name ] ) ) {
					unset( self::$controls[ $object_type ][ $object_name ] );
				}
			} elseif ( isset( self::$controls[ $object_type ][ $object_name ][ $id ] ) ) {
				// Remove control from object type and name
				unset( self::$controls[ $object_type ][ $object_name ][ $id ] );
			}
		}

	}

	/**
	 * Register a field control type.
	 *
	 * @access public
	 *
	 * @see    WP_Fields_API_Control
	 *
	 * @param string $type          Control type ID.
	 * @param string $control_class Name of a custom control which is a subclass of WP_Fields_API_Control.
	 */
	public function register_control_type( $type, $control_class = null ) {

		if ( null === $control_class ) {
			$control_class = $type;
		}

		self::$registered_control_types[ $type ] = $control_class;

	}

	/**
	 * Render JS templates for all registered control types.
	 *
	 * @access public
	 */
	public function render_control_templates() {

		/**
		 * @var $control WP_Fields_API_Control
		 */
		foreach ( self::$registered_control_types as $control_type => $control_class ) {
			$control = $this->setup_control( null, 'temp', null, array( 'type' => $control_type ) );

			$control->print_template();
		}

	}

	/**
	 * Helper function to compare two objects by priority, ensuring sort stability via instance_number.
	 *
	 * @access protected
	 *
	 * @param  {WP_Fields_API_Screen|WP_Fields_API_Section|WP_Fields_API_Control} $a Object A.
	 * @param  {WP_Fields_API_Screen|WP_Fields_API_Section|WP_Fields_API_Control} $b Object B.
	 *
	 * @return int
	 */
	protected final function _cmp_priority( $a, $b ) {

		if ( is_int( $a->priority ) && is_int( $b->priority ) ) {
			// Priority integers
			$compare = $a->priority - $b->priority;

			if ( $a->priority === $b->priority ) {
				$compare = $a->instance_number - $a->instance_number;
			}
		} else {
			// Priority strings
			$compare = 0;
		}

		return $compare;

	}

	/**
	 * Prepare screens, sections, and controls for all objects.
	 *
	 * For each, check if required related components exist,
	 * whether the user has the necessary capabilities,
	 * and sort by priority.
	 *
	 * @access public
	 *
	 * @param string $object_type Object type.
	 */
	public function prepare_controls( $object_type = null ) {

		// Prepare controls for all object types
		foreach ( self::$fields as $object => $object_names ) {
			if ( null === $object_type || $object === $object_type ) {
				foreach ( $object_names as $object_name => $fields ) {
					$this->prepare_object_controls( $object, $object_name );
				}
			}
		}

	}

	/**
	 * Prepare object screens, sections, and controls.
	 *
	 * For each, check if required related components exist,
	 * whether the user has the necessary capabilities,
	 * and sort by priority.
	 *
	 * @access public
	 *
	 * @param string $object_type Object type.
	 * @param string $object_name Object name (for post types and taxonomies).
	 */
	public function prepare_object_controls( $object_type, $object_name = null ) {

		if ( empty( $object_name ) && ! empty( $object_type ) ) {
			$object_name = '_' . $object_type; // Default to _object_type for internal handling
		}

		// Reset prepared IDs
		$prepared_ids = array(
			'control'   => array(),
			'section'   => array(),
			'screen'    => array(),
			'container' => array(),
		);

		// Setup

		// Get controls
		$controls = $this->get_controls( $object_type, $object_name );

		// Get sections
		$sections = $this->get_sections( $object_type, $object_name );

		// Get screens
		$screens = $this->get_screens( $object_type, $object_name );

		// Controls

		// Sort controls by priority
		uasort( $controls, array( $this, '_cmp_priority' ) );

		foreach ( $controls as $id => $control ) {
			// Check if section or screen exists
			if ( $control->section && ! isset( $sections[ $control->section ] ) ) {
				continue;
			} elseif ( $control->screen && ! isset( $screens[ $control->screen ] ) ) {
				continue;
			}

			// Check if control can be used by user
			if ( ! $control->check_capabilities() ) {
				continue;
			}

			// Add to prepared IDs
			$prepared_ids['control'][] = $id;

			if ( $control->section ) {
				// Add control to section controls
				$sections[ $control->section ]->controls[] = $control;
			} elseif ( $control->screen ) {
				// Add control to screen controls
				$screens[ $control->screen ]->controls[] = $control;
			}
		}

		// Sections

		// Sort sections by priority
		uasort( $sections, array( $this, '_cmp_priority' ) );

		foreach ( $sections as $id => $section ) {
			// Check if section can be seen by user
			if ( ! $section->check_capabilities() ) {
				continue;
			}

			if ( $section->controls ) {
				// Sort section controls by priority
				usort( $section->controls, array( $this, '_cmp_priority' ) );
			}

			if ( ! $section->screen ) {
				// Top-level section.

				// Add to prepared IDs
				$prepared_ids['section'][]   = $id;
				$prepared_ids['container'][] = $id;
			} elseif ( $section->screen && isset( $screens[ $section->screen ] ) ) {
				// This section belongs to a screen.
				$screens[ $section->screen ]->sections[ $id ] = $section;

				// Add to prepared IDs
				$prepared_ids['section'][]   = $id;
				$prepared_ids['container'][] = $id;
			}
		}

		// Screens

		// Sort screens by priority
		uasort( $screens, array( $this, '_cmp_priority' ) );

		foreach ( $screens as $id => $screen ) {
			// Check if screen has sections or can be seen by user
			if ( ! $screen->check_capabilities() ) {
				continue;
			}

			if ( $screen->sections ) {
				// Sort screen sections by priority
				uasort( $screen->sections, array( $this, '_cmp_priority' ) );
			}

			// Add to prepared IDs
			$prepared_ids['screen'][]    = $id;
			$prepared_ids['container'][] = $id;
		}

		// Merge screens and top-level sections together.
		$containers = array_merge( $screens, $sections );

		// Sort containers by priority
		uasort( $containers, array( $this, '_cmp_priority' ) );

		// Saving

		// Save controls
		if ( ! isset( self::$controls[ $object_type ] ) ) {
			self::$controls[ $object_type ] = array();
		}

		if ( ! isset( self::$controls[ $object_type ][ $object_name ] ) ) {
			self::$controls[ $object_type ][ $object_name ] = array();
		}

		self::$controls[ $object_type ][ $object_name ] = $controls;

		// Save sections
		if ( ! isset( self::$sections[ $object_type ] ) ) {
			self::$sections[ $object_type ] = array();
		}

		if ( ! isset( self::$sections[ $object_type ][ $object_name ] ) ) {
			self::$sections[ $object_type ][ $object_name ] = array();
		}

		self::$sections[ $object_type ][ $object_name ] = $sections;

		// Save screens
		if ( ! isset( self::$screens[ $object_type ] ) ) {
			self::$screens[ $object_type ] = array();
		}

		if ( ! isset( self::$screens[ $object_type ][ $object_name ] ) ) {
			self::$screens[ $object_type ][ $object_name ] = array();
		}

		self::$screens[ $object_type ][ $object_name ] = $screens;

		// Save containers
		if ( ! isset( self::$containers[ $object_type ] ) ) {
			self::$containers[ $object_type ] = array();
		}

		if ( ! isset( self::$containers[ $object_type ][ $object_name ] ) ) {
			self::$containers[ $object_type ][ $object_name ] = array();
		}

		self::$containers[ $object_type ][ $object_name ] = $containers;

		// Saved prepared IDs
		if ( ! isset( self::$prepared_ids[ $object_type ] ) ) {
			self::$prepared_ids[ $object_type ] = array();
		}

		if ( ! isset( self::$prepared_ids[ $object_type ][ $object_name ] ) ) {
			self::$prepared_ids[ $object_type ][ $object_name ] = array();
		}

		self::$prepared_ids[ $object_type ][ $object_name ] = $prepared_ids;

	}

	/**
	 * Register some default controls.
	 *
	 * @access public
	 */
	public function register_controls() {

		/* Control Types */
		$this->register_control_type( 'text', 'WP_Fields_API_Control' );
		$this->register_control_type( 'textarea', 'WP_Fields_API_Textarea_Control' );
		$this->register_control_type( 'checkbox', 'WP_Fields_API_Checkbox_Control' );
		$this->register_control_type( 'multi-checkbox', 'WP_Fields_API_Multi_Checkbox_Control' );
		$this->register_control_type( 'radio', 'WP_Fields_API_Radio_Control' );
		$this->register_control_type( 'select', 'WP_Fields_API_Select_Control' );
		$this->register_control_type( 'dropdown-pages', 'WP_Fields_API_Dropdown_Pages_Control' );
		$this->register_control_type( 'color', 'WP_Fields_API_Color_Control' );
		$this->register_control_type( 'media', 'WP_Fields_API_Media_Control' );
		$this->register_control_type( 'upload', 'WP_Fields_API_Upload_Control' );
		$this->register_control_type( 'image', 'WP_Fields_API_Image_Control' );

		/**
		 * Fires once WordPress has loaded, allowing control types to be registered.
		 *
		 * @param WP_Fields_API $this WP_Fields_API instance.
		 */
		do_action( 'fields_register_controls', $this );

	}

	/**
	 * Check if an object ID was prepared or not
	 *
	 * @access public
	 *
	 * @param string $object_type Object type.
	 * @param string $type        Type including screen, section, or field.
	 * @param string $id          Object ID.
	 * @param string $object_name Object name (for post types and taxonomies).
	 *
	 * @return boolean
	 */
	public function is_prepared( $object_type, $type, $id, $object_name = null ) {

		if ( empty( $object_name ) && ! empty( $object_type ) ) {
			$object_name = '_' . $object_type; // Default to _object_type for internal handling
		}

		$found = false;

		if ( ! empty( self::$prepared_ids[ $object_type ][ $object_name ][ $type ] ) && in_array( $id, self::$prepared_ids[ $object_type ][ $object_name ][ $type ] ) ) {
			$found = true;
		}

		return $found;

	}

}