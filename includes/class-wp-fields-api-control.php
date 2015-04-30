<?php
/**
 * Fields API Control Class
 *
 * @package WordPress
 * @subpackage Fields_API
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
	public $instance_number;

	/**
	 * @access public
	 * @var string
	 */
	public $id;

	/**
	 * @access public
	 * @var string
	 */
	public $object;

	/**
	 * All settings tied to the control.
	 *
	 * @access public
	 * @var array
	 */
	public $settings;

	/**
	 * The primary setting for the control (if there is one).
	 *
	 * @access public
	 * @var string
	 */
	public $setting = 'default';

	/**
	 * @access public
	 * @var int
	 */
	public $priority = 10;

	/**
	 * @access public
	 * @var string
	 */
	public $section = '';

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
	 * @todo: Remove choices
	 *
	 * @access public
	 * @var array
	 */
	public $choices = array();

	/**
	 * @access public
	 * @var array
	 */
	public $input_attrs = array();

	/**
	 * @deprecated It is better to just call the json() method
	 * @access public
	 * @var array
	 */
	public $json = array();

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
	 * Constructor.
	 *
	 * Parameters are not set to maintain PHP overloading compatibility (strict standards)
	 *
	 * @return WP_Fields_API_Control $setting
	 */
	public function __construct() {

		call_user_func_array( array( $this, 'init' ), func_get_args() );

	}

	/**
	 * Secondary constructor; Any supplied $args override class property defaults.
	 *
	 * @param string $object
	 * @param string $id                    An specific ID of the setting. Can be a
	 *                                      theme mod or option name.
	 * @param array  $args                  Setting arguments.
	 *
	 * @return WP_Fields_API_Control $setting
	 */
	public function init( $object, $id, $args = array() ) {

		global $wp_fields;

		$this->object = $object;

		$keys = array_keys( get_object_vars( $this ) );

		foreach ( $keys as $key ) {
			if ( isset( $args[ $key ] ) ) {
				$this->$key = $args[ $key ];
			}
		}

		$this->id = $id;

		self::$instance_count += 1;
		$this->instance_number = self::$instance_count;

		// Process settings.
		if ( empty( $this->settings ) ) {
			$this->settings = $id;
		}

		$settings = array();

		if ( is_array( $this->settings ) ) {
			foreach ( $this->settings as $key => $setting ) {
				$settings[ $key ] = $wp_fields->get_setting( $this->object, $setting );
			}
		} else {
			$this->setting = $wp_fields->get_setting( $this->object, $this->settings );
			$settings['default'] = $this->setting;
		}

		$this->settings = $settings;

	}

	/**
	 * Enqueue control related scripts/styles.
	 *
	 */
	public function enqueue() {}

	/**
	 * Check whether control is active to current Customizer preview.
	 *
	 * @access public
	 *
	 * @return bool Whether the control is active to the current preview.
	 */
	final public function active() {

		$control = $this;
		$active = call_user_func( $this->active_callback, $this );

		/**
		 * Filter response of WP_Fields_API_Control::active().
		 *
		 * @param bool                  $active  Whether the Field control is active.
		 * @param WP_Fields_API_Control $control WP_Fields_API_Control instance.
		 */
		$active = apply_filters( 'fields_control_active_' . $this->object, $active, $control );

		return $active;

	}

	/**
	 * Default callback used when invoking WP_Customize_Control::active().
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
	 * Fetch a setting's value.
	 * Grabs the main setting by default.
	 *
	 * @param string $setting_key
	 * @return mixed The requested setting's value, if the setting exists.
	 */
	final public function value( $setting_key = 'default' ) {

		if ( isset( $this->settings[ $setting_key ] ) ) {
			return $this->settings[ $setting_key ]->value();
		}

		return null;

	}

	/**
	 * Refresh the parameters passed to the JavaScript via JSON.
	 */
	public function to_json() {

		$this->json['settings'] = array();

		foreach ( $this->settings as $key => $setting ) {
			$this->json['settings'][ $key ] = $setting->id;
		}

		$this->json['type'] = $this->type;
		$this->json['priority'] = $this->priority;
		$this->json['active'] = $this->active();
		$this->json['section'] = $this->section;
		$this->json['content'] = $this->get_content();
		$this->json['label'] = $this->label;
		$this->json['description'] = $this->description;
		$this->json['instanceNumber'] = $this->instance_number;

	}

	/**
	 * Get the data to export to the client via JSON.
	 *
	 * @return array Array of parameters passed to the JavaScript.
	 */
	public function json() {

		$this->to_json();

		return $this->json;

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

		foreach ( $this->settings as $setting ) {
			if ( ! $setting->check_capabilities() ) {
				return false;
			}
		}

		$section = $wp_fields->get_section( $this->object, $this->section );

		if ( isset( $section ) && ! $section->check_capabilities() ) {
			return false;
		}

		return true;

	}

	/**
	 * Get the control's content for insertion.
	 *
	 * @return string Contents of the control.
	 */
	final public function get_content() {

		ob_start();

		$this->maybe_render();

		$template = trim( ob_get_contents() );

		ob_end_clean();

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
		do_action( 'fields_render_control_' . $this->object, $this );

		/**
		 * Fires just before a specific control is rendered.
		 *
		 * The dynamic portion of the hook name, `$this->id`, refers to
		 * the control ID.
		 *
		 * @param WP_Fields_API_Control $this {@see WP_Fields_API_Control} instance.
		 */
		do_action( 'fields_render_control_' . $this->object . '_' . $this->id, $this );

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
	 * Get the data link attribute for a setting.
	 *
	 *
	 * @param string $setting_key
	 * @return string Data link parameter, if $setting_key is a valid setting, empty string otherwise.
	 */
	public function get_link( $setting_key = 'default' ) {

		if ( ! isset( $this->settings[ $setting_key ] ) ) {
			return '';
		}

		return 'data-fields-setting-link="' . esc_attr( $this->settings[ $setting_key ]->id ) . '"';

	}

	/**
	 * Render the data link attribute for the control's input element.
	 *
	 * @uses WP_Fields_API_Control::get_link()
	 *
	 * @param string $setting_key
	 */
	public function link( $setting_key = 'default' ) {

		echo $this->get_link( $setting_key );

	}

	/**
	 * Render the custom attributes for the control's input element.
	 *
	 * @access public
	 */
	public function input_attrs() {

		foreach( $this->input_attrs as $attr => $value ) {
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
	 *
	 */
	public function render_content() {

		switch( $this->type ) {
			case 'checkbox':
				?>
				<label>
					<input type="checkbox" value="<?php echo esc_attr( $this->value() ); ?>" <?php $this->link(); checked( $this->value() ); ?> />
					<?php echo esc_html( $this->label ); ?>
					<?php if ( ! empty( $this->description ) ) : ?>
						<span class="description fields-control-description"><?php echo $this->description; ?></span>
					<?php endif; ?>
				</label>
				<?php
				break;
			case 'radio':
				if ( empty( $this->choices ) )
					return;

				$name = '_fields-radio-' . $this->id;

				if ( ! empty( $this->label ) ) : ?>
					<span class="fields-control-title"><?php echo esc_html( $this->label ); ?></span>
				<?php endif;
				if ( ! empty( $this->description ) ) : ?>
					<span class="description fields-control-description"><?php echo $this->description ; ?></span>
				<?php endif;

				foreach ( $this->choices as $value => $label ) :
					?>
					<label>
						<input type="radio" value="<?php echo esc_attr( $value ); ?>" name="<?php echo esc_attr( $name ); ?>" <?php $this->link(); checked( $this->value(), $value ); ?> />
						<?php echo esc_html( $label ); ?><br/>
					</label>
					<?php
				endforeach;
				break;
			case 'select':
				if ( empty( $this->choices ) )
					return;

				?>
				<label>
					<?php if ( ! empty( $this->label ) ) : ?>
						<span class="fields-control-title"><?php echo esc_html( $this->label ); ?></span>
					<?php endif;
					if ( ! empty( $this->description ) ) : ?>
						<span class="description fields-control-description"><?php echo $this->description; ?></span>
					<?php endif; ?>

					<select <?php $this->link(); ?>>
						<?php
						foreach ( $this->choices as $value => $label )
							echo '<option value="' . esc_attr( $value ) . '"' . selected( $this->value(), $value, false ) . '>' . $label . '</option>';
						?>
					</select>
				</label>
				<?php
				break;
			case 'textarea':
				?>
				<label>
					<?php if ( ! empty( $this->label ) ) : ?>
						<span class="fields-control-title"><?php echo esc_html( $this->label ); ?></span>
					<?php endif;
					if ( ! empty( $this->description ) ) : ?>
						<span class="description fields-control-description"><?php echo $this->description; ?></span>
					<?php endif; ?>
					<textarea rows="5" <?php $this->link(); ?>><?php echo esc_textarea( $this->value() ); ?></textarea>
				</label>
				<?php
				break;
			case 'dropdown-pages':
				$dropdown = wp_dropdown_pages(
					array(
						'name'              => '_fields-dropdown-pages-' . $this->id,
						'echo'              => 0,
						'show_option_none'  => __( '&mdash; Select &mdash;' ),
						'option_none_value' => '0',
						'selected'          => $this->value(),
					)
				);

				// Hackily add in the data link parameter.
				$dropdown = str_replace( '<select', '<select ' . $this->get_link(), $dropdown );

				printf(
					'<label class="fields-control-select"><span class="fields-control-title">%s</span> %s</label>',
					$this->label,
					$dropdown
				);
				break;
			default:
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
				break;
		}

	}

	/**
	 * Render the control's JS template.
	 *
	 * This function is only run for control types that have been registered with
	 * {@see WP_Fields_API::register_control_type()}.
	 *
	 * In the future, this will also print the template for the control's container
	 * element and be override-able.
	 *
	 */
	public function print_template() {

?>
    <script type="text/html" id="tmpl-fields-<?php echo esc_attr( $this->object ); ?>-control-<?php echo esc_attr( $this->type ); ?>-content">
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
	 *
	 */
	public function content_template() {

		// Nothing by default

	}

}