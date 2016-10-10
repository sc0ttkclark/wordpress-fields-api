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
	 * Object type.
	 *
	 * @access public
	 * @var string
	 */
	public $object_type;

	/**
	 * Default section type to use for new sections added to form
	 *
	 * @access public
	 * @var string
	 */
	public $default_section_type = 'table';

	/**
	 * Container children type
	 *
	 * @var string
	 */
	public $container_type = 'form';

	/**
	 * Container children type
	 *
	 * @var string
	 */
	public $child_container_type = 'section';

	/**
	 * {@inheritdoc}
	 */
	public function __construct( $id, $args = array() ) {

		$sections = array();

		if ( isset( $args['sections'] ) ) {
			if ( ! empty( $args['sections'] ) && is_array( $args['sections'] ) ) {
				$sections = $args['sections'];
			}

			unset( $args['sections'] );
		}

		parent::__construct( $id, $args );

		foreach ( $sections as $section ) {
			$this->add_section( $section );
		}

	}

	/**
	 * Add a section
	 *
	 * @param array|WP_Fields_API_Section $args Section arguments
	 *
	 * @return WP_Error|WP_Fields_API_Section
	 */
	public function add_section( $args = array() ) {

		/**
		 * @var $wp_fields WP_Fields_API
		 */
		global $wp_fields;

		$section = null;
		$id      = null;

		if ( is_a( $args, 'WP_Fields_API_Section' ) ) {
			$section = $args;
			$id      = $section->id;
		} elseif ( ! empty( $args['id'] ) ) {
			$id = $args['id'];
		}

		return $this->add_child( $id, $section );

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

		$form_nonce = $this->object_type . '_' . $this->id;

		if ( ! empty( $_REQUEST['wp_fields_api_fields_save'] ) && false !== wp_verify_nonce( $_REQUEST['wp_fields_api_fields_save'], $form_nonce ) ) {
			$values = array();

			/**
			 * @var $sections WP_Fields_API_Section[]
			 */
			$sections = $this->get_children();

			$errors = array();

			foreach ( $sections as $section ) {
				if ( ! $section->check_capabilities() ) {
					continue;
				}

				/**
				 * @var $controls WP_Fields_API_Control[]
				 */
				$controls = $section->get_children();

				// Get values, handle validation first
				foreach ( $controls as $control ) {
					if ( ! $control->check_capabilities() ) {
						continue;
					}

					if ( $control->internal && 'readonly' !== $control->type ) {
						continue;
					}

					$field = $control->field;

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

					$field = $control->field;

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

		$form_nonce = $this->object_type . '_' . $this->id;

		wp_nonce_field( $form_nonce, 'wp_fields_api_fields_save' );

		$sections = $this->get_children();

		if ( ! empty( $sections ) ) {
			?>
			<div class="fields-form-<?php echo esc_attr( $this->get_object_type() ); ?> form-<?php echo esc_attr( $this->id ); ?>-wrap fields-api-form">
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