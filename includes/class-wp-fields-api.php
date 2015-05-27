<?php
/**
 * Class WP_Fields_API
 *
 * @package WordPress
 * @subpackage Fields_API
 */
final class WP_Fields_API {

	/**
	 * Registered Fields
	 *
	 * @access protected
	 * @var array
	 */
	protected static $fields = array();

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
	 * Registered Controls
	 *
	 * @access protected
	 * @var array
	 */
	protected static $controls = array();

	/**
	 * Controls that may be rendered from JS templates.
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
	 * Unsanitized values for Fields.
	 *
	 * @var array|false
	 */
	private $_post_values;

	/**
	 * Constructor.
	 *
	 * @access public
	 */
	public function __construct() {

	    require_once( WP_FIELDS_API_DIR . 'includes/class-wp-fields-api-field.php' );
	    require_once( WP_FIELDS_API_DIR . 'includes/class-wp-fields-api-control.php' );
	    require_once( WP_FIELDS_API_DIR . 'includes/class-wp-fields-api-section.php' );
	    require_once( WP_FIELDS_API_DIR . 'includes/class-wp-fields-api-screen.php' );

		// Register our wp_loaded() first before WP_Customize_Manage::wp_loaded()
		add_action( 'wp_loaded', array( $this, 'wp_loaded' ), 9 );

		add_action( 'fields_register',      array( $this, 'register_controls' ) );
		add_action( 'fields_controls_init', array( $this, 'prepare_controls' ) );

	}

	/**
	 * Allow Fields, Sections, Screens, and Controls to be registered
	 *
	 * @access public
	 */
	public function wp_loaded() {

		/**
		 * Fires once WordPress has loaded, allowing scripts and styles to be initialized.
		 *
		 * @param WP_Fields_API $this WP_Fields_API instance.
		 */
		do_action( 'fields_register', $this );

	}

	/**
	 * Get the registered fields.
	 *
	 * @access public
	 *
	 * @param string $object Object type.
	 *
	 * @return array<WP_Fields_API_Field>
	 */
	public function get_fields( $object = null ) {

		$fields = array();

		if ( null === $object ) {
			// Late init
			foreach ( self::$fields as $object => $controls ) {
				$this->get_fields( $object );
			}

			$fields = self::$fields;
		} elseif ( isset( self::$fields[ $object ] ) ) {
			// Late init
			foreach ( self::$fields[ $object ] as $id => $field ) {
				if ( is_array( $field ) ) {
					self::$fields[ $object ][ $id ] = new WP_Fields_API_Field( $object, $id, $field );
				}
			}

			$fields = self::$fields[ $object ];
		}

		return $fields;

	}

	/**
	 * Add a field.
	 *
	 * @access public
	 *
	 * @param string $object                    Object type.
	 * @param WP_Fields_API_Field|string $id  Fields API Field object, or ID.
	 * @param array $args                       Field arguments; passed to WP_Fields_API_Field
	 *                                          constructor.
	 */
	public function add_field( $object, $id, $args = array() ) {

		$control = false;

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

		self::$fields[ $object ] = self::$fields[ $object ] || array();

		self::$fields[ $object ][ $id ] = $field;

		// Control handling
		if ( $control ) {
			// Generate Control ID if not set
			if ( empty( $control['id'] ) ) {
				$control['id'] = 'fields_' . sanitize_key( $object ) . '_' . sanitize_key( $id ) . '_' . sanitize_key( $field );
			}

			// Get Control ID
			$control_id = $control['id'];

			// Remove ID from control args
			unset( $control['id'] );

			// Add control for field
			$this->add_control( $object, $control_id, $control );
		}

	}

	/**
	 * Retrieve a field.
	 *
	 * @access public
	 *
	 * @param string $object Object type.
	 * @param string $id     Field Field ID.
	 *
	 * @return WP_Fields_API_Field|null
	 */
	public function get_field( $object, $id ) {

		$field = null;

		if ( isset( self::$fields[ $object ][ $id ] ) ) {
			// Late init
			if ( is_array( self::$fields[ $object ][ $id ] ) ) {
				self::$fields[ $object ][ $id ] = new WP_Fields_API_Field( $object, $id, self::$fields[ $object ][ $id ] );
			}

			$field = self::$fields[ $object ][ $id ];
		}

		return $field;

	}

