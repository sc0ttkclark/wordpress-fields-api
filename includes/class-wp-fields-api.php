<?php
/**
 * Class WP_Fields_API
 *
 * @package WordPress
 */
final class WP_Fields_API {

	/**
	 * Registered Settings
	 *
	 * @access protected
	 * @var array
	 */
	protected static $settings = array();

	/**
	 * Registered Containers
	 *
	 * @access protected
	 * @var array
	 */
	protected static $containers = array();

	/**
	 * Registered Panels
	 *
	 * @access protected
	 * @var array
	 */
	protected static $panels = array();

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
	 * IDs for Panels, Sections, and Controls which are valid and have been prepared.
	 *
	 * @access protected
	 * @var array
	 */
	protected static $prepared_ids = array();

	/**
	 * Get the registered settings.
	 *
	 * @access public
	 *
	 * @param string $object Object type.
	 *
	 * @return array<WP_Fields_API_Setting>
	 */
	public function get_settings( $object = null ) {

		$settings = array();

		if ( null === $object ) {
			// Late init
			foreach ( self::$settings as $object => $controls ) {
				$this->get_settings( $object );
			}

			$settings = self::$settings;
		} elseif ( isset( self::$settings[ $object ] ) ) {
			// Late init
			foreach ( self::$settings[ $object ] as $id => $setting ) {
				if ( is_array( $setting ) ) {
					self::$settings[ $object ][ $id ] = new WP_Fields_API_Setting( $object, $id, $setting );
				}
			}

			$settings = self::$settings[ $object ];
		}

		return $settings;

	}

	/**
	 * Add a field setting.
	 *
	 * @access public
	 *
	 * @param string $object                    Object type.
	 * @param WP_Fields_API_Setting|string $id  Fields API Setting object, or ID.
	 * @param array $args                       Setting arguments; passed to WP_Fields_API_Setting
	 *                                          constructor.
	 */
	public function add_setting( $object, $id, $args = array() ) {

		if ( is_a( $id, 'WP_Fields_API_Setting' ) ) {
			$setting = $id;

			$id = $setting->id;
		} else {
			// Save for late init
			$setting = $args;
		}

		self::$settings[ $object ] = self::$settings[ $object ] || array();

		self::$settings[ $object ][ $id ] = $setting;

	}

	/**
	 * Retrieve a field setting.
	 *
	 * @access public
	 *
	 * @param string $object Object type.
	 * @param string $id     Field Setting ID.
	 *
	 * @return WP_Fields_API_Setting|null
	 */
	public function get_setting( $object, $id ) {

		$setting = null;

		if ( isset( self::$settings[ $object ][ $id ] ) ) {
			// Late init
			if ( is_array( self::$settings[ $object ][ $id ] ) ) {
				self::$settings[ $object ][ $id ] = new WP_Fields_API_Setting( $object, $id, self::$settings[ $object ][ $id ] );
			}

			$setting = self::$settings[ $object ][ $id ];
		}

		return $setting;

	}

	/**
	 * Remove a field setting.
	 *
	 * @access public
	 *
	 * @param string $object Object type.
	 * @param string $id     Field Setting ID.
	 */
	public function remove_setting( $object, $id ) {

		if ( isset( self::$settings[ $object ][ $id ] ) ) {
			unset( self::$settings[ $object ][ $id ] );
		}

	}

	/**
	 * Get the registered containers.
	 *
	 * @access public
	 *
	 * @param string $object Object type.
	 *
	 * @return array<WP_Fields_API_Panel|WP_Fields_API_Section>
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
	 * Get the registered panels.
	 *
	 * @access public
	 *
	 * @param string $object Object type.
	 *
	 * @return array<WP_Fields_API_Panel>
	 */
	public function get_panels( $object = null ) {

		$panels = array();

		if ( null === $object ) {
			// Late init
			foreach ( self::$panels as $object => $controls ) {
				$this->get_panels( $object );
			}

			$panels = self::$panels;
		} elseif ( isset( self::$panels[ $object ] ) ) {
			// Late init
			foreach ( self::$panels[ $object ] as $id => $panel ) {
				if ( is_array( $panel ) ) {
					self::$panels[ $object ][ $id ] = new WP_Fields_API_Panel( $object, $id, $panel );
				}
			}

			$panels = self::$panels[ $object ];
		}

		return $panels;

	}

	/**
	 * Add a field panel.
	 *
	 * @access public
	 *
	 * @param string $object                  Object type.
	 * @param WP_Fields_API_Panel|string $id  Field Panel object, or Panel ID.
	 * @param array $args                     Optional. Panel arguments. Default empty array.
	 */
	public function add_panel( $object, $id, $args = array() ) {

		if ( is_a( $id, 'WP_Fields_API_Panel' ) ) {
			$panel = $id;

			$id = $panel->id;
		} else {
			// Save for late init
			$panel = $args;
		}

		self::$panels[ $id ] = $panel;

	}

