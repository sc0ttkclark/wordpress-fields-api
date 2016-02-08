<?php
/**
 * Fields API Control Class
 *
 * @package WordPress
 * @subpackage Fields_API
 *
 * @property array $choices Key/Values used by Multiple choice control types
 */
class WP_Fields_API_Control extends WP_Fields_API_Container {

	/**
	 * {@inheritdoc}
	 */
	protected $container_type = 'control';

	/**
	 * Override default Input name, defaults to $this->id.
	 *
	 * @access public
	 * @var string
	 */
	public $input_name;

	/**
	 * Item ID of current item passed to WP_Fields_API_Field for value()
	 *
	 * @access public
	 * @var int|string
	 */
	public $item_id = 0;

	/**
	 * @access public
	 * @var int
	 */
	public $priority = 10;

	/**
	 * @access public
	 * @var array
	 */
	public $input_attrs = array();

	/**
	 * @access public
	 * @var array
	 */
	public $wrap_attrs = array();

	/**
	 * @access public
	 * @var string
	 */
	public $type = 'text';

	/**
	 * Choices callback
	 *
	 * @access public
	 *
	 * @see WP_Fields_API_Control::setup_choices()
	 *
	 * @var callable Callback is called with one argument, the instance of WP_Fields_API_Control.
	 *               It returns an array of choices for the control to use.
	 */
	public $choices_callback = null;

	/**
	 * Secondary constructor; Any supplied $args override class property defaults.
	 *
	 * @param string $object_type   Object type.
	 * @param string $id            A specific ID of the control.
	 * @param array  $args          Control arguments.
	 */
	public function init( $object_type, $id, $args = array() ) {

		/**
		 * @var $wp_fields WP_Fields_API
		 */
		global $wp_fields;

		parent::init( $object_type, $id, $args );

		// Setup field
		if ( ! $this->field ) {
			$this->field = $id;
		}

		if ( $this->field ) {
			$field = $wp_fields->get_field( $this->object_type, $this->field, $this->object_name );

			if ( $field ) {
				$this->add_child( $field );
			}
		}

	}

	/**
	 * Get the form for this control's section.
	 *
	 * @return WP_Fields_API_Form|null
	 */
	public function get_form() {

		$section = $this->get_section();

		$form = null;

		if ( $section ) {
			$form = $section->get_form();
		}

		return $form;

	}

	/**
	 * Get the section for this control.
	 *
	 * @return WP_Fields_API_Section|null
	 */
	public function get_section() {

		return $this->get_parent();

	}

	/**
	 * Get associated field
	 *
	 * @return null|WP_Fields_API_Field
	 */
	public function get_field() {

		$fields = $this->get_children( 'field' );

		$field = null;

		if ( $fields ) {
			$field = current( $fields );
		}

		return $field;

	}

	/**
	 * Set the field on this control
	 *
	 * @param string                    $id
	 * @param array|WP_Fields_API_Field $args
	 *
	 * @return bool|WP_Error True on success or return error
	 */
	public function set_field( $id, $args = array() ) {

		/**
		 * @var $wp_fields WP_Fields_API
		 */
		global $wp_fields;

		// Remove field on section
		$this->remove_children( 'field' );

		// If no Field ID set, generate from current control
		if ( ! $id ) {
			$id = $this->id;
		}

		$added = $wp_fields->add_field( $this->object_type, $id, $this->object_name, $args );

		if ( $added && ! is_wp_error( $added ) ) {
			// Get and setup field
			$field = $wp_fields->get_field( $this->object_type, $id, $this->object_name );

			if ( $field && ! is_wp_error( $field ) ) {
				$this->add_child( $field, 'field' );

				return true;
			} else {
				return $field;
			}
		}

		return $added;

	}

	/**
	 * Get choice values
	 */
	public function choices() {

		return array();

	}

	/**
	 * Setup the choices values and set the choices property to allow dynamic building
	 */
	public function setup_choices() {

		if ( ! isset( $this->choices ) ) {
			if ( is_callable( $this->choices_callback ) ) {
				$choices = call_user_func( $this->choices_callback, $this );
			} else {
				$choices = $this->choices();
			}

			$this->choices = $choices;
		}

	}

	/**
	 * Fetch a field's value.
	 *
	 * @return mixed The requested field's value, if the field exists.
	 */
	final public function value() {

		$field = $this->get_field();

		$value = null;

		if ( $field ) {
			$value = $field->value( $this->get_item_id() );
		}

		return $value;

	}

	/**
	 * {@inheritdoc}
	 */
	public function check_capabilities() {

		$field = $this->get_field();

		if ( ! $field || ! $field->check_capabilities() ) {
			return false;
		}

		$section = $this->get_section();

		if ( $section && ! $section->check_capabilities() ) {
			return false;
		}

		return parent::check_capabilities();

	}

