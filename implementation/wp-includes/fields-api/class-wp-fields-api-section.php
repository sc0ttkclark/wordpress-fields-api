<?php
/**
 * WordPress Fields API Section classes
 *
 * @package WordPress
 * @subpackage Customize
 * @since 3.4.0
 */

/**
 * Fields API Section class.
 *
 * A UI container for controls, managed by the WP_Fields_API.
 */
class WP_Fields_API_Section {

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
	 * Whether or not to display the heading title for section on render
	 *
	 * @access public
	 * @var bool
	 */
	public $display_title = true;

	/**
	 * Priority of the section which informs load order of sections.
	 *
	 * @access public
	 * @var integer
	 */
	public $priority = 160;

	/**
	 * Screen in which to show the section, making it a sub-section.
	 *
	 * @access public
	 * @var string|WP_Fields_API_Screen
	 */
	public $screen = '';

	/**
	 * Capability required for the section.
	 *
	 * @access public
	 * @var string
	 */
	public $capability = 'edit_theme_options';

	/**
	 * Theme feature support for the section.
	 *
	 * @access public
	 * @var string|array
	 */
	public $theme_supports = '';

	/**
	 * Title of the section to show in UI.
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
	 * Fields API controls for this section.
	 *
	 * @access public
	 * @var array<WP_Fields_API_Controls>
	 */
	public $controls = array();

	/**
	 * Type of this section.
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
	 * @see WP_Fields_API_Section::check_capabilities()
	 *
	 * @var callable Callback is called with one argument, the instance of
	 *               WP_Fields_API_Section, and returns bool to indicate whether
	 *               the section has capabilities to be used.
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
	 * @param string $id            A specific ID of the section.
	 * @param array  $args          Section arguments.
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

		/*if ( $this->screen ) {
			$screen_obj = $wp_fields->get_screen( $this->object_type, $this->screen, $this->object_name );

			if ( $screen_obj ) {
				$this->screen = $screen_obj;
			}
		}*/

		$this->controls = array(); // Users cannot customize the $controls array.

	}

	/**
	 * Check whether section is active to current Fields API preview.
	 *
	 * @since 4.1.0
	 * @access public
	 *
	 * @return bool Whether the section is active to the current preview.
	 */
	final public function active() {

		$section = $this;
		$active = true;

		if ( is_callable( $this->active_callback ) ) {
			$active = call_user_func( $this->active_callback, $this );
		}

		/**
		 * Filter response of {@see WP_Fields_API_Section::active()}.
		 *
		 * @param bool                 $active  Whether the Fields API section is active.
		 * @param WP_Fields_API_Section $section {@see WP_Fields_API_Section} instance.
		 */
		$active = apply_filters( 'fields_api_section_active_' . $this->object_type, $active, $section );

		return $active;

	}

	/**
	 * Default callback used when invoking {@see WP_Fields_API_Section::active()}.
	 *
	 * Subclasses can override this with their specific logic, or they may provide
	 * an 'active_callback' argument to the constructor.
	 *
	 * @since 4.1.0
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

		$array = wp_array_slice_assoc( (array) $this, array( 'id', 'description', 'priority', 'screen', 'type' ) );
		$array['title'] = html_entity_decode( $this->title, ENT_QUOTES, get_bloginfo( 'charset' ) );
		$array['content'] = $this->get_content();
		$array['active'] = $this->active();
		$array['instanceNumber'] = $this->instance_number;

		return $array;

	}

	/**
	 * Checks required user capabilities and whether the theme has the
	 * feature support required by the section.
	 *
	 * @return bool False if theme doesn't support the section or user can't change section, otherwise true.
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
	 * Get the section's content template for insertion into the Fields UI.
	 *
	 * @return string Contents of the section.
	 */
	final public function get_content() {

		ob_start();

		$this->maybe_render();

		$template = trim( ob_get_contents() );

		ob_end_clean();

		return $template;

	}

	/**
	 * Check capabilities and render the section.
	 */
	final public function maybe_render() {

		if ( ! $this->check_capabilities() ) {
			return;
		}

		/**
		 * Fires before rendering a Fields API section.
		 *
		 * The dynamic portion of the hook name, `$this->object_type`, refers to the object
		 * of the specific Fields API section to be rendered.
		 *
		 * @param WP_Fields_API_Section $this WP_Fields API_Section instance.
		 */
		do_action( "fields_api_render_section_{$this->object_type}", $this );

		/**
		 * Fires before rendering a specific Fields API section.
		 *
		 * The dynamic portion of the hook name, `$this->object_type`, refers to the ID
		 * of the specific Fields API section to be rendered.
		 *
		 * The dynamic portion of the hook name, `$this->id`, refers to the ID
		 * of the specific Fields API section to be rendered.
		 *
		 */
		do_action( "fields_api_render_section_{$this->object_type}_{$this->object_name}_{$this->id}" );

		$this->render();

	}

	/**
	 * Render the section, and the controls that have been added to it.
	 */
	protected function render() {
		echo esc_html( $this->title );
	}

	/**
	 * Render the section's JS template.
	 *
	 * This function is only run for section types that have been registered with
	 * WP_Fields_API::register_section_type().
	 *
	 * @since 4.3.0
	 * @access public
	 *
	 * @see WP_Fields_API_Section::render_template()
	 */
	public function print_template() {

		// Nothing by default

	}

	/**
	 * An Underscore (JS) template for rendering this section.
	 *
	 * Class variables for this section class are available in the `data` JS object;
	 * export custom variables by overriding WP_Fields_API_Section::json().
	 *
	 * @since 4.3.0
	 * @access protected
	 *
	 * @see WP_Fields_API_Section::print_template()
	 */
	protected function render_template() {

		// Nothing by default

	}

}