	/**
	 * Remove a field.
	 *
	 * @access public
	 *
	 * @param string $object Object type.
	 * @param string $id     Field Field ID.
	 */
	public function remove_field( $object, $id ) {

		if ( isset( self::$fields[ $object ][ $id ] ) ) {
			unset( self::$fields[ $object ][ $id ] );
		}

	}

	/**
	 * Get the registered containers.
	 *
	 * @access public
	 *
	 * @param string $object Object type.
	 *
	 * @return array<WP_Fields_API_Screen|WP_Fields_API_Section>
	 */
	public function get_containers( $object = null ) {

		$containers = array();

		if ( null === $object ) {
			$containers = self::$containers;
		} elseif ( isset( self::$containers[ $object ] ) ) {
			$containers = self::$containers[ $object ];
		}

		return $containers;

	}

	/**
	 * Get the registered screens.
	 *
	 * @access public
	 *
	 * @param string $object Object type.
	 *
	 * @return array<WP_Fields_API_Screen>
	 */
	public function get_screens( $object = null ) {

		$screens = array();

		if ( null === $object ) {
			// Late init
			foreach ( self::$screens as $object => $controls ) {
				$this->get_screens( $object );
			}

			$screens = self::$screens;
		} elseif ( isset( self::$screens[ $object ] ) ) {
			// Late init
			foreach ( self::$screens[ $object ] as $id => $screen ) {
				if ( is_array( $screen ) ) {
					self::$screens[ $object ][ $id ] = new WP_Fields_API_Screen( $object, $id, $screen );
				}
			}

			$screens = self::$screens[ $object ];
		}

		return $screens;

	}

	/**
	 * Add a field screen.
	 *
	 * @access public
	 *
	 * @param string $object                  Object type.
	 * @param WP_Fields_API_Screen|string $id  Field Screen object, or Screen ID.
	 * @param array $args                     Optional. Screen arguments. Default empty array.
	 */
	public function add_screen( $object, $id, $args = array() ) {

		if ( is_a( $id, 'WP_Fields_API_Screen' ) ) {
			$screen = $id;

			$id = $screen->id;
		} else {
			// Save for late init
			$screen = $args;
		}

		self::$screens[ $id ] = $screen;

	}

	/**
	 * Retrieve a field screen.
	 *
	 * @access public
	 *
	 * @param string $object       Object type.
	 * @param string $id           Screen ID to get.
	 *
	 * @return WP_Fields_API_Screen Requested screen instance.
	 */
	public function get_screen( $object, $id ) {

		$screen = null;

		if ( isset( self::$screens[ $object ][ $id ] ) ) {
			// Late init
			if ( is_array( self::$screens[ $object ][ $id ] ) ) {
				self::$screens[ $object ][ $id ] = new WP_Fields_API_Screen( $object, $id, self::$fields[ $object ][ $id ] );
			}

			$screen = self::$screens[ $object ][ $id ];
		}

		return $screen;

	}

	/**
	 * Remove a field screen.
	 *
	 * @access public
	 *
	 * @param string $object Object type.
	 * @param string $id     Screen ID to remove.
	 */
	public function remove_screen( $object, $id ) {

		if ( isset( self::$screens[ $object ][ $id ] ) ) {
			unset( self::$screens[ $object ][ $id ] );
		}

	}

	/**
	 * Get the registered sections.
	 *
	 * @access public
	 *
	 * @param string $object Object type.
	 * @param string $screen  Screen ID.
	 *
	 * @return array<WP_Fields_API_Section>
	 */
	public function get_sections( $object = null, $screen = null ) {

		$sections = array();

		if ( null === $object ) {
			// Late init
			foreach ( self::$sections as $object => $controls ) {
				$this->get_sections( $object );
			}

			$sections = self::$sections;
		} elseif ( isset( self::$sections[ $object ] ) ) {
			// Late init
			foreach ( self::$sections[ $object ] as $id => $section ) {
				if ( is_array( $section ) ) {
					self::$sections[ $object ][ $id ] = new WP_Fields_API_Section( $object, $id, $section );
				}
			}

			$sections = self::$sections[ $object ];

			// Get only sections for a specific screen
			if ( $screen ) {
				$screen_sections = array();

				foreach ( $sections as $id => $section ) {
					if ( $screen == $section->screen ) {
						$screen_sections[ $id ] = $section;
					}
				}

				$sections = $screen_sections;
			}
		}

		return $sections;

	}

