<?php
/**
 * WordPress Fields API Form class
 *
 * @package WordPress
 * @subpackage Fields_API
 */

/**
 * Fields API Form class.
 *
 * A UI container for sections, managed by WP_Fields_API.
 *
 * @see WP_Fields_API
 */
class WP_Fields_API_Form {

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
	 * Priority of the form, defining the display order of forms and sections.
	 *
	 * @access public
	 * @var integer
	 */
	public $priority = 160;

	/**
	 * Capability required for the form.
	 *
	 * @access public
	 * @var string
	 */
	public $capability = 'edit_theme_options';

	/**
	 * Theme feature support for the form.
	 *
	 * @access public
	 * @var string|array
	 */
	public $theme_supports = '';

	/**
	 * Title of the form to show in UI.
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
	 * Fields API sections for this form.
	 *
	 * @access public
	 * @var array<WP_Fields_API_Section>
	 */
	public $sections = array();

	/**
	 * Type of this form.
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
	 * @see WP_Fields_API_Form::check_capabilities()
	 *
	 * @var callable Callback is called with one argument, the instance of
	 *               WP_Fields_API_Form, and returns bool to indicate whether
	 *               the form has capabilities to be used.
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
	 * @param string $id            A specific ID of the form.
	 * @param array  $args          Form arguments.
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
	 * Check whether form is active to current Fields API preview.
	 *
	 * @access public
	 *
	 * @return bool Whether the form is active to the current preview.
	 */
	final public function active() {

		$form = $this;
		$active = true;

		if ( is_callable( $this->active_callback ) ) {
			$active = call_user_func( $this->active_callback, $this );
		}

		/**
		 * Filter response of WP_Fields_API_Form::active().
		 *
		 *
		 * @param bool                $active  Whether the Fields API form is active.
		 * @param WP_Fields_API_Form $form   {@see WP_Fields_API_Form} instance.
		 */
		$active = apply_filters( 'fields_api_form_active_' . $this->object_type, $active, $form );

		return $active;

	}

	/**
	 * Default callback used when invoking {@see WP_Fields_API_Form::active()}.
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
	 * feature support required by the form.
	 *
	 * @return bool False if theme doesn't support the form or user can't change form, otherwise true.
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
	 * Get the form's content template for insertion into the Fields API form.
	 *
	 * @return string Content for the form.
	 */
	final public function get_content() {

		ob_start();

		$this->maybe_render();

		$template = trim( ob_get_contents() );

		ob_end_clean();

		return $template;

	}

	/**
	 * Check capabilities and render the form.
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
		 * Fires before rendering a Fields API form.
		 *
		 * @param WP_Fields_API_Form $this WP_Fields_API_Form instance.
		 */
		do_action( "fields_api_render_form_{$this->object_type}", $this );

		/**
		 * Fires before rendering a specific Fields API form.
		 *
		 * The dynamic portion of the hook name, `$this->id`, refers to
		 * the ID of the specific Fields API form to be rendered.
		 */
		do_action( "fields_api_render_form_{$this->object_type}_{$this->object_name}_{$this->id}" );

