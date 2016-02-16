<?php
/**
 * WordPress Fields API Form class
 *
 * @package    WordPress
 * @subpackage Fields_API
 */

/**
 * Fields API Form class.
 *
 * A UI container for sections, managed by WP_Fields_API.
 *
 * @see WP_Fields_API
 */
class WP_Fields_API_Form extends WP_Fields_API_Container {

	/**
	 * {@inheritdoc}
	 */
	protected $container_type = 'form';

	/**
	 * Default section type to use for new sections added to form
	 *
	 * @access public
	 * @var string
	 */
	public $default_section_type = 'table';

	/**
	 * Register forms, sections, controls, and fields
	 *
	 * @param string      $object_type
	 * @param string      $form_id
	 * @param null|string $object_name
	 * @param array       $args
	 *
	 * @return WP_Fields_API_Form
	 */
	public static function register( $object_type = null, $form_id = null, $object_name = null, $args = array() ) {

		/**
		 * @var $wp_fields WP_Fields_API
		 * @var $form      WP_Fields_API_Form
		 */
		global $wp_fields;

		// Set object_name if not overridden
		if ( ! isset( $args['object_name'] ) ) {
			$args['object_name'] = $object_name;
		}

		$class_name = get_called_class();

		// Setup form
		$form = new $class_name( $object_type, $form_id, $args );

		// Add form to Fields API
		$wp_fields->add_form( $form->object_type, $form, $form->object_name );

		// Register control types for this form
		$form->register_control_types( $wp_fields );

		// Register fields for this form
		$form->register_fields( $wp_fields );

		return $form;

	}

	/**
	 * Encapsulated registering of custom control types
	 *
	 * @param WP_Fields_API $wp_fields
	 */
	public function register_control_types( $wp_fields ) {

		// None by default

		// @todo Remove this when done testing

		if ( ! defined( 'WP_FIELDS_API_EXAMPLES' ) || ! WP_FIELDS_API_EXAMPLES ) {
			return;
		}

		// Include control type(s)
		require_once( WP_FIELDS_API_DIR . 'implementation/wp-includes/fields-api/control-types/custom/class-wp-fields-api-repeater-control.php' );

		// Register control type(s)
		$wp_fields->register_control_type( 'repeater', 'WP_Fields_API_Repeater_Control' );

	}

	/**
	 * Encapsulated registering of sections, controls, and fields for a form
	 *
	 * @param WP_Fields_API $wp_fields
	 */
	public function register_fields( $wp_fields ) {

		// @todo Remove this when done testing

		if ( ! defined( 'WP_FIELDS_API_EXAMPLES' ) || ! WP_FIELDS_API_EXAMPLES ) {
			return;
		}

		//////////////
		// Examples //
		//////////////

		$total_examples = 1;

		if ( defined( 'WP_FIELDS_API_TESTING' ) && WP_FIELDS_API_TESTING && ! empty( $_GET['fields-api-memory-test'] ) ) {
			$total_examples = 25;

			if ( 1 < $_GET['fields-api-memory-test'] ) {
				$total_examples = absint( $_GET['fields-api-memory-test'] );
			}
		}

		for ( $x = 1; $x <= $total_examples; $x ++ ) {
			// Section
			$section_id   = $this->id . '-example-my-fields-' . $x;
			$section_args = array(
				'label'    => __( 'Fields API Example - My Fields', 'fields-api' ),
				'form'     => $this->id,
			);

			if ( 1 < $total_examples ) {
				$section_args['label'] .= ' ' . $x;
			}

			if ( in_array( $this->object_type, array( 'post', 'comment' ) ) ) {
				$section_args['type'] = 'meta-box';
			}

			$wp_fields->add_section( $this->object_type, $section_id, $this->object_name, $section_args );

			// Add example for each control type
			$control_types = array(
				'repeater',
				'text',
				'textarea',
				'checkbox',
				'multi-checkbox',
				'radio',
				'select',
				'dropdown-pages',
				'dropdown-terms',
				'color',
				'media',
				'media-file',
			);

			$option_types = array(
				'multi-checkbox',
				'radio',
				'select',
			);

			foreach ( $control_types as $control_type ) {
				$field_id = 'example_my_' . $x . '_' . $control_type . '_field';
				$label    = sprintf( __( '%s Field' ), ucwords( str_replace( '-', ' ', $control_type ) ) );

				$field_args = array(
					// Add a control to the field at the same time
					'control' => array(
						'type'        => $control_type,
						'id'          => $this->id . '-' . $field_id,
						'section'     => $section_id,
						'label'       => $label,
						'description' => 'Example field description',
					),
				);

				if ( in_array( $control_type, $option_types ) ) {
					$field_args['control']['choices'] = array(
						''         => 'N/A',
						'option-1' => 'Option 1',
						'option-2' => 'Option 2',
						'option-3' => 'Option 3',
						'option-4' => 'Option 4',
						'option-5' => 'Option 5',
					);

					if ( 'multi-checkbox' == $control_type ) {
						unset( $field_args['control']['choices'][''] );
					}
				} elseif ( 'checkbox' == $control_type ) {
					$field_args['control']['checkbox_label'] = 'Example checkbox label';
				}

				if ( 'dropdown-terms' == $control_type ) {
					$field_args['control']['taxonomy'] = 'category';
				}

				$wp_fields->add_field( $this->object_type, $field_id, $this->object_name, $field_args );
			}
		}

	}

