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
	 * Get all controls for sections in this container.
	 *
	 * @return WP_Fields_API_Control[]
	 */
	public function get_controls() {

		$sections = $this->get_sections();

		$form_controls = array();

		foreach ( $sections as $section ) {
			$form_controls = array_merge( $form_controls, $section->get_controls() );
		}

		return $form_controls;

	}

	/**
	 * Register forms, sections, controls, and fields
	 *
	 * @param string      $object_type
	 * @param string      $form_id
	 * @param null|string $object_subtype
	 * @param array       $args
	 *
	 * @return WP_Fields_API_Form
	 */
	public static function register( $object_type = null, $form_id = null, $object_subtype = null, $args = array() ) {

		/**
		 * @var $wp_fields WP_Fields_API
		 * @var $form      WP_Fields_API_Form
		 */
		global $wp_fields;

		// Set object_subtype if not overridden
		if ( ! isset( $args['object_subtype'] ) ) {
			$args['object_subtype'] = $object_subtype;
		}

		$class_name = get_called_class();

		// Setup form
		$form = new $class_name( $object_type, $form_id, $args );

		// Add form to Fields API
		$wp_fields->add_form( $form->object_type, $form, $form->object_subtype );

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
				'controls' => array(),
			);

			if ( 1 < $total_examples ) {
				$section_args['label'] .= ' ' . $x;
			}

			if ( in_array( $this->object_type, array( 'post', 'comment' ) ) ) {
				$section_args['type'] = 'meta-box';
			}

			// Add example for each control type
			$control_types = array(
				'text',
				'textarea',
				'checkbox',
				'multi-checkbox',
				'radio',
				'select',
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
				$control_id   = $this->id . '-example_my_' . $x . '_' . $control_type . '_field';
				$control_args = array(
					// Add a control to the field at the same time
					'type'        => $control_type,
					'label'       => sprintf( __( '%s Field' ), ucwords( str_replace( '-', ' ', $control_type ) ) ),
					'description' => 'Example field description',
				);

				if ( in_array( $control_type, $option_types ) ) {
					$control_args['choices'] = array(
						''         => 'N/A',
						'option-1' => 'Option 1',
						'option-2' => 'Option 2',
						'option-3' => 'Option 3',
						'option-4' => 'Option 4',
						'option-5' => 'Option 5',
					);

					if ( 'multi-checkbox' == $control_type ) {
						unset( $control_args['choices'][''] );
					}
				} elseif ( 'checkbox' == $control_type ) {
					$control_args['checkbox_label'] = 'Example checkbox label';
				}

				$section_args['controls'][ $control_id ] = $control_args;
			}

			$wp_fields->add_section( $this->object_type, $section_id, $this->object_subtype, $section_args );
		}

	}

	/**
	 * Handle saving of fields
	 *
	 * @param int|null    $item_id     Item ID
	 * @param string|null $object_subtype Object subtype
	 *
	 * @return bool|array false if saving doesn't occur, true if there are no errors, array if there are error(s)
	 */
	public function save_fields( $item_id = null, $object_subtype = null ) {

		if ( ! empty( $item_id ) ) {
			$this->item_id = $item_id;
		}

		if ( ! empty( $object_subtype ) ) {
			$this->object_subtype = $object_subtype;
		}

		$form_nonce = $this->object_type . '_' . $this->id . '_' . $this->item_id;

		if ( ! empty( $_REQUEST['wp_fields_api_fields_save'] ) && false !== wp_verify_nonce( $_REQUEST['wp_fields_api_fields_save'], $form_nonce ) ) {
			$values = array();

			$sections = $this->get_sections();

			$errors = array();

			foreach ( $sections as $section ) {
				if ( ! $section->check_capabilities() ) {
					continue;
				}

				$controls = $section->get_controls();

				// Get values, handle validation first
				foreach ( $controls as $control ) {
					if ( ! $control->check_capabilities() ) {
						continue;
					}

					if ( $control->internal && 'readonly' !== $control->type ) {
						continue;
					}

					$field = $control->get_field();

					if ( ! $field ) {
						continue;
					}

					// Pass $object_subtype into control
					$control->object_subtype = $this->object_subtype;

					// Pass $object_subtype into field
					$field->object_subtype = $this->object_subtype;

					// Get value from $_POST
					$value = null;

					$input_attrs = $control->get_input_attrs();
					$input_name  = $input_attrs['name'];

					if ( ! empty( $_POST[ $input_name ] ) ) {
						$value = $_POST[ $input_name ];
					}

					// Sanitize
					$value = $field->sanitize( $value );

					if ( is_wp_error( $value ) ) {
						$control->error = $value;
						$errors[ $field->id ] =  $value;
					} else {
						$values[ $field->id ] = $value;
					}
				}

				/**
				 * Allow user to short circuit all saving if an error occurs
				 */
				if ( apply_filters( 'wp_fields_api_skip_save_on_error', false, $this, $errors, $values ) ) {
					return $errors;
				}

				// Save values once validation completes
				foreach ( $controls as $control ) {
					if ( $control->internal && 'readonly' !== $control->type ) {
						continue;
					}

					$field = $control->get_field();

					if ( ! $field || ! isset( $values[ $field->id ] ) ) {
						continue;
					}

					$value = $values[ $field->id ];

					// Save value
					$success = $field->save( $value, $item_id );

					if ( is_wp_error( $success ) ) {
						$errors[ $field->id ] = $success;
					}
				}
			}

			if ( ! empty( $errors ) ) {
				return $errors;
			}

			return true;
		}

		return false;

	}

	/**
	 * Render form for implementation
	 */
	protected function render() {

		/**
		 * @var $wp_fields WP_Fields_API
		 */
		global $wp_fields;

		$form_nonce = $this->object_type . '_' . $this->id . '_' . $this->item_id;

		wp_nonce_field( $form_nonce, 'wp_fields_api_fields_save' );

		$sections = $this->get_sections();

		if ( ! empty( $sections ) ) {
			?>
			<div class="fields-form-<?php echo esc_attr( $this->object_type ); ?> form-<?php echo esc_attr( $this->id ); ?>-wrap fields-api-form">
				<?php
				foreach ( $sections as $section ) {
					// Pass $object_subtype into section
					$section->object_subtype = $this->object_subtype;

					$section->maybe_render();
				}
				?>
			</div>
			<?php

			// Render control templates
			add_action( 'admin_print_footer_scripts', array( $wp_fields, 'render_control_templates' ), 5 );
		}

	}

}
