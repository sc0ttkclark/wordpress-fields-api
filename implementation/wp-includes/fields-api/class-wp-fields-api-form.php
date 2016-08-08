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
	 * Create new form
	 * 
	 * @access public
	 * @param string $object_type Object type
	 * @param string $id          ID for this component
	 * @param array  $args        Additional container args to set
	 */
	public function __construct( $object_type, $id, $args = array() ) {
		$this->object_type = $object_type;

		parent::__construct( $id, $args );
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
