<?php

/**
 * This is a manager for the Fields API, based on the WP_Customize_Manager.
 *
 * @package    WordPress
 * @subpackage Fields_API
 */
final class WP_Fields_API {

	/**
	 * Instantiated components
	 *
	 * @access protected
	 * @var array $components {
	 *      @type WP_Fields_API_Form[]    $form
	 *      @type WP_Fields_API_Section[] $section
	 *      @type WP_Fields_API_Control[] $control
	 *      @type WP_Fields_API_Field[]   $field
	 * }
	 */
	public $components = array(
		'form'    => array(),
		'section' => array(),
		'control' => array(),
		'field'   => array(),
	);

	/**
	 * Form component types that may be rendered.
	 *
	 * @access protected
	 * @var array
	 */
	protected $registered_types = array();

	/**
	 * Include the library and bootstrap.
	 *
	 * @constructor
	 * @access public
	 */
	private function __construct() {

		$fields_api_dir = WP_FIELDS_API_DIR . 'implementation/wp-includes/fields-api/';

		// Include API classes
		require_once( $fields_api_dir . 'class-wp-fields-api-component.php' );
		require_once( $fields_api_dir . 'class-wp-fields-api-container.php' );
		require_once( $fields_api_dir . 'class-wp-fields-api-form.php' );
		require_once( $fields_api_dir . 'class-wp-fields-api-section.php' );
		require_once( $fields_api_dir . 'class-wp-fields-api-field.php' );
		require_once( $fields_api_dir . 'class-wp-fields-api-control.php' );
		require_once( $fields_api_dir . 'class-wp-fields-api-datasource.php' );

		// Include section types
		require_once( $fields_api_dir . 'section-types/class-wp-fields-api-table-section.php' );
		require_once( $fields_api_dir . 'section-types/class-wp-fields-api-meta-box-section.php' );
		require_once( $fields_api_dir . 'section-types/class-wp-fields-api-meta-box-table-section.php' );

		// Include control types
		require_once( $fields_api_dir . 'control-types/class-wp-fields-api-readonly-control.php' );
		require_once( $fields_api_dir . 'control-types/class-wp-fields-api-textarea-control.php' );
		require_once( $fields_api_dir . 'control-types/class-wp-fields-api-wysiwyg-control.php' );
		require_once( $fields_api_dir . 'control-types/class-wp-fields-api-checkbox-control.php' );
		require_once( $fields_api_dir . 'control-types/class-wp-fields-api-multi-checkbox-control.php' );
		require_once( $fields_api_dir . 'control-types/class-wp-fields-api-radio-control.php' );
		//require_once( $fields_api_dir . 'control-types/class-wp-fields-api-radio-multi-label-control.php' ); // @todo Revisit
		require_once( $fields_api_dir . 'control-types/class-wp-fields-api-select-control.php' );
		require_once( $fields_api_dir . 'control-types/class-wp-fields-api-color-control.php' );
		require_once( $fields_api_dir . 'control-types/class-wp-fields-api-media-control.php' );
		require_once( $fields_api_dir . 'control-types/class-wp-fields-api-media-file-control.php' );
		require_once( $fields_api_dir . 'control-types/class-wp-fields-api-number-inline-description.php' );

		// Include datasources
		require_once( $fields_api_dir . 'datasources/class-wp-fields-api-admin-color-scheme-datasource.php' );
		require_once( $fields_api_dir . 'datasources/class-wp-fields-api-comment-datasource.php' );
		require_once( $fields_api_dir . 'datasources/class-wp-fields-api-post-datasource.php' );
		require_once( $fields_api_dir . 'datasources/class-wp-fields-api-page-datasource.php' );
		require_once( $fields_api_dir . 'datasources/class-wp-fields-api-term-datasource.php' );
		require_once( $fields_api_dir . 'datasources/class-wp-fields-api-user-datasource.php' );

		// Register our wp_loaded() first before WP_Customize_Manage::wp_loaded()
		add_action( 'wp_loaded', array( $this, 'wp_loaded' ), 9 );

	}

