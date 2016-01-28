<?php
/**
 * Fields API Control Class
 *
 * @package WordPress
 * @subpackage Fields_API
 *
 * @property array $choices Key/Values used by Multiple choice control types
 */
class WP_Fields_API_Control {

	/**
	 * Incremented with each new class instantiation, then stored in $instance_number.
	 *
	 * Used when sorting two instances whose priorities are equal.
	 *
	 * @access protected
	 * @var int
	 */
	protected static $instance_count = 0;

	/**
	 * Order in which this instance was created in relation to other instances.
	 *
	 * @access public
	 * @var int
	 */
	public $instance_number = 0;

	/**
	 * Unique identifier.
	 *
	 * @access public
	 * @var string
	 */
	public $id = '';

	/**
	 * Object type.
	 *
	 * @access public
	 * @var string
	 */
	public $object_type = '';

	/**
	 * Object name (for post types and taxonomies).
	 *
	 * @access public
	 * @var string
	 */
	public $object_name = '';

	/**
	 * Item ID of current item passed to WP_Fields_API_Field for value()
	 *
	 * @access public
	 * @var int|string
	 */
	public $item_id;

	/**
	 * All fields tied to the control.
	 *
	 * @access public
	 * @var array
	 */
	public $fields = array();

	/**
	 * The primary field for the control (if there is one).
	 *
	 * @access public
	 * @var string|WP_Fields_API_Field
	 */
	public $field = 'default';

	/**
	 * The primary screen for the control (if there is one).
	 *
	 * @access public
	 * @var string|WP_Fields_API_Section
	 */
	public $section = '';

	/**
	 * The primary screen for the control (if there is one).
	 *
	 * @access public
	 * @var string|WP_Fields_API_Screen
	 */
	public $screen = '';

	/**
	 * @access public
	 * @var int
	 */
	public $priority = 10;

	/**
	 * @access public
	 * @var string
	 */
	public $label = '';

	/**
	 * @access public
	 * @var string
	 */
	public $description = '';

	/**
	 * @access public
	 * @var array
	 */
	public $input_attrs = array();

	/**
	 * @access public
	 * @var string
	 */
	public $type = 'text';

	/**
	 * Callback.
	 *
	 * @access public
	 *
	 * @see WP_Fields_API_Control::active()
	 *
	 * @var callable Callback is called with one argument, the instance of
	 *               WP_Fields_API_Control, and returns bool to indicate whether
	 *               the control is active (such as it relates to the URL
	 *               currently being previewed).
	 */
	public $active_callback = '';

	/**
	 * Capabilities Callback.
	 *
	 * @access public
	 *
	 * @see WP_Fields_API_Control::check_capabilities()
	 *
	 * @var callable Callback is called with one argument, the instance of
	 *               WP_Fields_API_Control, and returns bool to indicate whether
	 *               the control has capabilities to be used.
	 */
	public $capabilities_callback = '';

	/**
	 * Constructor.
	 *
	 * Parameters are not set to maintain PHP overloading compatibility (strict standards)
	 */
	public function __construct() {

		$args = func_get_args();

		call_user_func_array( array( $this, 'init' ), $args );

	}

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

		$this->object_type = $object_type;

		if ( is_array( $id ) ) {
			$args = $id;

			$id = '';
		} else {
			$this->id = $id;
		}

		$keys = array_keys( get_object_vars( $this ) );

		// Magic property, allowing for override
		$keys[] = 'choices';

		foreach ( $keys as $key ) {
			if ( isset( $args[ $key ] ) ) {
				$this->$key = $args[ $key ];
			}
		}

		if ( empty( $this->active_callback ) ) {
			$this->active_callback = array( $this, 'active_callback' );
		}

		self::$instance_count += 1;
		$this->instance_number = self::$instance_count;

		// Process fields.
		if ( empty( $this->fields ) ) {
			$this->fields = $id;
		}

		$fields = array();

		if ( is_array( $this->fields ) ) {
			foreach ( $this->fields as $key => $field ) {
				$field_obj = $wp_fields->get_field( $this->object_type, $field, $this->object_name );

				if ( $field_obj ) {
					$fields[ $key ] = $field_obj;
				}
			}
		} else {
			$field_obj = $wp_fields->get_field( $this->object_type, $this->fields, $this->object_name );

			if ( $field_obj ) {
				$this->field       = $field_obj;
				$fields['default'] = $field_obj;
			}
		}

