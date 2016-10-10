<?php
/**
 * Fields API Control Class
 *
 * @package WordPress
 * @subpackage Fields_API
 *
 * @property array $choices Key/Values used by Multiple choice control types
 */
class WP_Fields_API_Control extends WP_Fields_API_Component {

	/**
	 * Label to render
	 *
	 * @var string
	 */
	public $label;

	/**
	 * Show label or not
	 *
	 * @var bool
	 */
	public $display_label;

	/**
	 * Description to render
	 *
	 * @var string
	 */
	public $description;

	/**
	 * Description callback function to execute
	 *
	 * @var callback
	 */
	public $description_callback;

	/**
	 * Item ID of current item passed to WP_Fields_API_Field for value()
	 *
	 * @access public
	 * @var int|string
	 */
	public $item_id = 0;

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
	 * Contains field object
	 *
	 * @var WP_Fields_API_Field|null
	 */
	public $field = null;

	/**
	 * Datasource type for control
	 *
	 * @access public
	 * @var WP_Fields_API_Datasource
	 */
	public $datasource = null;

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
	 * Render callback
	 *
	 * @access public
	 *
	 * @see WP_Fields_API_Control::render_content()
	 *
	 * @var callable Callback is called with one argument, the instance of WP_Fields_API_Control.
	 *               It outputs an input for the control to use.
	 */
	public $render_callback = null;

	/**
	 * List of control types that have had their templates printed to screen
	 *
	 * @access private
	 * @var array
	 */
	private static $printed_templates = array();

	/**
	 * Store save errors for this control
	 *
	 * @access public
	 * @var WP_Error
	 */
	public $error = null;

	/**
	 * {@inheritdoc}
	 */
	public function __construct( $id, $args = array() ) {

		$field = null;

		if ( isset( $args['field'] ) ) {
			if ( ! empty( $args['field'] ) ) {
				$field = $args['field'];
			}

			unset( $args['field'] );
		}

		parent::__construct( $id, $args );

		if ( $field ) {
			$this->add_field( $field );
		}

	}

	/**
	 * Add field to control
	 *
	 * @param string                    $id   Field ID
	 * @param array|WP_Fields_API_Field $args Field arguments
	 *
	 * @return WP_Error|WP_Fields_API_Field
	 */
	public function add_field( $id, $args = array() ) {

		/**
		 * @var $wp_fields WP_Fields_API
		 */
		global $wp_fields;

		if ( is_a( $args, 'WP_Fields_API_Field' ) ) {
			$id = $args->id;
		} elseif ( ! empty( $args['id'] ) ) {
			$id = $args['id'];
		}

		if ( empty( $id ) ) {
			// @todo Need WP_Error code
			return new WP_Error( 'fields-api-id-required', __( 'ID is required.', 'fields-api' ) );
		}

		if ( ! empty( $this->field ) ) {
			// @todo Need WP_Error code
			return new WP_Error( 'fields-api-field-exists', __( 'Field already exists.', 'fields-api' ) );
		}

		$object_type = $this->get_object_type();

		if ( empty( $object_type ) ) {
			// @todo Need WP_Error code
			return new WP_Error( 'fields-api-object-type-required', __( 'Object type is required.', 'fields-api' ) );
		}

		$field = $wp_fields->add_field( $object_type, $id, $args );

		if ( $field && ! is_wp_error( $field ) ) {
			$this->field = $field;

			$field->parent = $this;
		}

		return $field;

	}

	/**
	 * Get field from control
	 *
	 * @return WP_Fields_API_Field|null
	 */
	public function get_field() {

		return $this->field;

	}

	/**
	 * Remove field from control
	 */
	public function remove_field() {

		if ( $this->field ) {
			$this->field->parent = null;
		}

		$this->field = null;

	}

	/**
	 * Get the container description.
	 *
	 * @return string Description of the container.
	 */
	public function render_description() {
		if ( is_callable( $this->description_callback ) ) {
			call_user_func( $this->description_callback, $this );
			return;
		}

		if ( $this->description ) {
		?>
			<p class="description">
				<?php echo wp_kses_post( $this->description ); ?>
			</p>
		<?php
		}
	}

	/**
	 * Get choice values
	 */
	public function choices() {

		$data = array();

		// If control has a datasource, use it for getting the data
		if ( $this->datasource ) {
			$args = array();

			// @todo Needs hook docs
			$args = apply_filters( "fields_control_datasource_get_args_{$this->datasource->type}", $args, $this->datasource, $this );

			// @todo Needs hook docs
			$args = apply_filters( "fields_control_datasource_get_args_{$this->datasource->type}_{$this->id}", $args, $this->datasource, $this );

			// Get data from datasource
			$data = $this->datasource->get_data( $args, $this );
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
			if ( $this->field ) {
				$value = $this->field->value( $this->get_item_id() );
			}
		}

		return $value;

	}

	/**
	 * {@inheritdoc}
	 */
	public function check_capabilities() {

		if ( ! $this->field || ! $this->field->check_capabilities() ) {
			return false;
		}

		$section = $this->parent;

		if ( $section && ! $section->check_capabilities() ) {
			return false;
		}

		return parent::check_capabilities();

	}

	/**
	 * Render HTML attributes safely to the screen.
	 *
	 * @access public
	 *
	 * @param array $attrs
	 */
	public function render_attrs( $attrs = array() ) {
		foreach ( $attrs as $attr => $value ) {
			echo esc_attr( $attr ) . '="' . esc_attr( $value ) . '" ';
		}
	}

	/**
	 * Renders the control wrapper and calls $this->render_content() for the internals.
	 */
	protected function render() {

		$attrs = array(
			'id'    => 'fields-control-' . str_replace( '[', '-', str_replace( ']', '', $this->id ) ),
			'class' => 'fields-control fields-control-' . $this->type,
		);
		if ( is_wp_error( $this->error ) ) {
			$attrs['class'] .= ' fields-error fields-error-code-' . esc_attr( $this->error->get_error_code() );
		}

		$input_attrs = $this->get_input_attrs();

		$attrs['data-fields-type']       = $this->type;
		$attrs['data-fields-input-name'] = $input_attrs['name'];
		?>
			<div <?php $this->render_attrs( $attrs ); ?>>
				<?php
					$render_control = true;

					// Check if datasource will override control rendering
					if ( $this->datasource ) {
						if ( true === $this->datasource->render_control( $this ) ) {
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

		if ( is_callable( $this->render_callback ) ) {
			call_user_func( $this->render_callback, $this );

			return;
		}
		?>
		<input type="<?php echo esc_attr( $this->type ); ?>" <?php $this->input_attrs(); ?> value="<?php echo esc_attr( $this->value() ); ?>" <?php $this->link(); ?> />

		<?php if ( is_wp_error( $this->error ) ) : ?>
			<span class="field-error-text"><?php echo esc_html( $this->error->get_error_message() ); ?></span>
		<?php endif; ?>

		<?php

	}

	/**
	 * Get the data link attribute for a field.
	 *
	 * @return string Data link parameter, if field exists, empty string otherwise.
	 */
	public function get_link() {

		if ( ! $this->field ) {
			return '';
		}

		return ' data-fields-field-link="' . esc_attr( $this->field->id ) . '"';

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
			$this->input_attrs['name'] = $this->id;
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
}