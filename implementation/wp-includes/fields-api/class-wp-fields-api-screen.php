<?php
/**
 * WordPress Fields API Screen classes
 *
 * @package WordPress
 * @subpackage Fields_API
 */

/**
 * Fields API Screen class.
 *
 * A UI container for sections, managed by WP_Fields_API.
 *
 * @see WP_Fields_API
 */
class WP_Fields_API_Screen {

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
	 * Priority of the screen, defining the display order of screens and sections.
	 *
	 * @access public
	 * @var integer
	 */
	public $priority = 160;

	/**
	 * Capability required for the screen.
	 *
	 * @access public
	 * @var string
	 */
	public $capability = 'edit_theme_options';

	/**
	 * Theme feature support for the screen.
	 *
	 * @access public
	 * @var string|array
	 */
	public $theme_supports = '';

	/**
	 * Title of the screen to show in UI.
	 *
	 * @access public
	 * @var string
	 */
	public $title = '';

	/**
	 * Description to show in the UI.
	 *
	 * @access public
	 * @var string
	 */
	public $description = '';

	/**
	 * Fields API sections for this screen.
	 *
	 * @access public
	 * @var array<WP_Fields_API_Section>
	 */
	public $sections = array();

	/**
	 * Type of this screen.
	 *
	 * @access public
	 * @var string
	 */
	public $type = 'default';

	/**
	 * Active callback.
	 *
	 * @access public
	 *
	 * @see WP_Fields_API_Section::active()
	 *
	 * @var callable Callback is called with one argument, the instance of
	 *               {@see WP_Fields_API_Section}, and returns bool to indicate
	 *               whether the section is active (such as it relates to the URL
	 *               currently being previewed).
	 */
	public $active_callback = '';

	/**
	 * Capabilities Callback.
	 *
	 * @access public
	 *
	 * @see WP_Fields_API_Screen::check_capabilities()
	 *
	 * @var callable Callback is called with one argument, the instance of
	 *               WP_Fields_API_Screen, and returns bool to indicate whether
	 *               the screen has capabilities to be used.
	 */
	public $capabilities_callback = '';

	/**
	 * Item ID of current item
	 *
	 * @access public
	 * @var int|string
	 */
	public $item_id;

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
	 * @param string $id            A specific ID of the screen.
	 * @param array  $args          Screen arguments.
	 */
	public function init( $object_type, $id, $args = array() ) {

		if ( ! empty( $object_type ) ) {
			$this->object_type = $object_type;
		}

		if ( ! empty( $id ) ) {
			if ( is_array( $id ) ) {
				$args = $id;

				$id = '';
			} else {
				$this->id = $id;
			}
		}

		$keys = array_keys( get_object_vars( $this ) );

		foreach ( $keys as $key ) {
			if ( isset( $args[ $key ] ) ) {
				$this->$key = $args[ $key ];
			}
		}

		self::$instance_count += 1;
		$this->instance_number = self::$instance_count;

		if ( empty( $this->active_callback ) ) {
			$this->active_callback = array( $this, 'active_callback' );
		}

		$this->sections = array(); // Users cannot customize the $sections array.

	}

	/**
	 * Check whether screen is active to current Fields API preview.
	 *
	 * @access public
	 *
	 * @return bool Whether the screen is active to the current preview.
	 */
	final public function active() {

		$screen = $this;
		$active = true;

		if ( is_callable( $this->active_callback ) ) {
			$active = call_user_func( $this->active_callback, $this );
		}

		/**
		 * Filter response of WP_Fields_API_Screen::active().
		 *
		 *
		 * @param bool                $active  Whether the Fields API screen is active.
		 * @param WP_Fields_API_Screen $screen   {@see WP_Fields_API_Screen} instance.
		 */
		$active = apply_filters( 'fields_api_screen_active_' . $this->object_type, $active, $screen );

		return $active;

	}

	/**
	 * Default callback used when invoking {@see WP_Fields_API_Screen::active()}.
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
	 * Gather the parameters passed to client JavaScript via JSON.
	 *
	 * @return array The array to be exported to the client as JSON.
	 */
	public function json() {

		$array = wp_array_slice_assoc( (array) $this, array( 'id', 'title', 'description', 'priority', 'type' ) );

		$array['content'] = $this->get_content();
		$array['active'] = $this->active();
		$array['instanceNumber'] = $this->instance_number;

		return $array;

	}

	/**
	 * Checks required user capabilities and whether the theme has the
	 * feature support required by the screen.
	 *
	 * @return bool False if theme doesn't support the screen or user can't change screen, otherwise true.
	 */
	public function check_capabilities() {

		if ( $this->capability && ! call_user_func_array( 'current_user_can', (array) $this->capability ) ) {
			return false;
		}

		if ( $this->theme_supports && ! call_user_func_array( 'current_theme_supports', (array) $this->theme_supports ) ) {
			return false;
		}

		$access = true;

		if ( is_callable( $this->capabilities_callback ) ) {
			$access = call_user_func( $this->capabilities_callback, $this );
		}

		return $access;

	}

