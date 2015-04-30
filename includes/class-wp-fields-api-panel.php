<?php
/**
 * WordPress Fields API Panel classes
 *
 * @package WordPress
 * @subpackage Fields_API
 */

/**
 * Fields API Panel class.
 *
 * A UI container for sections, managed by WP_Fields_API.
 *
 * @see WP_Fields_API
 */
class WP_Fields_API_Panel {

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
	public $id;

	/**
	 * Priority of the panel, defining the display order of panels and sections.
	 *
	 * @access public
	 * @var integer
	 */
	public $priority = 160;

	/**
	 * Capability required for the panel.
	 *
	 * @access public
	 * @var string
	 */
	public $capability = 'edit_theme_options';

	/**
	 * Theme feature support for the panel.
	 *
	 * @access public
	 * @var string|array
	 */
	public $theme_supports = '';

	/**
	 * Title of the panel to show in UI.
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
	 * Fields API sections for this panel.
	 *
	 * @access public
	 * @var array
	 */
	public $sections;

	/**
	 * Type of this panel.
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

		$this->sections = array(); // Users cannot customize the $sections array.

	}

	/**
	 * Check whether panel is active to current Customizer preview.
	 *
	 * @access public
	 *
	 * @return bool Whether the panel is active to the current preview.
	 */
	final public function active() {

		$panel = $this;
		$active = call_user_func( $this->active_callback, $this );

		/**
		 * Filter response of WP_Customize_Panel::active().
		 *
		 *
		 * @param bool               $active  Whether the Customizer panel is active.
		 * @param WP_Customize_Panel $panel   {@see WP_Customize_Panel} instance.
		 */
		$active = apply_filters( 'fields_api_panel_active_' . $this->object, $active, $panel );

		return $active;

	}

	/**
	 * Default callback used when invoking {@see WP_Customize_Panel::active()}.
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

		$array = wp_array_slice_assoc( (array) $this, array( 'title', 'description', 'priority', 'type' ) );

		$array['content'] = $this->get_content();
		$array['active'] = $this->active();
		$array['instanceNumber'] = $this->instance_number;

		return $array;

	}

	/**
	 * Checks required user capabilities and whether the theme has the
	 * feature support required by the panel.
	 *
	 * @return bool False if the user doesn't have the capability.
	 */
	public function check_capabilities() {

		if ( $this->capability && ! call_user_func_array( 'current_user_can', (array) $this->capability ) ) {
			return false;
		}

		return true;

	}

	/**
	 * Get the panel's content template for insertion into the Customizer pane.
	 *
	 * @return string Content for the panel.
	 */
	final public function get_content() {

		ob_start();

		$this->maybe_render();

		$template = trim( ob_get_contents() );

		ob_end_clean();

		return $template;

	}

	/**
	 * Check capabilities and render the panel.
	 *
	 */
	final public function maybe_render() {

		if ( ! $this->check_capabilities() ) {
			return;
		}

		/**
		 * Fires before rendering a Customizer panel.
		 *
		 * @param WP_Customize_Panel $this WP_Customize_Panel instance.
		 */
		do_action( "fields_api_render_panel_{$this->object}", $this );

		/**
		 * Fires before rendering a specific Customizer panel.
		 *
		 * The dynamic portion of the hook name, `$this->id`, refers to
		 * the ID of the specific Customizer panel to be rendered.
		 */
		do_action( "fields_api_render_panel_{$this->object}_{$this->id}" );

		$this->render();

	}

	/**
	 * Render the panel container, and then its contents.
	 *
	 * @access protected
	 */
	protected function render() {
		$classes = 'accordion-section control-section control-panel control-panel-' . $this->type;
		?>
		<li id="accordion-panel-<?php echo esc_attr( $this->id ); ?>" class="<?php echo esc_attr( $classes ); ?>">
			<h3 class="accordion-section-title" tabindex="0">
				<?php echo esc_html( $this->title ); ?>
				<span class="screen-reader-text"><?php _e( 'Press return or enter to open this panel' ); ?></span>
			</h3>
			<ul class="accordion-sub-container control-panel-content">
				<?php $this->render_content(); ?>
			</ul>
		</li>
		<?php
	}

	/**
	 * Render the sections that have been added to the panel.
	 *
	 * @access protected
	 */
	protected function render_content() {
		?>
		<li class="panel-meta accordion-section control-section<?php if ( empty( $this->description ) ) { echo ' cannot-expand'; } ?>">
			<div class="accordion-section-title" tabindex="0">
				<span class="preview-notice"><?php
					/* translators: %s is the site/panel title in the Fields API */
					echo sprintf( __( 'You are editing %s' ), '<strong class="panel-title">' . esc_html( $this->title ) . '</strong>' );
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