	/**
	 * Retrieve a field panel.
	 *
	 * @access public
	 *
	 * @param string $object       Object type.
	 * @param string $id           Panel ID to get.
	 *
	 * @return WP_Fields_API_Panel Requested panel instance.
	 */
	public function get_panel( $object, $id ) {

		$panel = null;

		if ( isset( self::$panels[ $object ][ $id ] ) ) {
			// Late init
			if ( is_array( self::$panels[ $object ][ $id ] ) ) {
				self::$panels[ $object ][ $id ] = new WP_Fields_API_Panel( $object, $id, self::$settings[ $object ][ $id ] );
			}

			$panel = self::$panels[ $object ][ $id ];
		}

		return $panel;

	}

	/**
	 * Remove a field panel.
	 *
	 * @access public
	 *
	 * @param string $object Object type.
	 * @param string $id     Panel ID to remove.
	 */
	public function remove_panel( $object, $id ) {

		if ( isset( self::$panels[ $object ][ $id ] ) ) {
			unset( self::$panels[ $object ][ $id ] );
		}

	}

	/**
	 * Get the registered sections.
	 *
	 * @access public
	 *
	 * @param string $object Object type.
	 * @param string $panel  Panel ID.
	 *
	 * @return array<WP_Fields_API_Section>
	 */
	public function get_sections( $object = null, $panel = null ) {

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

			// Get only sections for a specific panel
			if ( $panel ) {
				$panel_sections = array();

				foreach ( $sections as $id => $section ) {
					if ( $panel == $section->panel ) {
						$panel_sections[ $id ] = $section;
					}
				}

				$sections = $panel_sections;
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
	 * @param string $panel  Panel ID.
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
	 * @param {WP_Fields_API_Panel|WP_Fields_API_Section|WP_Fields_API_Control} $a Object A.
	 * @param {WP_Fields_API_Panel|WP_Fields_API_Section|WP_Fields_API_Control} $b Object B.
	 *
	 * @return int
	 */
	protected final function _cmp_priority( $a, $b ) {

		if ( $a->priority === $b->priority ) {
			return $a->instance_number - $a->instance_number;
		}

		return $a->priority - $b->priority;

	}

	/**
	 * Prepare panels, sections, and controls for all objects.
	 *
	 * For each, check if required related components exist,
	 * whether the user has the necessary capabilities,
	 * and sort by priority.
	 *
	 * @access public
	 */
	public function prepare_controls() {

		// Get object types
		$objects = array_keys( self::$settings );

		// Prepare controls for all object types
		foreach ( $objects as $object ) {
			$this->prepare_object_controls( $object );
		}

	}

	/**
	 * Prepare object panels, sections, and controls.
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
			'panel'     => array(),
			'container' => array()
		);

		// Setup

		// Get controls
		$controls = $this->get_controls( $object );

		// Get sections
		$sections = $this->get_sections( $object );

		// Get panels
		$panels = $this->get_panels( $object );

		// Controls

		// Sort controls by priority
		uasort( $controls, array( $this, '_cmp_priority' ) );

		foreach ( $controls as $id => $control ) {
			// Check if section or panel exists
			if ( $control->section && ! isset( $sections[ $control->section ] ) ) {
				continue;
			} elseif ( $control->panel && ! isset( $panels[ $control->panel ] ) ) {
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
			} elseif ( $control->panel ) {
				// Add control to panel controls
				$panels[ $control->panel ]->controls[] = $control;
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

			if ( ! $section->panel ) {
				// Top-level section.

				// Add to prepared IDs
				$prepared_ids['section'][]   = $id;
				$prepared_ids['container'][] = $id;
			} elseif ( $section->panel && isset( $panels[ $section->panel ] ) ) {
				// This section belongs to a panel.
				$panels[ $section->panel ]->sections[ $id ] = $section;

				// Add to prepared IDs
				$prepared_ids['section'][]   = $id;
				$prepared_ids['container'][] = $id;
			}
		}

		// Panels

		// Sort panels by priority
		uasort( $panels, array( $this, '_cmp_priority' ) );

		foreach ( $panels as $id => $panel ) {
			// Check if panel has sections or can be seen by user
			if ( ! $panel->sections || ! $panel->check_capabilities() ) {
				continue;
			}

			// Sort panel sections by priority
			uasort( $panel->sections, array( $this, '_cmp_priority' ) );

			// Add to prepared IDs
			$prepared_ids['panel'][]     = $id;
			$prepared_ids['container'][] = $id;
		}

		// Merge panels and top-level sections together.
		$containers = array_merge( $panels, $sections );

		// Sort containers by priority
		uasort( $containers, array( $this, '_cmp_priority' ) );

		// Saving

		// Save controls
		self::$controls[ $object ] = $controls;

		// Save sections
		self::$sections[ $object ] = $sections;

		// Save panels
		self::$panels[ $object ] = $panels;

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
	 * @param string $type   Type including panel, section, or setting.
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

}