	/**
	 * Get the screen's content template for insertion into the Fields API screen.
	 *
	 * @return string Content for the screen.
	 */
	final public function get_content() {

		ob_start();

		$this->maybe_render();

		$template = trim( ob_get_contents() );

		ob_end_clean();

		return $template;

	}

	/**
	 * Check capabilities and render the screen.
	 *
	 * @param int|null    $item_id Item ID
	 * @param string|null $object_name Object name
	 */
	final public function maybe_render( $item_id = null, $object_name = null ) {

		$this->item_id = $item_id;

		if ( null !== $object_name ) {
			$this->object_name = $object_name;
		}

		if ( ! $this->check_capabilities() ) {
			return;
		}

		/**
		 * Fires before rendering a Fields API screen.
		 *
		 * @param WP_Fields_API_Screen $this WP_Fields_API_Screen instance.
		 */
		do_action( "fields_api_render_screen_{$this->object_type}", $this );

		/**
		 * Fires before rendering a specific Fields API screen.
		 *
		 * The dynamic portion of the hook name, `$this->id`, refers to
		 * the ID of the specific Fields API screen to be rendered.
		 */
		do_action( "fields_api_render_screen_{$this->object_type}_{$this->object_name}_{$this->id}" );

		$this->render();

	}

	/**
	 * Render the screen's JS templates.
	 *
	 * This function is only run for screen types that have been registered with
	 * WP_Fields_API::register_screen_type().
	 *
	 * @see WP_Fields_API::register_screen_type()
	 */
	public function print_template() {

		// Nothing by default

	}

	/**
	 * An Underscore (JS) template for rendering this screen's container.
	 *
	 * Class variables for this screen class are available in the `data` JS object;
	 * export custom variables by overriding WP_Fields_API_Screen::json().
	 *
	 * @see WP_Fields_API_Screen::print_template()
	 *
	 * @since 4.3.0
	 * @access protected
	 */
	protected function render_template() {

		// Nothing by default

	}

	/**
	 * An Underscore (JS) template for this screen's content
	 *
	 * Class variables for this control class are available in the `data` JS object;
	 * export custom variables by overriding {@see WP_Fields_API_Screen::to_json()}.
	 *
	 * @see WP_Fields_API_Screen::print_template()
	 *
	 * @access protected
	 */
	protected function content_template() {

		// Nothing by default

	}