	/**
	 * Setup instance for singleton
	 *
	 * @return WP_Fields_API
	 */
	public static function get_instance() {
		static $instance;

		if ( empty( $instance ) ) {
			$instance = new self;
		}

		return $instance;

	}

	/**
	 * Trigger the `fields_register` action hook on `wp_loaded`.
	 *
	 * Fields, Sections, Forms, and Controls should be registered on this hook.
	 *
	 * @access public
	 */
	public function wp_loaded() {

		// Register default controls
		$this->register_defaults();

		/**
		 * Fires when the Fields API is available, and components can be registered.
		 *
		 * @param WP_Fields_API $this The Fields manager object.
		 */
		do_action( 'fields_register', $this );

	}

	/**
	 * Get the registered forms.
	 *
	 * @access public
	 *
	 * @param string $object_type    Object type.
	 * @param string $object_subtype Object subtype (for post types and taxonomies).
	 *
	 * @return WP_Fields_API_Form[]
	 */
	public function get_forms( $object_type = null, $object_subtype = null ) {

		/**
		 * @var $forms WP_Fields_API_Form[]
		 */
		$forms = $this->components['form'];

		if ( $object_type !== null || $object_subtype !== null ) {
			foreach ( $forms as $key => $form ) {
				if ( $object_type !== null && $form->get_object_type() !== $object_type ) {
					unset( $forms[ $key ] );
				} elseif ( $object_subtype !== null && $form->object_subtype !== $object_subtype ) {
					unset( $forms[ $key ] );
				}
			}
		}

		return $forms;

	}

	/**
	 * Get a form
	 *
	 * @access public
	 *
	 * @param string|WP_Fields_API_Form $id Unique form id
	 *
	 * @return WP_Fields_API_Form|false
	 */
	public function get_form( $id ) {

		$form = false;

		if ( is_a( $id, 'WP_Fields_API_Form' ) ) {
			$form = $id;
		} elseif ( ! empty( $this->components['form'][ $id ] ) ) {
			$form = $this->components['form'][ $id ];
		}

		return $form;

	}

	/**
	 * Create a form
	 *
	 * @access public
	 *
	 * @param string                   $object_type Object type.
	 * @param string                   $id          Unique form id.
	 * @param array|WP_Fields_API_Form $args        Additional form arguments.
	 *
	 * @return WP_Error|WP_Fields_API_Form
	 */
	public function add_form( $object_type, $id, $args = array() ) {

		if ( empty( $object_type ) ) {
			// @todo Need WP_Error code
			return new WP_Error( 'fields-api-object-type-required', __( 'Object type is required.', 'fields-api' ) );
		}

		if ( empty( $id ) ) {
			// @todo Need WP_Error code
			return new WP_Error( 'fields-api-id-required', __( 'ID is required.', 'fields-api' ) );
		}

		$form = null;

		if ( is_a( $args, 'WP_Fields_API_Form' ) ) {
			$id   = $args->id;
			$args = array();

			$form->object_type = $object_type;
		} elseif ( ! is_array( $args ) ) {
			// @todo Need WP_Error code
			return new WP_Error( 'fields-api-unexpected-form-arguments', __( 'Unexpected form arguments.', 'fields-api' ) );
		}

		if ( ! empty( $this->components['form'][ $id ] ) ) {
			// @todo Need WP_Error code
			return new WP_Error( 'fields-api-id-exists', __( 'ID already exists.', 'fields-api' ) );
		}

		if ( ! $form ) {
			$class = $this->registered_types['form']['default'];

			if ( ! empty( $args['type'] ) ) {
				$class = $this->registered_types['form'][ $args['type'] ];
			}

			$args['object_type'] = $object_type;

			$form = new $class( $id, $args );
		}

		$this->components['form'][ $id ] = $form;

		return $this->components['form'][ $id ];

	}

	/**
	 * Remove a form.
	 *
	 * @access public
	 *
	 * @param string|WP_Fields_API_Form $form Form ID or object to remove
	 */
	public function remove_form( $form ) {

		if ( ! is_a( $form, 'WP_Fields_API_Form' ) ) {
			$form = $this->get_form( $form );
		}

		if ( $form && ! empty( $this->components['form'][ $form->id ] ) ) {
			unset( $this->components['form'][ $form->id ] );

			unset( $form );
		}

	}