		$this->fields = $fields;

	}

	/**
	 * Setup the choices values and set the choices property to allow dynamic building
	 */
	public function setup_choices() {

		if ( ! isset( $this->choices ) ) {
			$choices = $this->choices();

			$this->choices = $choices;
		}

	}

	/**
	 * Enqueue control related scripts/styles.
	 */
	public function enqueue() {}

	/**
	 * Check whether control is active to current Fields API preview.
	 *
	 * @access public
	 *
	 * @return bool Whether the control is active to the current preview.
	 */
	final public function active() {

		$control = $this;
		$active = true;

		if ( is_callable( $this->active_callback ) ) {
			$active = call_user_func( $this->active_callback, $this );
		}

		/**
		 * Filter response of WP_Fields_API_Control::active().
		 *
		 * @param bool                  $active  Whether the Field control is active.
		 * @param WP_Fields_API_Control $control WP_Fields_API_Control instance.
		 */
		$active = apply_filters( 'fields_control_active_' . $this->object_type, $active, $control );

		return $active;

	}

	/**
	 * Default callback used when invoking WP_Fields_API_Control::active().
	 *
	 * Subclasses can override this with their specific logic, or they may
	 * provide an 'active_callback' argument to the constructor.
	 *
	 * @access public
	 *
	 * @return bool Always true.
	 */
	public function active_callback() {

		return true;

	}

	/**
	 * Fetch a field's value.
	 * Grabs the main field by default.
	 *
	 * @param string $field_key
	 * @return mixed The requested field's value, if the field exists.
	 */
	final public function value( $field_key = 'default' ) {

		if ( isset( $this->fields[ $field_key ] ) ) {
			/**
			 * @var $field WP_Fields_API_Field
			 */
			$field = $this->fields[ $field_key ];

			return $field->value( $this->item_id );
		}

		return null;

	}

	/**
	 * Get the data to export to the client via JSON.
	 *
	 * @return array Array of parameters passed to the JavaScript.
	 */
	public function json() {

		$array = array();

		$array['fields'] = wp_list_pluck( $this->fields, 'id' );
		$array['type'] = $this->type;
		$array['priority'] = $this->priority;
		$array['active'] = $this->active();
		$array['section'] = $this->section;
		$array['content'] = $this->get_content();
		$array['label'] = $this->label;
		$array['description'] = $this->description;
		$array['instanceNumber'] = $this->instance_number;

		return $array;

	}

	/**
	 * Check if the theme supports the control and check user capabilities.
	 *
	 * @return bool False if theme doesn't support the control or user doesn't have the required permissions, otherwise true.
	 */
	final public function check_capabilities() {

		/**
		 * @var $wp_fields WP_Fields_API
		 */
		global $wp_fields;

		/**
		 * @var $field WP_Fields_API_Field
		 */
		foreach ( $this->fields as $field ) {
			if ( ! $field || ! $field->check_capabilities() ) {
				return false;
			}
		}

		$section = $wp_fields->get_section( $this->object_type, $this->section, $this->object_name );

		if ( $section && ! $section->check_capabilities() ) {
			return false;
		}

		$access = true;

		if ( is_callable( $this->capabilities_callback ) ) {
			$access = call_user_func( $this->capabilities_callback, $this );
		}

		return $access;

	}

	/**
	 * Get the control's content for insertion.
	 *
	 * @return string Contents of the control.
	 */
	final public function get_content() {

		ob_start();

		$this->maybe_render();

		$template = trim( ob_get_clean() );

		return $template;

	}

	/**
	 * Check capabilities and render the control.
	 *
	 * @uses WP_Fields_API_Control::render()
	 */
	final public function maybe_render() {

		if ( ! $this->check_capabilities() ) {
			return;
		}

		/**
		 * Fires just before the current control is rendered.
		 *
		 * @param WP_Fields_API_Control $this WP_Fields_API_Control instance.
		 */
		do_action( 'fields_render_control_' . $this->object_type, $this );

		/**
		 * Fires just before a specific control is rendered.
		 *
		 * The dynamic portion of the hook name, `$this->id`, refers to
		 * the control ID.
		 *
		 * @param WP_Fields_API_Control $this {@see WP_Fields_API_Control} instance.
		 */
		do_action( 'fields_render_control_' . $this->object_type . '_' . $this->object_name . '_' . $this->id, $this );

		$this->render();

	}

	/**
	 * Renders the control wrapper and calls $this->render_content() for the internals.
	 *
	 */
	protected function render() {

		$id    = 'fields-control-' . str_replace( '[', '-', str_replace( ']', '', $this->id ) );
		$class = 'fields-control fields-control-' . $this->type;

		?><li id="<?php echo esc_attr( $id ); ?>" class="<?php echo esc_attr( $class ); ?>">
			<?php $this->render_content(); ?>
		</li><?php

	}

	/**
	 * Get the data link attribute for a field.
	 *
	 *
	 * @param string $field_key
	 * @return string Data link parameter, if $field_key is a valid field, empty string otherwise.
	 */
	public function get_link( $field_key = 'default' ) {

		if ( ! isset( $this->fields[ $field_key ] ) ) {
			return '';
		}

		return 'data-fields-field-link="' . esc_attr( $this->fields[ $field_key ]->id ) . '"';

	}

	/**
	 * Render the data link attribute for the control's input element.
	 *
	 * @uses WP_Fields_API_Control::get_link()
	 *
	 * @param string $field_key
	 */
	public function link( $field_key = 'default' ) {

		echo $this->get_link( $field_key );

	}

	/**
	 * Render the custom attributes for the control's input element.
	 *
	 * @access public
	 */
	public function input_attrs() {

		foreach ( $this->input_attrs as $attr => $value ) {
			echo $attr . '="' . esc_attr( $value ) . '" ';
		}

	}

	/**
	 * Render the control's content.
	 *
	 * Allows the content to be overriden without having to rewrite the wrapper in $this->render().
	 *
	 * Supports basic input types `text`, `checkbox`, `textarea`, `radio`, `select` and `dropdown-pages`.
	 * Additional input types such as `email`, `url`, `number`, `hidden` and `date` are supported implicitly.
	 *
	 * Control content can alternately be rendered in JS. See {@see WP_Fields_API_Control::print_template()}.
	 */
	public function render_content() {

		?>
		<label>
			<?php if ( ! empty( $this->label ) ) : ?>
				<span class="fields-control-title"><?php echo esc_html( $this->label ); ?></span>
			<?php endif;
			if ( ! empty( $this->description ) ) : ?>
				<span class="description fields-control-description"><?php echo $this->description; ?></span>
			<?php endif; ?>
			<input type="<?php echo esc_attr( $this->type ); ?>" <?php $this->input_attrs(); ?> value="<?php echo esc_attr( $this->value() ); ?>" <?php $this->link(); ?> />
		</label>
		<?php

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

			return $this->choices;
		}

		return null;

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