	/**
	 * Register screens, sections, controls, and fields
	 *
	 * @param string      $object_type
	 * @param string      $screen_id
	 * @param null|string $object_name
	 * @param array       $args
	 *
	 * @return WP_Fields_API_Screen
	 */
	public static function register( $object_type = null, $screen_id = null, $object_name = null, $args = array() ) {

		/**
		 * @var $wp_fields WP_Fields_API
		 * @var $screen    WP_Fields_API_Screen
		 */
		global $wp_fields;

		// Set object_name if not overridden
		if ( ! isset( $args['object_name'] ) ) {
			$args['object_name'] = $object_name;
		}

		$class_name = get_called_class();

		// Setup screen
		$screen = new $class_name( $object_type, $screen_id, $args );

		// Add screen to Fields API
		$wp_fields->add_screen( $screen->object_type, $screen, $screen->object_name );

		// Register control types for this screen
		$screen->register_control_types( $wp_fields );

		// Register fields for this screen
		$screen->register_fields( $wp_fields );

		return $screen;

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
	 * Encapsulated registering of sections, controls, and fields for a screen
	 *
	 * @param WP_Fields_API $wp_fields
	 */
	public function register_fields( $wp_fields ) {

		/*
		// Register control types
		$wp_fields->register_control_type( 'control-type-id', 'Control_Class_Name' );

		// Add section(s)
		$wp_fields->add_section( $this->object_type, 'section-id', $this->object_name, array(
			'title' => __( 'Section Heading' ),
		    'screen' => $this->id,
		) );

		$field_args = array(
			// 'sanitize_callback' => array( $this, 'my_sanitize_callback' ),
			'control'                   => array(
				'type'                  => 'text',
				'section'               => 'section-id',
				'label'                 => __( 'Control Label' ),
				'description'           => __( 'Description of control' ),
				// 'capabilities_callback' => array( $this, 'my_capabilities_callback' ),
			),
		);

		$wp_fields->add_field( $this->object_type, 'field-id', $this->object_name, $field_args );
		*/

		//////////////
		// Examples //
		//////////////

		// Section
		$wp_fields->add_section( $this->object_type, $this->id . '-example-my-fields', $this->object_name, array(
			'title' => __( 'Fields API Example - My Fields' ),
		    'screen' => $this->id,
		) );

		// Add example for each control type
		$control_types = array(
			'text',
			'checkbox',
			'multi-checkbox',
			'radio',
			'select',
			'dropdown-pages',
			'dropdown-terms',
			'color',
			'media',
			'media_file',
		);

		$option_types = array(
			'multi-checkbox',
			'radio',
			'select',
		);

		foreach ( $control_types as $control_type ) {
			$id    = 'example_my_' . $control_type . '_field';
			$label = sprintf( __( '%s Field' ), ucwords( str_replace( '-', ' ', $control_type ) ) );

			$field_args = array(
				// Add a control to the field at the same time
				'control' => array(
					'type'    => $control_type,
					'id'      => $this->id . '-' . $id,
					'section' => $this->id . '-example-my-fields',
					'label'   => $label,
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
			}

			$wp_fields->add_field( $this->object_type, $id, $this->object_name, $field_args );
		}

	}

	/**
	 * Handle saving of fields
	 *
	 * @param int|null    $item_id     Item ID
	 * @param string|null $object_name Object name
	 */
	public function save_fields( $item_id = null, $object_name = null ) {

		$screen_nonce = $this->object_type . '_' . $this->id;

		if ( ! empty( $_REQUEST['wp_fields_api_fields_save'] ) && false !== wp_verify_nonce( $_REQUEST['wp_fields_api_fields_save'], $screen_nonce ) ) {
			/**
			 * @var $wp_fields WP_Fields_API
			 */
			global $wp_fields;

			$controls = $wp_fields->get_controls( $this->object_type, $object_name );

			foreach ( $controls as $control ) {
				if ( empty( $control->field ) ) {
					continue;
				}

				// Pass $object_name and $item_id into control
				$control->object_name = $object_name;
				$control->item_id = $item_id;

				$field = $control->field;

				// Pass $object_name and $item_id into field
				$field->object_name = $object_name;

				// Get value from $_POST
				$value = null;

				if ( ! empty( $_POST[ 'field_' . $control->id ] ) ) {
					$value = $_POST[ 'field_' . $control->id ];
				}

				// Sanitize
				$value = $field->sanitize( $value );

				// Save value
				$field->save( $value, $item_id );
			}
		}

	}

	/**
	 * Render screen for implementation
	 */
	protected function render() {

		/**
		 * @var $wp_fields WP_Fields_API
		 */
		global $wp_fields;

		$screen_nonce = $this->object_type . '_' . $this->id;

		wp_nonce_field( $screen_nonce, 'wp_fields_api_fields_save' );

		$sections = $wp_fields->get_sections( $this->object_type, $this->object_name, $this->id );

		if ( ! empty( $sections ) ) {
			?>
				<div class="screen-<?php echo esc_attr( $this->id ); ?>-wrap fields-api-screen">
					<?php
						foreach ( $sections as $section ) {
							$this->render_section( $section, $this->item_id, $this->object_name );
						}
					?>
				</div>
			<?php
		}

	}

	/**
	 * Render section for implementation
	 *
	 * @param WP_Fields_API_Section $section     Section object
	 * @param int|null              $item_id     Item ID
	 * @param string|null           $object_name Object name
	 */
	public function render_section( $section, $item_id = null, $object_name = null ) {

		/**
		 * @var $wp_fields WP_Fields_API
		 */
		global $wp_fields;

		// Pass $object_name and $item_id to Section
		$section->object_name = $object_name;
		$section->item_id     = $item_id;

		$controls = $wp_fields->get_controls( $this->object_type, $section->object_name, $section->id );

		if ( ! empty( $controls ) ) {
			$content = $section->get_content();

			if ( $content && $section->display_title ) {
				?>
				<h3><?php echo $content; ?></h3>
				<?php
			}

			?>
			<table class="form-table section-<?php echo esc_attr( $section->id ); ?>-wrap fields-api-section">
				<?php
					foreach ( $controls as $control ) {
						$this->render_control( $control, $item_id, $section->object_name );
					}
				?>
			</table>
			<?php
		}

	}

	/**
	 * Render control for implementation
	 *
	 * @param WP_Fields_API_Control $control     Control object
	 * @param int|null              $item_id     Item ID
	 * @param string|null           $object_name Object name
	 */
	public function render_control( $control, $item_id = null, $object_name = null ) {

		// Pass $object_name and $item_id to Control
		$control->object_name = $object_name;
		$control->item_id     = $item_id;

		$label       = trim( $control->label );
		$description = trim( $control->description );

		// Avoid outputting them in render_content()
		$control->label       = '';
		$control->description = '';

		// Setup field id / name
		$control->input_attrs['id']   = 'field-' . $control->id;
		$control->input_attrs['name'] = 'field_' . $control->id;
		?>
			<tr class="field-<?php echo esc_attr( $control->id ); ?>-wrap fields-api-control">
				<th>
					<?php if ( 0 < strlen( $label ) ) { ?>
						<label for="field-<?php echo esc_attr( $control->id ); ?>"><?php echo esc_html( $label ); ?></label>
					<?php } ?>
				</th>
				<td>
					<?php $control->render_content(); ?>

					<?php if ( 0 < strlen( $description ) ) { ?>
						<p class="description"><?php echo wp_kses_post( $description ); ?></p>
					<?php } ?>
				</td>
			</tr>
		<?php

	}

}