	/**
	 * Add a field section.
	 *
	 * @access public
	 *
	 * @param string $object                    Object type.
	 * @param WP_Fields_API_Section|string $id  Field Section object, or Section ID.
	 * @param array                       $args Section arguments.
	 */
	public function add_section( $object, $id, $args = array() ) {

		if ( is_a( $id, 'WP_Fields_API_Section' ) ) {
			$section = $id;

			$id = $section->id;
		} else {
			// Save for late init
			$section = $args;
		}

		self::$sections[ $object ] = self::$sections[ $object ] || array();

		self::$sections[ $object ][ $id ] = $section;

	}

	/**
	 * Retrieve a field section.
	 *
	 * @access public
	 *
	 * @param string $object         Object type.
	 * @param string $id             Section ID to get.
	 *
	 * @return WP_Fields_API_Section Requested section instance.
	 */
	public function get_section( $object, $id ) {

		$section = null;

		if ( isset( self::$sections[ $object ][ $id ] ) ) {
			// Late init
			if ( is_array( self::$sections[ $object ][ $id ] ) ) {
				self::$sections[ $object ][ $id ] = new WP_Fields_API_Section( $object, $id, self::$sections[ $object ][ $id ] );
			}

			$section = self::$sections[ $object ][ $id ];
		}

		return $section;

	}

	/**
	 * Remove a field section.
	 *
	 * @access public
	 *
	 * @param string $object Object type.
	 * @param string $id     Section ID to remove.
	 */
	public function remove_section( $object, $id ) {

		if ( isset( self::$sections[ $object ][ $id ] ) ) {
			unset( self::$sections[ $object ][ $id ] );
		}

	}

	/**
	 * Get the registered controls.
	 *
	 * @access public
	 *
	 * @param string $object Object type.
	 * @param string $screen  Screen ID.
	 *
	 * @return array<WP_Fields_API_Control>
	 */
	public function get_controls( $object = null ) {

		$controls = array();

		if ( null === $object ) {
			// Late init
			foreach ( self::$controls as $object => $controls ) {
				$this->get_controls( $object );
			}

			$controls = self::$controls;
		} elseif ( isset( self::$controls[ $object ] ) ) {
			// Late init
			foreach ( self::$controls[ $object ] as $id => $control ) {
				if ( is_array( $control ) ) {
					self::$controls[ $object ][ $id ] = new WP_Fields_API_Control( $object, $id, $control );
				}
			}

			$controls = self::$controls[ $object ];
		}

		return $controls;

	}

	/**
	 * Add a field control.
	 *
	 * @access public
	 *
	 * @param string $object Object type.
	 * @param WP_Fields_API_Control|string $id   Field Control object, or ID.
	 * @param array                       $args  Control arguments; passed to WP_Fields_API_Control
	 *                                           constructor.
	 */
	public function add_control( $object, $id, $args = array() ) {

		if ( is_a( $id, 'WP_Fields_API_Control' ) ) {
			$control = $id;

			$id = $control->id;
		} else {
			// Save for late init
			$control = $args;
		}

		self::$controls[ $object ] = self::$controls[ $object ] || array();

		self::$controls[ $object ][ $id ] = $control;

	}

	/**
	 * Retrieve a field control.
	 *
	 * @access public
	 *
	 * @param string $object                  Object type.
	 * @param string $id                      ID of the control.
	 *
	 * @return WP_Fields_API_Control $control The control object.
	 */
	public function get_control( $object, $id ) {

		$control = null;

		if ( isset( self::$controls[ $object ][ $id ] ) ) {
			// Late init
			if ( is_array( self::$controls[ $object ][ $id ] ) ) {
				self::$controls[ $object ][ $id ] = new WP_Fields_API_Control( $object, $id, self::$controls[ $object ][ $id ] );
			}

			$control = self::$controls[ $object ][ $id ];
		}

		return $control;

	}

	/**
	 * Remove a field control.
	 *
	 * @access public
	 *
	 * @param string $object Object type.
	 * @param string $id     Control ID to remove.
	 */
	public function remove_control( $object, $id ) {

		if ( isset( self::$controls[ $object ][ $id ] ) ) {
			unset( self::$controls[ $object ][ $id ] );
		}

	}

	/**
	 * Register a field control type.
	 *
	 * Registered types are eligible to be rendered via JS and created dynamically.
	 *
	 * @access public
	 *
	 * @param string $control Name of a custom control which is a subclass of
	 *                        {@see WP_Fields_API_Control}.
	 */
	public function register_control_type( $control ) {

		self::$registered_control_types[] = $control;

	}