	/**
	 * Remove all forms
	 *
	 * @access public
	 *
	 * @param string $object_type    Object type
	 * @param string $object_subtype Object subtype (for post types and taxonomies).
	 */
	public function remove_forms( $object_type = null, $object_subtype = null ) {
		if ( null === $object_type && null === $object_subtype ) {
			$this->components['form'] = array();
			return;
		}

		if ( null !== $object_type ) {
			/**
			 * @var $form WP_Fields_API_Form
			 */
			foreach ( $this->components['form'] as $id => $form ) {
				if ( $object_type === $form->object_type ) {
					if ( null === $object_subtype || $object_subtype === $form->object_subtype ) {
						unset( $this->components['form'][ $id ] );

						unset( $form );
					}
				}
			}
		}
	}

	/**
	 * Get a field from a form
	 *
	 * @param string|WP_Fields_API_Field $id Field ID to get.
	 *
	 * @return WP_Fields_API_Field|bool Requested section instance.
	 */
	public function get_field( $object_type, $id ) {

		$field = false;

		if ( is_a( $id, 'WP_Fields_API_Field' ) ) {
			$field = $id;
		} elseif ( ! empty( $this->components['field'][ $object_type ][ $id ] ) ) {
			$field = $this->components['field'][ $object_type ][ $id ];
		}

		return $field;

	}

	/**
	 * Add a field.
	 *
	 * @access public
	 *
	 * @param string $id   Fields API Field object, or ID.
	 * @param array  $args Field arguments; passed to WP_Fields_API_Field constructor.
	 *
	 * @return WP_Fields_API_Field|WP_Error
	 */
	public function add_field( $object_type, $id, $args = array() ) {

		if ( empty( $id ) ) {
			// @todo Need WP_Error code
			return new WP_Error( 'fields-api-id-required', __( 'ID is required.', 'fields-api' ) );
		}

		if ( ! empty( $this->components['field'][ $object_type ][ $id ] ) ) {
			// @todo Need WP_Error code
			return new WP_Error( 'fields-api-id-exists', __( 'ID already exists.', 'fields-api' ) );
		}

		$class = $this->registered_types['field']['default'];

		if ( ! empty( $args['type'] ) ) {
			$class = $this->registered_types['field'][ $args['type'] ];
		}

		$this->components['field'][ $object_type ][ $id ] = new $class( $id, $args );

		return $this->components['field'][ $object_type ][ $id ];
	}

	/**
	 * Remove a field
	 *
	 * @access public
	 *
	 * @param string|WP_Fields_API_Field $field Field ID or object to remove
	 */
	public function remove_field( $object_type, $field ) {
		if ( ! is_a( $field, 'WP_Fields_API_Field' ) ) {
			$field = $this->get_field( $object_type, $field );
		}

		if ( $field ) {
			if ( ! empty( $field->parent ) ) {
				/**
				 * @var $parent WP_Fields_API_Control
				 */
				$parent = $field->parent;

				$parent->remove_field();
			}

			if ( ! empty( $this->components['field'][ $object_type ][ $field->id ] ) ) {
				unset( $this->components['field'][ $object_type ][ $field->id ] );
			}

			unset( $field );
		}
	}

	/**
	 * Get all registered types
	 *
	 * @access public
	 *
	 * @return array
	 */
	public function get_registered_types() {
		return $this->registered_types;
	}

	/**
	 * Get all registered types
	 *
	 * @access public
	 *
	 * @return string|null
	 */
	public function get_registered_type( $component_type, $type ) {

		$class = null;

		if ( ! empty( $this->registered_types[ $component_type ] ) ) {
			if ( ! empty( $this->registered_types[ $component_type ][ $type ] ) ) {
				$class = $this->registered_types[ $component_type ][ $type ];
			} elseif ( ! empty( $this->registered_types[ $component_type ]['default'] ) ) {
				$class = $this->registered_types[ $component_type ]['default'];
			}
		}

		return $class;

	}

