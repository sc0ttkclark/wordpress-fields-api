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
	 * Datasource type for control
	 *
	 * @access public
	 * @var string
	 */
	public $datasource;

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
	 * List of control types that have had their templates printed to screen
	 *
	 * @access private
	 * @var array
	 */
	private static $printed_templates = array();

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

		// Set field based on control id, if not explicitly set
		if ( ! $this->field ) {
			$this->field = $id;
		}

		// Setup datasource
		if ( $this->datasource ) {
			$datasource_type = null;
			$datasource_args = null;

			if ( is_string( $this->datasource ) ) {
				$datasource_type = $this->datasource;
			} else {
				$datasource_args = $this->datasource;
			}

			$this->datasource = $wp_fields->setup_datasource( $datasource_type, $datasource_args );
		}

	}

	/**
	 * Get associated datasource
	 *
	 * @return null|WP_Fields_API_Datasource
	 */
	public function get_datasource() {

		/**
		 * @var $wp_fields WP_Fields_API
		 */
		global $wp_fields;

		$datasources = $this->get_children( 'datasource' );

		$datasource = null;

		if ( ! $datasources && $this->datasource ) {
			$datasource_type = null;
			$datasource_args = null;

			if ( is_string( $this->datasource ) ) {
				$datasource_type = $this->datasource;
			} else {
				$datasource_args = $this->datasource;
			}

			$datasource = $wp_fields->setup_datasource( $datasource_type, $datasource_args );

			if ( $datasource ) {
				$this->add_child( $datasource, 'datasource' );
			}
		} elseif ( $datasources ) {
			$datasource = current( $datasources );
		}

		return $datasource;

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

		/**
		 * @var $wp_fields WP_Fields_API
		 */
		global $wp_fields;

		$fields = $this->get_children( 'field' );

		$field = null;

		if ( ! $fields && $this->field ) {
			$field = $wp_fields->get_field( $this->object_type, $this->field, $this->object_name );

			if ( $field ) {
				$this->add_child( $field, 'field' );
			}
		} elseif ( $fields ) {
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

		$data = array();

		// If control has a datasource, use it for getting the data
		if ( $this->datasource ) {
			// Get datasource
			$datasource = $this->get_datasource();

			// Get data from datasource
			$data = $datasource->get_data( array(), $this );
		}

		return $data;

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

		$value = null;

		if ( null !== $this->value_override ) {
			$value = $this->value_override;
		} else {
			$field = $this->get_field();

			if ( $field ) {
				$value = $field->value( $this->get_item_id() );
			}
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

		$attrs = array(
			'id'    => 'fields-control-' . str_replace( '[', '-', str_replace( ']', '', $this->id ) ),
			'class' => 'fields-control fields-control-' . $this->type,
		);

		$input_attrs = $this->get_input_attrs();

		$attrs['data-fields-type']       = $this->type;
		$attrs['data-fields-input-name'] = $input_attrs['name'];
		?>
			<div <?php $this->render_attrs( $attrs ); ?>>
				<?php
					$render_control = true;

					// Check if datasource will override control rendering
					if ( $this->datasource ) {
						$datasource = $this->get_datasource();

						if ( true === $datasource->render_control( $this ) ) {
							$render_control = false;
						}
					}

					// Check if we need to render this control
					if ( $render_control ) {
						$this->render_content();
					}
				?>
			</div>
		<?php

	}

	/**
	 * Render the control's content.
	 *
	 * Allows the content to be overridden without having to rewrite the wrapper in $this->render().
	 *
	 * Supports input types such as `text`, `email`, `url`, `number`, `hidden` and `date` implicitly.
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
	 * Get the input attributes for the control's input element.
	 *
	 * @access public
	 * @return array
	 */
	public function get_input_attrs() {

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

		return $this->input_attrs;

	}

	/**
	 * Render the custom attributes for the control's input element.
	 *
	 * @access public
	 */
	public function input_attrs() {

		$this->render_attrs( $this->get_input_attrs() );

	}

	/**
	 * Get the custom attributes for the control's wrapper element.
	 *
	 * @access public
	 * @return array
	 */
	public function get_wrap_attrs() {

		if ( ! isset( $this->wrap_attrs['class'] ) || false === strpos( $this->wrap_attrs['class'], 'fields-api-control' ) ) {
			$classes = 'form-field ' . $this->object_type . '-' . $this->id . '-wrap field-' . $this->id . '-wrap fields-api-control';

			if ( isset( $this->wrap_attrs['class'] ) ) {
				$classes .= ' ' . $this->wrap_attrs['class'];
			}

			$this->wrap_attrs['class'] = $classes;
		}

		return $this->wrap_attrs;

	}

	/**
	 * Render the custom attributes for the control's wrapper element.
	 *
	 * @access public
	 */
	public function wrap_attrs() {

		$this->render_attrs( $this->get_wrap_attrs() );

	}

	/**
	 * Enqueue scripts/styles as needed.
	 *
	 * @access public
	 */
	public function enqueue() {


	}

	/**
	 * Render the control's JS template.
	 *
	 * This function is only run for control types that have been registered with
	 * {@see WP_Fields_API::register_control_type()}.
	 */
	public function print_template() {

		if ( in_array( $this->type, self::$printed_templates ) ) {
			return;
		}

		self::$printed_templates[] = $this->type;
?>
    <script type="text/html" id="tmpl-fields-control-<?php echo esc_attr( $this->type ); ?>-content">
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

		?>
		<input type="{{ data.type }}" name="{{ data.input_name }}" value="{{ data.value }}" id="{{ data.input_id }}" />
		<?php

	}

	/**
	 * Magic method for handling backwards compatible properties / methods
	 *
	 * @param string $name Parameter name
	 *
	 * @return mixed|null
	 */
	public function &__get( $name ) {

		$null = null;

		// Map $this->choices to $this->choices() for dynamic choice handling
		if ( 'choices' == $name ) {
			$this->setup_choices();

			return $this->{$name};
		}

		return $null;

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