		$this->render();

	}

	/**
	 * Render the form's JS templates.
	 *
	 * This function is only run for form types that have been registered with
	 * WP_Fields_API::register_form_type().
	 *
	 * @see WP_Fields_API::register_form_type()
	 */
	public function print_template() {

		// Nothing by default

	}

	/**
	 * An Underscore (JS) template for rendering this form's container.
	 *
	 * Class variables for this form class are available in the `data` JS object;
	 * export custom variables by overriding WP_Fields_API_Form::json().
	 *
	 * @see WP_Fields_API_Form::print_template()
	 *
	 * @since 4.3.0
	 * @access protected
	 */
	protected function render_template() {

		// Nothing by default

	}

	/**
	 * An Underscore (JS) template for this form's content
	 *
	 * Class variables for this control class are available in the `data` JS object;
	 * export custom variables by overriding {@see WP_Fields_API_Form::to_json()}.
	 *
	 * @see WP_Fields_API_Form::print_template()
	 *
	 * @access protected
	 */
	protected function content_template() {

		// Nothing by default

	}

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
		 * @var $form    WP_Fields_API_Form
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

	}

	/**
	 * Encapsulated registering of sections, controls, and fields for a form
	 *
	 * @param WP_Fields_API $wp_fields
	 */
	public function register_fields( $wp_fields ) {

		if ( ! defined( 'WP_FIELDS_API_EXAMPLES' ) || ! WP_FIELDS_API_EXAMPLES ) {
			return;
		}

		//////////////
		// Examples //
		//////////////

		// Section
		$section_args = array(
			'title' => __( 'Fields API Example - My Fields' ),
		    'form' => $this->id,
		);

		if ( in_array( $this->object_type, array( 'post', 'comment' ) ) ) {
			$section_args['type'] = 'meta-box';
		}

		$wp_fields->add_section( $this->object_type, $this->id . '-example-my-fields', $this->object_name, $section_args );

		// Add example for each control type
		$control_types = array(
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
	 *
	 * @return int|WP_Error|null New item ID, WP_Error if there was a problem, null if no $item_id used
	 */
	public function save_fields( $item_id = null, $object_name = null ) {

		$form_nonce = $this->object_type . '_' . $this->id;

		if ( ! empty( $_REQUEST['wp_fields_api_fields_save'] ) && false !== wp_verify_nonce( $_REQUEST['wp_fields_api_fields_save'], $form_nonce ) ) {
			/**
			 * @var $wp_fields WP_Fields_API
			 */
			global $wp_fields;

			$controls = $wp_fields->get_controls( $this->object_type, $object_name );

			foreach ( $controls as $control ) {
				if ( empty( $control->field ) || $control->internal ) {
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

				$input_name = $control->id;

				if ( ! empty( $control->input_name ) ) {
					$input_name = $control->input_name;
				}

				if ( ! empty( $_POST[ $input_name ] ) ) {
					$value = $_POST[ $input_name ];
				}

				// Sanitize
				$value = $field->sanitize( $value );

				// Save value
				$field->save( $value, $item_id );
			}
		}

		return $item_id;

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

		$sections = $wp_fields->get_sections( $this->object_type, $this->object_name, $this->id );

		if ( ! empty( $sections ) ) {
			?>
				<div class="fields-form-<?php echo esc_attr( $this->object_type ); ?> form-<?php echo esc_attr( $this->id ); ?>-wrap fields-api-form">
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
	 * Render section and it's controls
	 *
	 * @param WP_Fields_API_Section $section     Section object
	 * @param null|int              $item_id     Item ID
	 * @param null|string           $object_name Object name
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
			?>
			<div class="fields-form-<?php echo esc_attr( $this->object_type ); ?>-section section-<?php echo esc_attr( $section->id ); ?>-wrap fields-api-section">
				<?php
					if ( $content && $section->display_title ) {
						?>
						<h3><?php echo $content; ?></h3>
						<?php
					}

					$this->render_controls( $controls, $item_id, $section->object_name );
				?>
			</div>
			<?php
		}

	}

	/**
	 * Render controls
	 *
	 * @param WP_Fields_API_Control[] $controls    Control objects
	 * @param null|int                $item_id     Item ID
	 * @param null|string             $object_name Object name
	 */
	public function render_controls( $controls, $item_id = null, $object_name = null ) {

		foreach ( $controls as $control ) {
			$this->render_control( $control, $item_id, $object_name );
		}

	}

	/**
	 * Render control wrapper, label, description, and control input
	 *
	 * @param WP_Fields_API_Control $control     Control object
	 * @param null|int              $item_id     Item ID
	 * @param null|string           $object_name Object name
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

		$input_id = 'field-' . $control->id;

		if ( isset( $control->input_attrs['id'] ) ) {
			$input_id = $control->input_attrs['id'];
		}
		?>
			<div <?php $control->wrap_attrs(); ?>>
				<?php if ( 0 < strlen( $label ) ) { ?>
					<label for="<?php echo esc_attr( $input_id ); ?>"><?php echo esc_html( $label ); ?></label>
				<?php } ?>

				<?php $control->render_content(); ?>

				<?php if ( 0 < strlen( $description ) ) { ?>
					<p class="description"><?php echo wp_kses_post( $description ); ?></p>
				<?php } ?>
			</div>
		<?php

	}

}