	/**
	 * Register a form type.
	 *
	 * @access public
	 *
	 * @see    WP_Fields_API_Form
	 *
	 * @param string $type       Form type ID.
	 * @param string $form_class Name of a custom form which is a subclass of WP_Fields_API_Form.
	 */
	public function register_form_type( $type, $form_class = 'WP_Fields_API_Form' ) {
		$this->registered_types['form'][ $type ] = $form_class;
	}

	/**
	 * Register a section type for use later
	 *
	 * @access public
	 *
	 * @param string $type          Type slug
	 * @param string $control_class Name of a custom esction which is a subclass of WP_Fields_API_Section.
	 */
	public function register_section_type( $type, $section_class = 'WP_Fields_API_Section' ) {
		$this->registered_types['section'][ $type ] = $section_class;
	}

	/**
	 * Register a control type for use later
	 *
	 * @access public
	 *
	 * @param string $type          Type slug
	 * @param string $control_class Name of a custom form which is a subclass of WP_Fields_API_Control.
	 */
	public function register_control_type( $type, $control_class = 'WP_Fields_API_Control' ) {
		$this->registered_types['control'][ $type ] = $control_class;
	}

	/**
	 * Register a field type.
	 *
	 * @access public
	 *
	 * @see    WP_Fields_API_Field
	 *
	 * @param string $type        Field type ID.
	 * @param string $field_class Name of a custom field type which is a subclass of WP_Fields_API_Field.
	 */
	public function register_field_type( $type, $field_class = 'WP_Fields_API_Field' ) {
		$this->registered_types['field'][ $type ] = $field_class;
	}

	/**
	 * Register a datasource type for use later
	 *
	 * @access public
	 *
	 * @param string $type          Type slug
	 * @param string $control_class Name of a custom datasource which is a subclass of WP_Fields_API_Datasource.
	 */
	public function register_datasource_type( $type, $datasource_class = 'WP_Fields_API_Datasource' ) {
		$this->registered_types['datasource'][ $type ] = $datasource_class;
	}

	/**
	 * Register some default form and control types.
	 *
	 * @access public
	 */
	public function register_defaults() {
		/* Defaults */
		$this->register_form_type( 'default', 'WP_Fields_API_Form' );
		$this->register_section_type( 'default', 'WP_Fields_API_Section' );
		$this->register_control_type( 'default', 'WP_Fields_API_Control' );
		$this->register_field_type( 'default', 'WP_Fields_API_Field' );
		$this->register_datasource_type( 'default', 'WP_Fields_API_Datasource' );

		/* Section Types */
		$this->register_section_type( 'meta-box', 'WP_Fields_API_Meta_Box_Section' );
		$this->register_section_type( 'meta-box-table', 'WP_Fields_API_Meta_Box_Table_Section' );
		$this->register_section_type( 'table', 'WP_Fields_API_Table_Section' );

		/* Control Types */
		$this->register_control_type( 'text', 'WP_Fields_API_Control' );
		$this->register_control_type( 'number', 'WP_Fields_API_Control' );
		$this->register_control_type( 'email', 'WP_Fields_API_Control' );
		$this->register_control_type( 'password', 'WP_Fields_API_Control' );
		$this->register_control_type( 'hidden', 'WP_Fields_API_Control' );
		$this->register_control_type( 'readonly', 'WP_Fields_API_Readonly_Control' );
		$this->register_control_type( 'textarea', 'WP_Fields_API_Textarea_Control' );
		$this->register_control_type( 'wysiwyg', 'WP_Fields_API_WYSIWYG_Control' );
		$this->register_control_type( 'checkbox', 'WP_Fields_API_Checkbox_Control' );
		$this->register_control_type( 'multi-checkbox', 'WP_Fields_API_Multi_Checkbox_Control' );
		$this->register_control_type( 'radio', 'WP_Fields_API_Radio_Control' );
		//$this->register_control_type( 'radio-multi-label', 'WP_Fields_API_Radio_Multi_Label_Control' ); // @todo Revisit
		$this->register_control_type( 'select', 'WP_Fields_API_Select_Control' );
		$this->register_control_type( 'color', 'WP_Fields_API_Color_Control' );
		$this->register_control_type( 'media', 'WP_Fields_API_Media_Control' );
		$this->register_control_type( 'media-file', 'WP_Fields_API_Media_File_Control' );
		$this->register_control_type( 'number-inline-desc', 'WP_Fields_API_Number_Inline_Description_Control' ); // @todo Revisit

		/* Datasources */
		$this->register_datasource_type( 'post-format', 'WP_Fields_API_Datasource' );
		$this->register_datasource_type( 'post-type', 'WP_Fields_API_Datasource' );
		$this->register_datasource_type( 'post-status', 'WP_Fields_API_Datasource' );
		$this->register_datasource_type( 'page-status', 'WP_Fields_API_Datasource' );
		$this->register_datasource_type( 'user-role', 'WP_Fields_API_Datasource' );
		$this->register_datasource_type( 'admin-color-scheme', 'WP_Fields_API_Admin_Color_Scheme_Datasource' );
		$this->register_datasource_type( 'comment', 'WP_Fields_API_Comment_Datasource' );
		$this->register_datasource_type( 'post', 'WP_Fields_API_Post_Datasource' );
		$this->register_datasource_type( 'page', 'WP_Fields_API_Page_Datasource' );
		$this->register_datasource_type( 'term', 'WP_Fields_API_Term_Datasource' );
		$this->register_datasource_type( 'user', 'WP_Fields_API_User_Datasource' );

		/**
		 * Fires once WordPress has loaded, allowing control types to be registered.
		 *
		 * @param WP_Fields_API $this WP_Fields_API instance.
		 */
		do_action( 'fields_register_controls', $this );

	}