	/**
	 * Renders the control wrapper and calls $this->render_content() for the internals.
	 *
	 */
	protected function render() {

		$id    = 'fields-control-' . str_replace( '[', '-', str_replace( ']', '', $this->id ) );
		$class = 'fields-control fields-control-' . $this->type;
		?>
			<div id="<?php echo esc_attr( $id ); ?>" class="<?php echo esc_attr( $class ); ?>">
				<?php $this->render_content(); ?>
			</div>
		<?php

	}

	/**
	 * Render the control's content.
	 *
	 * Allows the content to be overridden without having to rewrite the wrapper in $this->render().
	 *
	 * Supports basic input types `text`, `checkbox`, `textarea`, `radio`, `select` and `dropdown-pages`.
	 * Additional input types such as `email`, `url`, `number`, `hidden` and `date` are supported implicitly.
	 *
	 * Control content can alternately be rendered in JS. See {@see WP_Fields_API_Control::print_template()}.
	 */
	protected function render_content() {

		?>
		<input type="<?php echo esc_attr( $this->type ); ?>" <?php $this->input_attrs(); ?> value="<?php echo esc_attr( $this->value() ); ?>" <?php $this->link(); ?> />
		<?php

	}

	/**
	 * Get the data link attribute for a field.
	 *
	 * @return string Data link parameter, if field exists, empty string otherwise.
	 */
	public function get_link() {

		$field = $this->get_field();

		if ( ! $field ) {
			return '';
		}

		return 'data-fields-field-link="' . esc_attr( $field->id ) . '"';

	}

	/**
	 * Render the data link attribute for the control's input element.
	 */
	public function link() {

		echo $this->get_link();

	}

	/**
	 * Render the custom attributes for the control's input element.
	 *
	 * @access public
	 */
	public function input_attrs() {

		// Setup field id / name
		if ( ! isset( $this->input_attrs['id'] ) ) {
			$this->input_attrs['id'] = 'field-' . $this->id;
		}

		if ( ! isset( $this->input_attrs['name'] ) ) {
			$input_name = $this->id;

			if ( ! empty( $this->input_name ) ) {
				$input_name = $this->input_name;
			}

			$this->input_attrs['name'] = $input_name;
		}

		$this->render_attrs( $this->input_attrs );

	}

	/**
	 * Render the custom attributes for the control's wrapper element.
	 *
	 * @access public
	 */
	public function wrap_attrs() {

		$classes = 'form-field ' . $this->object_type . '-' . $this->id . '-wrap field-' . $this->id . '-wrap fields-api-control';

		if ( isset( $this->wrap_attrs['class'] ) ) {
			$classes .= ' ' . $this->wrap_attrs['class'];
		}

		$this->wrap_attrs['class'] = $classes;

		$this->render_attrs( $this->wrap_attrs );

	}

	/**
	 * Render the control's JS template.
	 *
	 * This function is only run for control types that have been registered with
	 * {@see WP_Fields_API::register_control_type()}.
	 *
	 * In the future, this will also print the template for the control's container
	 * element and be override-able.
	 */
	public function print_template() {

?>
    <script type="text/html" id="tmpl-fields-<?php echo esc_attr( $this->object_type ); ?>-control-<?php echo esc_attr( $this->type ); ?>-content">
        <?php $this->content_template(); ?>
    </script>
<?php

	}

	/**
	 * An Underscore (JS) template for this control's content (but not its container).
	 *
	 * Class variables for this control class are available in the `data` JS object;
	 * export custom variables by overriding {@see WP_Fields_API_Control::to_json()}.
	 *
	 * @see WP_Fields_API_Control::print_template()
	 */
	public function content_template() {

		// Nothing by default

	}

	/**
	 * Magic method for handling backwards compatible properties / methods
	 *
	 * @param string $name Parameter name
	 *
	 * @return mixed|null
	 */
	public function &__get( $name ) {

		// Map $this->choices to $this->choices() for dynamic choice handling
		if ( 'choices' == $name ) {
			$this->setup_choices();

			return $this->{$name};
		}

		return null;// $this->{$name}; @todo Change this back when we're done testing

	}

	/**
	 * Magic method for handling backwards compatible properties / methods
	 *
	 * @param string $name Parameter name
	 *
	 * @return mixed|null
	 */
	public function __isset( $name ) {

		// Map $this->choices to $this->choices() for dynamic choice handling
		if ( 'choices' == $name ) {
			$this->setup_choices();

			return isset( $this->choices );
		}

		return false;

	}

}