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
		$active = call_user_func( $this->active_callback, $this );

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
	 */
	final public function maybe_render() {

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
	 * Render the screen container, and then its contents.
	 *
	 * @access protected
	 */
	protected function render() {
		$classes = 'accordion-section control-section control-screen control-screen-' . $this->type;
		?>
		<li id="accordion-screen-<?php echo esc_attr( $this->id ); ?>" class="<?php echo esc_attr( $classes ); ?>">
			<h3 class="accordion-section-title" tabindex="0">
				<?php echo esc_html( $this->title ); ?>
				<span class="screen-reader-text"><?php _e( 'Press return or enter to open this screen' ); ?></span>
			</h3>
			<ul class="accordion-sub-container control-screen-content">
				<?php $this->render_content(); ?>
			</ul>
		</li>
		<?php
	}

	/**
	 * Render the screen's JS templates.
	 *
	 * This function is only run for screen types that have been registered with
	 * WP_Fields_API::register_panel_type().
	 *
	 * @see WP_Fields_API::register_panel_type()
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
	 * Render the sections that have been added to the screen.
	 *
	 * @access protected
	 */
	protected function render_content() {
		?>
		<li class="screen-meta accordion-section control-section<?php if ( empty( $this->description ) ) { echo ' cannot-expand'; } ?>">
			<div class="accordion-section-title" tabindex="0">
				<span class="preview-notice"><?php
					/* translators: %s is the site/screen title in the Fields API */
					printf( __( 'You are editing %s' ), '<strong class="screen-title">' . esc_html( $this->title ) . '</strong>' );
				?></span>
			</div>
			<?php if ( ! empty( $this->description ) ) : ?>
				<div class="accordion-section-content description">
					<?php echo $this->description; ?>
				</div>
			<?php endif; ?>
		</li>
		<?php
	}
}