	/**
	 * Handle saving of fields
	 *
	 * @param int|null    $item_id     Item ID
	 * @param string|null $object_name Object name
	 *
	 * @return int|WP_Error|null New item ID, WP_Error if there was a problem, null if no $item_id used
	 */
	public function save_fields( $item_id = null, $object_name = null ) {

		if ( ! empty( $item_id ) ) {
			$this->item_id = $item_id;
		}

		if ( ! empty( $object_name ) ) {
			$this->object_name = $object_name;
		}

		$form_nonce = $this->object_type . '_' . $this->id . '_' . $this->item_id;

		if ( ! empty( $_REQUEST['wp_fields_api_fields_save'] ) && false !== wp_verify_nonce( $_REQUEST['wp_fields_api_fields_save'], $form_nonce ) ) {
			/**
			 * @var $wp_fields WP_Fields_API
			 */
			global $wp_fields;

			$controls = $wp_fields->get_controls( $this->object_type, $this->object_name );

			$values = array();

			foreach ( $controls as $control ) {
				if ( empty( $control->field ) || $control->internal ) {
					continue;
				}

				// Pass $object_name into control
				$control->object_name = $this->object_name;

				$field = $control->field;

				// Pass $object_name into field
				$field->object_name = $this->object_name;

				// Get value from $_POST
				$value = null;

				$input_name = $control->id;

				if ( ! empty( $control->input_name ) ) {
					$input_name = $control->input_name;
				}

				if ( ! empty( $_POST[ $input_name ] ) ) {
					$value = $_POST[ $input_name ];
				}

				// Sanitize
				$value = $field->sanitize( $value );

				if ( is_wp_error( $value ) ) {
					return $value;
				}

				$values[ $field->id ] = $value;
			}

			foreach ( $controls as $control ) {
				if ( empty( $control->field ) || $control->internal ) {
					continue;
				}

				$field = $control->field;

				$value = $values[ $field->id ];

				// Save value
				$success = $field->save( $value );

				if ( is_wp_error( $success ) ) {
					return $success;
				}
			}
		}

		return $this->item_id;

	}

	/**
	 * Render form for implementation
	 */
	protected function render() {

		$form_nonce = $this->object_type . '_' . $this->id . '_' . $this->item_id;

		wp_nonce_field( $form_nonce, 'wp_fields_api_fields_save' );

		$sections = $this->get_sections();

		if ( ! empty( $sections ) ) {
			?>
				<div class="fields-form-<?php echo esc_attr( $this->object_type ); ?> form-<?php echo esc_attr( $this->id ); ?>-wrap fields-api-form">
					<?php
						foreach ( $sections as $section ) {
							// Pass $object_name into section
							$section->object_name = $this->object_name;

							$section->maybe_render();
						}
					?>
				</div>
			<?php

			$this->enqueue_footer_scripts();
		}

	}

	/**
	 * Add action to print footer scripts for form
	 */
	public function enqueue_footer_scripts() {

		/**
		 * @var $wp_fields WP_Fields_API
		 */
		global $wp_fields;

		if ( ! has_action( 'admin_print_footer_scripts', array( $wp_fields, 'render_control_templates' ) ) ) {
			add_action( 'admin_print_footer_scripts', array( $wp_fields, 'render_control_templates' ), 5 );
		}

	}

}