	/**
	 * Render JS templates for all registered control types.
	 *
	 * @access public
	 */
	public function render_control_templates() {

		foreach ( self::$registered_control_types as $control_type ) {
			$control = new $control_type( 'temp', array() );

			$control->print_template();
		}

	}

	/**
	 * Helper function to compare two objects by priority, ensuring sort stability via instance_number.
	 *
	 * @access protected
	 *
	 * @param {WP_Fields_API_Screen|WP_Fields_API_Section|WP_Fields_API_Control} $a Object A.
	 * @param {WP_Fields_API_Screen|WP_Fields_API_Section|WP_Fields_API_Control} $b Object B.
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
	 */
	public function prepare_controls() {

		// Get object types
		$objects = array_keys( self::$fields );

		// Prepare controls for all object types
		foreach ( $objects as $object ) {
			$this->prepare_object_controls( $object );
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
	 * @param string $object Object type.
	 */
	public function prepare_object_controls( $object ) {

		// Reset prepared IDs
		$prepared_ids = array(
			'control'   => array(),
			'section'   => array(),
			'screen'     => array(),
			'container' => array()
		);

		// Setup

		// Get controls
		$controls = $this->get_controls( $object );

		// Get sections
		$sections = $this->get_sections( $object );

		// Get screens
		$screens = $this->get_screens( $object );

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
			// Check if section has controls or can be seen by user
			if ( ! $section->controls || ! $section->check_capabilities() ) {
				continue;
			}

			// Sort section controls by priority
			usort( $section->controls, array( $this, '_cmp_priority' ) );

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
			if ( ! $screen->sections || ! $screen->check_capabilities() ) {
				continue;
			}

			// Sort screen sections by priority
			uasort( $screen->sections, array( $this, '_cmp_priority' ) );

			// Add to prepared IDs
			$prepared_ids['screen'][]     = $id;
			$prepared_ids['container'][] = $id;
		}

		// Merge screens and top-level sections together.
		$containers = array_merge( $screens, $sections );

		// Sort containers by priority
		uasort( $containers, array( $this, '_cmp_priority' ) );

		// Saving

		// Save controls
		self::$controls[ $object ] = $controls;

		// Save sections
		self::$sections[ $object ] = $sections;

		// Save screens
		self::$screens[ $object ] = $screens;

		// Save containers
		self::$containers[ $object ] = $containers;

		// Saved prepared IDs
		self::$prepared_ids[ $object ] = $prepared_ids;

	}

	/**
	 * Register some default controls.
	 *
	 * @access public
	 */
	public function register_controls() {

		/* Control Types (custom control classes) */
		$this->register_control_type( 'WP_Fields_API_Color_Control' );
		$this->register_control_type( 'WP_Fields_API_Upload_Control' );
		$this->register_control_type( 'WP_Fields_API_Image_Control' );
		$this->register_control_type( 'WP_Fields_API_Background_Image_Control' );

	}

	/**
	 * Check if an object ID was prepared or not
	 *
	 * @access public
	 *
	 * @param string $object Object type.
	 * @param string $type   Type including screen, section, or field.
	 * @param string $id     Object ID.
	 *
	 * @return boolean
	 */
	public function is_prepared( $object, $type, $id ) {

		$found = false;

		if ( ! empty( self::$prepared_ids[ $object ][ $type ] ) && in_array( $id, self::$prepared_ids[ $object ][ $type ] ) ) {
			$found = true;
		}

		return $found;

	}

	/**
	 * Parse the incoming $_POST['customized'] JSON data and store the unsanitized
	 * fields for subsequent post_value() lookups.
	 *
	 * @return array
	 */
	public function unsanitized_post_values() {

		if ( ! isset( $this->_post_values ) ) {
			$this->_post_values = false;
		}

		if ( empty( $this->_post_values ) ) {
			return array();
		}

		return $this->_post_values;

	}

	/**
	 * Return the sanitized value for a given field from the request's POST data.
	 * Introduced 'default' parameter.
	 *
	 * @param WP_Fields_API_Field $field A WP_Fields_API_Field derived object
	 * @param mixed                $default value returned $field has no post value (added in 4.2.0).
	 *
	 * @return string|mixed $post_value Sanitized value or the $default provided
	 */
	public function post_value( $field, $default = null ) {

		$post_values = $this->unsanitized_post_values();

		if ( array_key_exists( $field->id, $post_values ) ) {
			return $field->sanitize( $post_values[ $field->id ] );
		}

		return $default;

	}

}