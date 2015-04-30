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
	public $instance_number;

	/**
	 * WP_Customize_Manager instance.
	 *
	 * @access public
	 * @var WP_Customize_Manager
	 */
	public $manager;

	/**
	 * Unique identifier.
	 *
	 * @access public
	 * @var string
	 */
	public $id;

	/**
	 * Priority of the section which informs load order of sections.
	 *
	 * @access public
	 * @var integer
	 */
	public $priority = 160;

	/**
	 * Panel in which to show the section, making it a sub-section.
	 *
	 * @access public
	 * @var string
	 */
	public $panel = '';

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
	 * Customizer controls for this section.
	 *
	 * @access public
	 * @var array
	 */
	public $controls;

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
	 * Constructor.
	 *
	 * Parameters are not set to maintain PHP overloading compatibility (strict standards)
	 *
	 * @return WP_Fields_API_Section $setting
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
	 * @param array  $args                  Section arguments.
	 *
	 * @return WP_Fields_API_Section $setting
	 */
	public function init( $object, $id, $args = array() ) {

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

		if ( empty( $this->active_callback ) ) {
			$this->active_callback = array( $this, 'active_callback' );
		}

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
		$active = call_user_func( $this->active_callback, $this );

		/**
		 * Filter response of {@see WP_Fields_API_Section::active()}.
		 *
		 * @param bool                 $active  Whether the Fields API section is active.
		 * @param WP_Fields_API_Section $section {@see WP_Fields_API_Section} instance.
		 */
		$active = apply_filters( 'fields_api_section_active_' . $this->object, $active, $section );

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
	 *
	 * @return array The array to be exported to the client as JSON.
	 */
	public function json() {
		$array = wp_array_slice_assoc( (array) $this, array( 'title', 'description', 'priority', 'panel', 'type' ) );
		$array['content'] = $this->get_content();
		$array['instanceNumber'] = $this->instance_number;
		$array['active'] = $this->active();
		return $array;
	}

	/**
	 * Checks required user capabilities and whether the theme has the
	 * feature support required by the section.
	 *
	 *
	 * @return bool False if theme doesn't support the section or user doesn't have the capability.
	 */
	public function check_capabilities() {
		if ( $this->capability && ! call_user_func_array( 'current_user_can', (array) $this->capability ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Get the section's content template for insertion into the Fields UI.
	 *
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
		 * The dynamic portion of the hook name, `$this->object`, refers to the object
		 * of the specific Fields API section to be rendered.
		 *
		 * @param WP_Fields API_Section $this WP_Fields API_Section instance.
		 */
		do_action( "fields_api_render_section_{$this->object}", $this );

		/**
		 * Fires before rendering a specific Fields API section.
		 *
		 * The dynamic portion of the hook name, `$this->object`, refers to the ID
		 * of the specific Fields API section to be rendered.
		 *
		 * The dynamic portion of the hook name, `$this->id`, refers to the ID
		 * of the specific Fields API section to be rendered.
		 *
		 */
		do_action( "fields_api_render_section_{$this->object}_{$this->id}" );

		$this->render();

	}

	/**
	 * Render the section, and the controls that have been added to it.
	 */
	protected function render() {
		$classes = 'accordion-section control-section control-section-' . $this->type;
		?>
		<li id="accordion-section-<?php echo esc_attr( $this->id ); ?>" class="<?php echo esc_attr( $classes ); ?>">
			<h3 class="accordion-section-title" tabindex="0">
				<?php echo esc_html( $this->title ); ?>
				<span class="screen-reader-text"><?php _e( 'Press return or enter to expand' ); ?></span>
			</h3>
			<ul class="accordion-section-content">
				<?php if ( ! empty( $this->description ) ) : ?>
					<li class="fields-section-description-container">
						<p class="description fields-section-description"><?php echo $this->description; ?></p>
					</li>
				<?php endif; ?>
			</ul>
		</li>
		<?php
	}
}