	/**
	 * Register meta integration for register_meta and REST API
	 *
	 * @param string                    $object_type    Object type
	 * @param string                    $id             Field ID
	 * @param array|WP_Fields_API_Field $field          Field object or options array
	 * @param string|null               $object_subtype Object subtype
	 */
	public function register_meta_integration( $object_type, $id, $field, $object_subtype = null ) {

		// Meta types call register_meta() and register_rest_field() for their fields
		if ( in_array( $object_type, array(
				'post',
				'term',
				'user',
				'comment'
			) ) && ! $this->get_field_arg( $field, 'internal' )
		) {
			// Set callbacks
			$sanitize_callback = array( $this, 'register_meta_sanitize_callback' );
			$auth_callback     = $this->get_field_arg( $field, 'meta_auth_callback' );

			register_meta( $object_type, $id, $sanitize_callback, $auth_callback );

			if ( function_exists( 'register_rest_field' ) && $this->get_field_arg( $field, 'show_in_rest' ) ) {
				$rest_field_args = array(
					'get_callback'    => $this->get_field_arg( $field, 'rest_get_callback' ),
					'update_callback' => $this->get_field_arg( $field, 'rest_update_callback' ),
					'schema'          => $this->get_field_arg( $field, 'rest_schema_callback' ),
					'type'            => $this->get_field_arg( $field, 'rest_field_type' ),
					'description'     => $this->get_field_arg( $field, 'rest_field_description' ),
				);

				register_rest_field( $object_type, $id, $rest_field_args );
			}
		}

	}

	/**
	 * Hook into register_meta() sanitize callback and call field
	 *
	 * @param mixed  $meta_value Meta value to sanitize.
	 * @param string $meta_key   Meta key.
	 * @param string $meta_type  Meta type.
	 *
	 * @return mixed
	 */
	public function register_meta_sanitize_callback( $meta_value, $meta_key, $meta_type ) {

		// @todo Fix
		$field = $this->get_field( $meta_type, $meta_key );

		if ( $field ) {
			$meta_value = $field->sanitize( $meta_value );
		}

		return $meta_value;

	}

	/**
	 * Get argument from field array or object
	 *
	 * @param array|object $field
	 * @param string       $arg
	 *
	 * @return null|mixed
	 */
	public function get_field_arg( $field, $arg ) {

		$value = null;

		if ( is_array( $field ) && isset( $field[ $arg ] ) ) {
			$value = $field[ $arg ];
		} elseif ( is_object( $field ) && isset( $field->{$arg} ) ) {
			$value = $field->{$arg};
		}

		return $value;

	}

}