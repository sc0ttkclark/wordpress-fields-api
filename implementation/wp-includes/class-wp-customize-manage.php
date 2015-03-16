<?php
/**
 * Customize Manager.
 *
 * Bootstraps the Customize experience on the server-side.
 *
 * Sets up the theme-switching process if a theme other than the active one is
 * being previewed and customized.
 *
 * Serves as a factory for Customize Controls and Settings, and
 * instantiates default Customize Controls and Settings.
 *
 * @package WordPress
 * @subpackage Customize
 * @since 3.4.0
 */
final class WP_Customize_Manager {
	/**
	 * An instance of the theme being previewed.
	 *
	 * @var WP_Theme
	 */
	protected $theme;

	/**
	 * The directory name of the previously active theme (within the theme_root).
	 *
	 * @var string
	 */
	protected $original_stylesheet;

	/**
	 * Whether this is a Customizer pageload.
	 *
	 * @var boolean
	 */
	protected $previewing = false;

	/**
	 * Methods and properties deailing with managing widgets in the Customizer.
	 *
	 * @var WP_Customize_Widgets
	 */
	public $widgets;

	// Removed for implementation
	//protected $settings   = array();
	//protected $containers = array();
	//protected $panels     = array();
	//protected $sections   = array();
	//protected $controls   = array();

	protected $nonce_tick;

	protected $customized;

	/**
	 * Controls that may be rendered from JS templates.
	 *
	 * @since 4.1.0
	 * @access protected
	 * @var array
	 */
	// Removed for implementation
	//protected $registered_control_types = array();

	/**
	 * Unsanitized values for Customize Settings parsed from $_POST['customized'].
	 *
	 * @var array|false
	 */
	private $_post_values;

	/**
	 * Constructor.
	 *
	 * @since 3.4.0
	 */
	public function __construct() {
		require_once( ABSPATH . WPINC . '/class-wp-customize-setting.php' );
		require_once( ABSPATH . WPINC . '/class-wp-customize-panel.php' );
		require_once( ABSPATH . WPINC . '/class-wp-customize-section.php' );
		require_once( ABSPATH . WPINC . '/class-wp-customize-control.php' );
		require_once( ABSPATH . WPINC . '/class-wp-customize-widgets.php' );

		$this->widgets = new WP_Customize_Widgets( $this );

		add_filter( 'wp_die_handler', array( $this, 'wp_die_handler' ) );

		add_action( 'setup_theme',  array( $this, 'setup_theme' ) );
		add_action( 'wp_loaded',    array( $this, 'wp_loaded' ) );

		// Run wp_redirect_status late to make sure we override the status last.
		add_action( 'wp_redirect_status', array( $this, 'wp_redirect_status' ), 1000 );

		// Do not spawn cron (especially the alternate cron) while running the Customizer.
		remove_action( 'init', 'wp_cron' );

		// Do not run update checks when rendering the controls.
		remove_action( 'admin_init', '_maybe_update_core' );
		remove_action( 'admin_init', '_maybe_update_plugins' );
		remove_action( 'admin_init', '_maybe_update_themes' );

		add_action( 'wp_ajax_customize_save', array( $this, 'save' ) );

		add_action( 'customize_register',                 array( $this, 'register_controls' ) );
		add_action( 'customize_controls_init',            array( $this, 'prepare_controls' ) );
		add_action( 'customize_controls_enqueue_scripts', array( $this, 'enqueue_control_scripts' ) );
	}

	/**
	 * Return true if it's an AJAX request.
	 *
	 * @since 3.4.0
	 *
	 * @return bool
	 */
	public function doing_ajax() {
		return isset( $_POST['customized'] ) || ( defined( 'DOING_AJAX' ) && DOING_AJAX );
	}

	/**
	 * Custom wp_die wrapper. Returns either the standard message for UI
	 * or the AJAX message.
	 *
	 * @since 3.4.0
	 *
	 * @param mixed $ajax_message AJAX return
	 * @param mixed $message UI message
	 */
	protected function wp_die( $ajax_message, $message = null ) {
		if ( $this->doing_ajax() )
			wp_die( $ajax_message );

		if ( ! $message )
			$message = __( 'Cheatin&#8217; uh?' );

		wp_die( $message );
	}

	/**
	 * Return the AJAX wp_die() handler if it's a customized request.
	 *
	 * @since 3.4.0
	 *
	 * @return string
	 */
	public function wp_die_handler() {
		if ( $this->doing_ajax() )
			return '_ajax_wp_die_handler';

		return '_default_wp_die_handler';
	}

	/**
	 * Start preview and customize theme.
	 *
	 * Check if customize query variable exist. Init filters to filter the current theme.
	 *
	 * @since 3.4.0
	 */
	public function setup_theme() {
		send_origin_headers();

		if ( is_admin() && ! $this->doing_ajax() )
		    auth_redirect();
		elseif ( $this->doing_ajax() && ! is_user_logged_in() )
		    $this->wp_die( 0 );

		show_admin_bar( false );

		if ( ! current_user_can( 'customize' ) ) {
			$this->wp_die( -1 );
		}

		$this->original_stylesheet = get_stylesheet();

		$this->theme = wp_get_theme( isset( $_REQUEST['theme'] ) ? $_REQUEST['theme'] : null );

		if ( $this->is_theme_active() ) {
			// Once the theme is loaded, we'll validate it.
			add_action( 'after_setup_theme', array( $this, 'after_setup_theme' ) );
		} else {
			// If the requested theme is not the active theme and the user doesn't have the
			// switch_themes cap, bail.
			if ( ! current_user_can( 'switch_themes' ) )
				$this->wp_die( -1 );

			// If the theme has errors while loading, bail.
			if ( $this->theme()->errors() )
				$this->wp_die( -1 );

			// If the theme isn't allowed per multisite settings, bail.
			if ( ! $this->theme()->is_allowed() )
				$this->wp_die( -1 );
		}

		$this->start_previewing_theme();
	}

	/**
	 * Callback to validate a theme once it is loaded
	 *
	 * @since 3.4.0
	 */
	public function after_setup_theme() {
		if ( ! $this->doing_ajax() && ! validate_current_theme() ) {
			wp_redirect( 'themes.php?broken=true' );
			exit;
		}
	}

	/**
	 * If the theme to be previewed isn't the active theme, add filter callbacks
	 * to swap it out at runtime.
	 *
	 * @since 3.4.0
	 */
	public function start_previewing_theme() {
		// Bail if we're already previewing.
		if ( $this->is_preview() )
			return;

		$this->previewing = true;

		if ( ! $this->is_theme_active() ) {
			add_filter( 'template', array( $this, 'get_template' ) );
			add_filter( 'stylesheet', array( $this, 'get_stylesheet' ) );
			add_filter( 'pre_option_current_theme', array( $this, 'current_theme' ) );

			// @link: https://core.trac.wordpress.org/ticket/20027
			add_filter( 'pre_option_stylesheet', array( $this, 'get_stylesheet' ) );
			add_filter( 'pre_option_template', array( $this, 'get_template' ) );

			// Handle custom theme roots.
			add_filter( 'pre_option_stylesheet_root', array( $this, 'get_stylesheet_root' ) );
			add_filter( 'pre_option_template_root', array( $this, 'get_template_root' ) );
		}

		/**
		 * Fires once the Customizer theme preview has started.
		 *
		 * @since 3.4.0
		 *
		 * @param WP_Customize_Manager $this WP_Customize_Manager instance.
		 */
		do_action( 'start_previewing_theme', $this );
	}

	/**
	 * Stop previewing the selected theme.
	 *
	 * Removes filters to change the current theme.
	 *
	 * @since 3.4.0
	 */
	public function stop_previewing_theme() {
		if ( ! $this->is_preview() )
			return;

		$this->previewing = false;

		if ( ! $this->is_theme_active() ) {
			remove_filter( 'template', array( $this, 'get_template' ) );
			remove_filter( 'stylesheet', array( $this, 'get_stylesheet' ) );
			remove_filter( 'pre_option_current_theme', array( $this, 'current_theme' ) );

			// @link: https://core.trac.wordpress.org/ticket/20027
			remove_filter( 'pre_option_stylesheet', array( $this, 'get_stylesheet' ) );
			remove_filter( 'pre_option_template', array( $this, 'get_template' ) );

			// Handle custom theme roots.
			remove_filter( 'pre_option_stylesheet_root', array( $this, 'get_stylesheet_root' ) );
			remove_filter( 'pre_option_template_root', array( $this, 'get_template_root' ) );
		}

		/**
		 * Fires once the Customizer theme preview has stopped.
		 *
		 * @since 3.4.0
		 *
		 * @param WP_Customize_Manager $this WP_Customize_Manager instance.
		 */
		do_action( 'stop_previewing_theme', $this );
	}

	/**
	 * Get the theme being customized.
	 *
	 * @since 3.4.0
	 *
	 * @return WP_Theme
	 */
	public function theme() {
		return $this->theme;
	}

	/**
	 * Get the registered settings.
	 *
	 * @since 3.4.0
	 *
	 * @return array
	 *
	 * @uses WP_Fields_API::get_settings
	 */
	public function settings() {

		/**
		 * @var WP_Fields_API $wp_fields
		 */
		global $wp_fields;

		return $wp_fields->get_settings( 'customizer' );

	}

	/**
	 * Get the registered controls.
	 *
	 * @since 3.4.0
	 *
	 * @return array
	 *
	 * @uses WP_Fields_API::get_controls
	 */
	public function controls() {

		/**
		 * @var WP_Fields_API $wp_fields
		 */
		global $wp_fields;

		return $wp_fields->get_controls( 'customizer' );

	}

	/**
	 * Get the registered containers.
	 *
	 * @since 4.0.0
	 *
	 * @return array
	 *
	 * @uses WP_Fields_API::get_containers
	 */
	public function containers() {

		/**
		 * @var WP_Fields_API $wp_fields
		 */
		global $wp_fields;

		return $wp_fields->get_containers( 'customizer' );

	}

	/**
	 * Get the registered sections.
	 *
	 * @since 3.4.0
	 *
	 * @return array
	 *
	 * @uses WP_Fields_API::get_sections
	 */
	public function sections() {

		/**
		 * @var WP_Fields_API $wp_fields
		 */
		global $wp_fields;

		return $wp_fields->get_sections( 'customizer' );

	}

	/**
	 * Get the registered panels.
	 *
	 * @since 4.0.0
	 * @access public
	 *
	 * @return array Panels.
	 *
	 * @uses WP_Fields_API::get_panels
	 */
	public function panels() {

		/**
		 * @var WP_Fields_API $wp_fields
		 */
		global $wp_fields;

		return $wp_fields->get_panels( 'customizer' );

	}

	/**
	 * Checks if the current theme is active.
	 *
	 * @since 3.4.0
	 *
	 * @return bool
	 */
	public function is_theme_active() {
		return $this->get_stylesheet() == $this->original_stylesheet;
	}

	/**
	 * Register styles/scripts and initialize the preview of each setting
	 *
	 * @since 3.4.0
	 */
	public function wp_loaded() {

		/**
		 * Fires once WordPress has loaded, allowing scripts and styles to be initialized.
		 *
		 * @since 3.4.0
		 *
		 * @param WP_Customize_Manager $this WP_Customize_Manager instance.
		 */
		do_action( 'customize_register', $this );

		if ( $this->is_preview() && ! is_admin() )
			$this->customize_preview_init();
	}

	/**
	 * Prevents AJAX requests from following redirects when previewing a theme
	 * by issuing a 200 response instead of a 30x.
	 *
	 * Instead, the JS will sniff out the location header.
	 *
	 * @since 3.4.0
	 *
	 * @param $status
	 * @return int
	 */
	public function wp_redirect_status( $status ) {
		if ( $this->is_preview() && ! is_admin() )
			return 200;

		return $status;
	}

	/**
	 * Parse the incoming $_POST['customized'] JSON data and store the unsanitized
	 * settings for subsequent post_value() lookups.
	 *
	 * @since 4.1.1
	 *
	 * @return array
	 */
	public function unsanitized_post_values() {
		if ( ! isset( $this->_post_values ) ) {
			if ( isset( $_POST['customized'] ) ) {
				$this->_post_values = json_decode( wp_unslash( $_POST['customized'] ), true );
			}

			if ( empty( $this->_post_values ) ) { // if not isset or of JSON error
				$this->_post_values = false;
			}
		}

		return $this->unsanitized_post_values();
	}

	/**
	 * Print JavaScript settings.
	 *
	 * @since 3.4.0
	 */
	public function customize_preview_init() {
		$this->nonce_tick = check_ajax_referer( 'preview-customize_' . $this->get_stylesheet(), 'nonce' );

		$this->prepare_controls();

		wp_enqueue_script( 'customize-preview' );
		add_action( 'wp', array( $this, 'customize_preview_override_404_status' ) );
		add_action( 'wp_head', array( $this, 'customize_preview_base' ) );
		add_action( 'wp_head', array( $this, 'customize_preview_html5' ) );
		add_action( 'wp_footer', array( $this, 'customize_preview_settings' ), 20 );
		add_action( 'shutdown', array( $this, 'customize_preview_signature' ), 1000 );
		add_filter( 'wp_die_handler', array( $this, 'remove_preview_signature' ) );

		$settings = $this->settings();

		foreach ( $settings as $setting ) {
			$setting->preview();
		}

		/**
		 * Fires once the Customizer preview has initialized and JavaScript
		 * settings have been printed.
		 *
		 * @since 3.4.0
		 *
		 * @param WP_Customize_Manager $this WP_Customize_Manager instance.
		 */
		do_action( 'customize_preview_init', $this );
	}

	/**
	 * Prevent sending a 404 status when returning the response for the customize
	 * preview, since it causes the jQuery AJAX to fail. Send 200 instead.
	 *
	 * @since 4.0.0
	 * @access public
	 */
	public function customize_preview_override_404_status() {
		if ( is_404() ) {
			status_header( 200 );
		}
	}

	/**
	 * Print base element for preview frame.
	 *
	 * @since 3.4.0
	 */
	public function customize_preview_base() {
		?><base href="<?php echo home_url( '/' ); ?>" /><?php
	}

	/**
	 * Print a workaround to handle HTML5 tags in IE < 9
	 *
	 * @since 3.4.0
	 */
	public function customize_preview_html5() { ?>
		<!--[if lt IE 9]>
		<script type="text/javascript">
			var e = [ 'abbr', 'article', 'aside', 'audio', 'canvas', 'datalist', 'details',
				'figure', 'footer', 'header', 'hgroup', 'mark', 'menu', 'meter', 'nav',
				'output', 'progress', 'section', 'time', 'video' ];
			for ( var i = 0; i < e.length; i++ ) {
				document.createElement( e[i] );
			}
		</script>
		<![endif]--><?php
	}

	/**
	 * Print JavaScript settings for preview frame.
	 *
	 * @since 3.4.0
	 */
	public function customize_preview_settings() {

		/**
		 * @var WP_Fields_API $wp_fields
		 */
		global $wp_fields;

		$settings = array(
			'values'  => array(),
			'channel' => wp_unslash( $_POST['customize_messenger_channel'] ),
			'activePanels' => array(),
			'activeSections' => array(),
			'activeControls' => array(),
		);

		if ( 2 == $this->nonce_tick ) {
			$settings['nonce'] = array(
				'save' => wp_create_nonce( 'save-customize_' . $this->get_stylesheet() ),
				'preview' => wp_create_nonce( 'preview-customize_' . $this->get_stylesheet() )
			);
		}

		$settings = $this->settings();

		foreach ( $settings as $id => $setting ) {
			if ( ! $wp_fields->is_prepared( 'customizer', 'setting', $id ) ) {
				continue;
			}

			$settings['values'][ $id ] = $setting->js_value();
		}

		$panels = $this->panels();

		foreach ( $panels as $id => $panel ) {
			if ( ! $wp_fields->is_prepared( 'customizer', 'panel', $id ) ) {
				continue;
			}

			$settings['activePanels'][ $id ] = $panel->active();

			foreach ( $panel->sections as $id => $section ) {
				$settings['activeSections'][ $id ] = $section->active();
			}
		}

		$sections = $this->sections();

		foreach ( $sections as $id => $section ) {
			if ( ! $wp_fields->is_prepared( 'customizer', 'section', $id ) ) {
				continue;
			}

			$settings['activeSections'][ $id ] = $section->active();
		}

		$controls = $this->controls();

		foreach ( $controls as $id => $control ) {
			if ( ! $wp_fields->is_prepared( 'customizer', 'control', $id ) ) {
				continue;
			}

			$settings['activeControls'][ $id ] = $control->active();
		}

		?>
		<script type="text/javascript">
			var _wpCustomizeSettings = <?php echo wp_json_encode( $settings ); ?>;
		</script>
		<?php
	}

	/**
	 * Prints a signature so we can ensure the Customizer was properly executed.
	 *
	 * @since 3.4.0
	 */
	public function customize_preview_signature() {
		echo 'WP_CUSTOMIZER_SIGNATURE';
	}

	/**
	 * Removes the signature in case we experience a case where the Customizer was not properly executed.
	 *
	 * @since 3.4.0
	 */
	public function remove_preview_signature( $return = null ) {
		remove_action( 'shutdown', array( $this, 'customize_preview_signature' ), 1000 );

		return $return;
	}

	/**
	 * Is it a theme preview?
	 *
	 * @since 3.4.0
	 *
	 * @return bool True if it's a preview, false if not.
	 */
	public function is_preview() {
		return (bool) $this->previewing;
	}

	/**
	 * Retrieve the template name of the previewed theme.
	 *
	 * @since 3.4.0
	 *
	 * @return string Template name.
	 */
	public function get_template() {
		return $this->theme()->get_template();
	}

	/**
	 * Retrieve the stylesheet name of the previewed theme.
	 *
	 * @since 3.4.0
	 *
	 * @return string Stylesheet name.
	 */
	public function get_stylesheet() {
		return $this->theme()->get_stylesheet();
	}

	/**
	 * Retrieve the template root of the previewed theme.
	 *
	 * @since 3.4.0
	 *
	 * @return string Theme root.
	 */
	public function get_template_root() {
		return get_raw_theme_root( $this->get_template(), true );
	}

	/**
	 * Retrieve the stylesheet root of the previewed theme.
	 *
	 * @since 3.4.0
	 *
	 * @return string Theme root.
	 */
	public function get_stylesheet_root() {
		return get_raw_theme_root( $this->get_stylesheet(), true );
	}

	/**
	 * Filter the current theme and return the name of the previewed theme.
	 *
	 * @since 3.4.0
	 *
	 * @param $current_theme {@internal Parameter is not used}
	 * @return string Theme name.
	 */
	public function current_theme( $current_theme ) {
		return $this->theme()->display('Name');
	}

	/**
	 * Switch the theme and trigger the save() method on each setting.
	 *
	 * @since 3.4.0
	 */
	public function save() {
		if ( ! $this->is_preview() )
			die;

		check_ajax_referer( 'save-customize_' . $this->get_stylesheet(), 'nonce' );

		// Do we have to switch themes?
		if ( ! $this->is_theme_active() ) {
			// Temporarily stop previewing the theme to allow switch_themes()
			// to operate properly.
			$this->stop_previewing_theme();
			switch_theme( $this->get_stylesheet() );
			update_option( 'theme_switched_via_customizer', true );
			$this->start_previewing_theme();
		}

		/**
		 * Fires once the theme has switched in the Customizer, but before settings
		 * have been saved.
		 *
		 * @since 3.4.0
		 *
		 * @param WP_Customize_Manager $this WP_Customize_Manager instance.
		 */
		do_action( 'customize_save', $this );

		$settings = $this->settings();

		foreach ( $settings as $setting ) {
			$setting->save();
		}

		/**
		 * Fires after Customize settings have been saved.
		 *
		 * @since 3.6.0
		 *
		 * @param WP_Customize_Manager $this WP_Customize_Manager instance.
		 */
		do_action( 'customize_save_after', $this );

		die;
	}

	/**
	 * Add a customize setting.
	 *
	 * @since 3.4.0
	 *
	 * @param WP_Customize_Setting|string $id Customize Setting object, or ID.
	 * @param array $args                     Setting arguments; passed to WP_Customize_Setting
	 *                                        constructor.
	 *
	 * @uses WP_Fields_API::add_setting
	 */
	public function add_setting( $id, $args = array() ) {

		/**
		 * @var WP_Fields_API $wp_fields
		 */
		global $wp_fields;

		$wp_fields->add_setting( 'customizer', $id, $args );

	}

	/**
	 * Retrieve a customize setting.
	 *
	 * @since 3.4.0
	 *
	 * @param string $id Customize Setting ID.
	 * @return WP_Customize_Setting
	 *
	 * @uses WP_Fields_API::get_setting
	 */
	public function get_setting( $id ) {

		/**
		 * @var WP_Fields_API $wp_fields
		 */
		global $wp_fields;

		return $wp_fields->get_setting( 'customizer', $id );

	}

	/**
	 * Remove a customize setting.
	 *
	 * @since 3.4.0
	 *
	 * @param string $id Customize Setting ID.
	 *
	 * @uses WP_Fields_API::remove_setting
	 */
	public function remove_setting( $id ) {

		/**
		 * @var WP_Fields_API $wp_fields
		 */
		global $wp_fields;

		$wp_fields->remove_setting( 'customizer', $id );

	}

	/**
	 * Add a customize panel.
	 *
	 * @since 4.0.0
	 * @access public
	 *
	 * @param WP_Customize_Panel|string $id   Customize Panel object, or Panel ID.
	 * @param array                     $args Optional. Panel arguments. Default empty array.
	 *
	 * @uses WP_Fields_API::add_panel
	 */
	public function add_panel( $id, $args = array() ) {

		/**
		 * @var WP_Fields_API $wp_fields
		 */
		global $wp_fields;

		$wp_fields->add_panel( 'customizer', $id, $args );

	}

	/**
	 * Retrieve a customize panel.
	 *
	 * @since 4.0.0
	 * @access public
	 *
	 * @param string $id Panel ID to get.
	 * @return WP_Customize_Panel Requested panel instance.
	 *
	 * @uses WP_Fields_API::get_panel
	 */
	public function get_panel( $id ) {

		/**
		 * @var WP_Fields_API $wp_fields
		 */
		global $wp_fields;

		return $wp_fields->get_panel( 'customizer', $id );

	}

	/**
	 * Remove a customize panel.
	 *
	 * @since 4.0.0
	 * @access public
	 *
	 * @param string $id Panel ID to remove.
	 *
	 * @uses WP_Fields_API::remove_panel
	 */
	public function remove_panel( $id ) {

		/**
		 * @var WP_Fields_API $wp_fields
		 */
		global $wp_fields;

		$wp_fields->remove_panel( 'customizer', $id );

	}

	/**
	 * Add a customize section.
	 *
	 * @since 3.4.0
	 *
	 * @param WP_Customize_Section|string $id   Customize Section object, or Section ID.
	 * @param array                       $args Section arguments.
	 *
	 * @uses WP_Fields_API::add_section
	 */
	public function add_section( $id, $args = array() ) {

		/**
		 * @var WP_Fields_API $wp_fields
		 */
		global $wp_fields;

		$wp_fields->add_section( 'customizer', $id, $args );

	}

	/**
	 * Retrieve a customize section.
	 *
	 * @since 3.4.0
	 *
	 * @param string $id Section ID.
	 * @return WP_Customize_Section
	 *
	 * @uses WP_Fields_API::get_section
	 */
	public function get_section( $id ) {

		/**
		 * @var WP_Fields_API $wp_fields
		 */
		global $wp_fields;

		return $wp_fields->get_section( 'customizer', $id );

	}

	/**
	 * Remove a customize section.
	 *
	 * @since 3.4.0
	 *
	 * @param string $id Section ID.
	 *
	 * @uses WP_Fields_API::remove_section
	 */
	public function remove_section( $id ) {

		/**
		 * @var WP_Fields_API $wp_fields
		 */
		global $wp_fields;

		$wp_fields->remove_section( 'customizer', $id );

	}

	/**
	 * Add a customize control.
	 *
	 * @since 3.4.0
	 *
	 * @param WP_Customize_Control|string $id   Customize Control object, or ID.
	 * @param array                       $args Control arguments; passed to WP_Customize_Control
	 *                                          constructor.
	 *
	 * @uses WP_Fields_API::add_control
	 */
	public function add_control( $id, $args = array() ) {

		/**
		 * @var WP_Fields_API $wp_fields
		 */
		global $wp_fields;

		$wp_fields->add_control( 'customizer', $id, $args );

	}

	/**
	 * Retrieve a customize control.
	 *
	 * @since 3.4.0
	 *
	 * @param string $id ID of the control.
	 * @return WP_Customize_Control $control The control object.
	 *
	 * @uses WP_Fields_API::get_control
	 */
	public function get_control( $id ) {

		/**
		 * @var WP_Fields_API $wp_fields
		 */
		global $wp_fields;

		return $wp_fields->get_control( 'customizer', $id );

	}

	/**
	 * Remove a customize control.
	 *
	 * @since 3.4.0
	 *
	 * @param string $id ID of the control.
	 *
	 * @uses WP_Fields_API::remove_control
	 */
	public function remove_control( $id ) {

		/**
		 * @var WP_Fields_API $wp_fields
		 */
		global $wp_fields;

		$wp_fields->remove_control( 'customizer', $id );

	}

	/**
	 * Register a customize control type.
	 *
	 * Registered types are eligible to be rendered via JS and created dynamically.
	 *
	 * @since 4.1.0
	 * @access public
	 *
	 * @param string $control Name of a custom control which is a subclass of
	 *                        {@see WP_Customize_Control}.
	 *
	 * @uses WP_Fields_API::register_control_type
	 */
	public function register_control_type( $control ) {

		/**
		 * @var WP_Fields_API $wp_fields
		 */
		global $wp_fields;

		$wp_fields->register_control_type( 'customizer', $control );

	}

	/**
	 * Render JS templates for all registered control types.
	 *
	 * @since 4.1.0
	 * @access public
	 *
	 * @uses WP_Fields_API::render_control_templates
	 */
	public function render_control_templates() {

		/**
		 * @var WP_Fields_API $wp_fields
		 */
		global $wp_fields;

		$wp_fields->render_control_templates();

	}

	/**
	 * Helper function to compare two objects by priority, ensuring sort stability via instance_number.
	 *
	 * @since 3.4.0
	 *
	 * @param {WP_Customize_Panel|WP_Customize_Section|WP_Customize_Control} $a Object A.
	 * @param {WP_Customize_Panel|WP_Customize_Section|WP_Customize_Control} $b Object B.
	 * @return int
	 *
	 * @deprecated Since 4.x
	 */
	protected final function _cmp_priority( $a, $b ) {
		if ( $a->priority === $b->priority ) {
			return $a->instance_number - $a->instance_number;
		} else {
			return $a->priority - $b->priority;
		}
	}

	/**
	 * Prepare panels, sections, and controls.
	 *
	 * For each, check if required related components exist,
	 * whether the user has the necessary capabilities,
	 * and sort by priority.
	 *
	 * @since 3.4.0
	 *
	 * @uses WP_Fields_API::prepare_controls
	 */
	public function prepare_controls() {

		/**
		 * @var WP_Fields_API $wp_fields
		 */
		global $wp_fields;

		$wp_fields->prepare_controls( 'customizer' );

	}

	/**
	 * Enqueue scripts for customize controls.
	 *
	 * @since 3.4.0
	 */
	public function enqueue_control_scripts() {

		/**
		 * @var WP_Fields_API $wp_fields
		 */
		global $wp_fields;

		$controls = $this->controls();

		foreach ( $controls as $control ) {
			if ( ! $wp_fields->is_prepared( 'customizer', 'control', $control->id ) ) {
				continue;
			}

			$control->enqueue();
		}

	}

	/**
	 * Register some default controls.
	 *
	 * @since 3.4.0
	 */
	public function register_controls() {

		/* Control Types (custom control classes) */
		$this->register_control_type( 'WP_Customize_Color_Control' );
		$this->register_control_type( 'WP_Customize_Upload_Control' );
		$this->register_control_type( 'WP_Customize_Image_Control' );
		$this->register_control_type( 'WP_Customize_Background_Image_Control' );

		/* Site Title & Tagline */

		$this->add_section( 'title_tagline', array(
			'title'    => __( 'Site Title & Tagline' ),
			'priority' => 20,
		) );

		$this->add_setting( 'blogname', array(
			'default'    => get_option( 'blogname' ),
			'type'       => 'option',
			'capability' => 'manage_options',
		) );

		$this->add_control( 'blogname', array(
			'label'      => __( 'Site Title' ),
			'section'    => 'title_tagline',
		) );

		$this->add_setting( 'blogdescription', array(
			'default'    => get_option( 'blogdescription' ),
			'type'       => 'option',
			'capability' => 'manage_options',
		) );

		$this->add_control( 'blogdescription', array(
			'label'      => __( 'Tagline' ),
			'section'    => 'title_tagline',
		) );

		/* Colors */

		$this->add_section( 'colors', array(
			'title'          => __( 'Colors' ),
			'priority'       => 40,
		) );

		$this->add_setting( 'header_textcolor', array(
			'theme_supports' => array( 'custom-header', 'header-text' ),
			'default'        => get_theme_support( 'custom-header', 'default-text-color' ),

			'sanitize_callback'    => array( $this, '_sanitize_header_textcolor' ),
			'sanitize_js_callback' => 'maybe_hash_hex_color',
		) );

		// Input type: checkbox
		// With custom value
		$this->add_control( 'display_header_text', array(
			'settings' => 'header_textcolor',
			'label'    => __( 'Display Header Text' ),
			'section'  => 'title_tagline',
			'type'     => 'checkbox',
		) );

		$this->add_control( new WP_Customize_Color_Control( $this, 'header_textcolor', array(
			'label'   => __( 'Header Text Color' ),
			'section' => 'colors',
		) ) );

		// Input type: Color
		// With sanitize_callback
		$this->add_setting( 'background_color', array(
			'default'        => get_theme_support( 'custom-background', 'default-color' ),
			'theme_supports' => 'custom-background',

			'sanitize_callback'    => 'sanitize_hex_color_no_hash',
			'sanitize_js_callback' => 'maybe_hash_hex_color',
		) );

		$this->add_control( new WP_Customize_Color_Control( $this, 'background_color', array(
			'label'   => __( 'Background Color' ),
			'section' => 'colors',
		) ) );


		/* Custom Header */

		$this->add_section( 'header_image', array(
			'title'          => __( 'Header Image' ),
			'theme_supports' => 'custom-header',
			'priority'       => 60,
		) );

		$this->add_setting( new WP_Customize_Filter_Setting( $this, 'header_image', array(
			'default'        => get_theme_support( 'custom-header', 'default-image' ),
			'theme_supports' => 'custom-header',
		) ) );

		$this->add_setting( new WP_Customize_Header_Image_Setting( $this, 'header_image_data', array(
			// 'default'        => get_theme_support( 'custom-header', 'default-image' ),
			'theme_supports' => 'custom-header',
		) ) );

		$this->add_control( new WP_Customize_Header_Image_Control( $this ) );

		/* Custom Background */

		$this->add_section( 'background_image', array(
			'title'          => __( 'Background Image' ),
			'theme_supports' => 'custom-background',
			'priority'       => 80,
		) );

		$this->add_setting( 'background_image', array(
			'default'        => get_theme_support( 'custom-background', 'default-image' ),
			'theme_supports' => 'custom-background',
		) );

		$this->add_setting( new WP_Customize_Background_Image_Setting( $this, 'background_image_thumb', array(
			'theme_supports' => 'custom-background',
		) ) );

		$this->add_control( new WP_Customize_Background_Image_Control( $this ) );

		$this->add_setting( 'background_repeat', array(
			'default'        => get_theme_support( 'custom-background', 'default-repeat' ),
			'theme_supports' => 'custom-background',
		) );

		$this->add_control( 'background_repeat', array(
			'label'      => __( 'Background Repeat' ),
			'section'    => 'background_image',
			'type'       => 'radio',
			'choices'    => array(
				'no-repeat'  => __('No Repeat'),
				'repeat'     => __('Tile'),
				'repeat-x'   => __('Tile Horizontally'),
				'repeat-y'   => __('Tile Vertically'),
			),
		) );

		$this->add_setting( 'background_position_x', array(
			'default'        => get_theme_support( 'custom-background', 'default-position-x' ),
			'theme_supports' => 'custom-background',
		) );

		$this->add_control( 'background_position_x', array(
			'label'      => __( 'Background Position' ),
			'section'    => 'background_image',
			'type'       => 'radio',
			'choices'    => array(
				'left'       => __('Left'),
				'center'     => __('Center'),
				'right'      => __('Right'),
			),
		) );

		$this->add_setting( 'background_attachment', array(
			'default'        => get_theme_support( 'custom-background', 'default-attachment' ),
			'theme_supports' => 'custom-background',
		) );

		$this->add_control( 'background_attachment', array(
			'label'      => __( 'Background Attachment' ),
			'section'    => 'background_image',
			'type'       => 'radio',
			'choices'    => array(
				'scroll'     => __('Scroll'),
				'fixed'      => __('Fixed'),
			),
		) );

		// If the theme is using the default background callback, we can update
		// the background CSS using postMessage.
		if ( get_theme_support( 'custom-background', 'wp-head-callback' ) === '_custom_background_cb' ) {
			foreach ( array( 'color', 'image', 'position_x', 'repeat', 'attachment' ) as $prop ) {
				$this->get_setting( 'background_' . $prop )->transport = 'postMessage';
			}
		}

		/* Nav Menus */

		$locations      = get_registered_nav_menus();
		$menus          = wp_get_nav_menus();
		$num_locations  = count( array_keys( $locations ) );

		$this->add_section( 'nav', array(
			'title'          => __( 'Navigation' ),
			'theme_supports' => 'menus',
			'priority'       => 100,
			'description'    => sprintf( _n('Your theme supports %s menu. Select which menu you would like to use.', 'Your theme supports %s menus. Select which menu appears in each location.', $num_locations ), number_format_i18n( $num_locations ) ) . "\n\n" . __('You can edit your menu content on the Menus screen in the Appearance section.'),
		) );

		if ( $menus ) {
			$choices = array( 0 => __( '&mdash; Select &mdash;' ) );
			foreach ( $menus as $menu ) {
				$choices[ $menu->term_id ] = wp_html_excerpt( $menu->name, 40, '&hellip;' );
			}

			foreach ( $locations as $location => $description ) {
				$menu_setting_id = "nav_menu_locations[{$location}]";

				$this->add_setting( $menu_setting_id, array(
					'sanitize_callback' => 'absint',
					'theme_supports'    => 'menus',
				) );

				$this->add_control( $menu_setting_id, array(
					'label'   => $description,
					'section' => 'nav',
					'type'    => 'select',
					'choices' => $choices,
				) );
			}
		}

		/* Static Front Page */
		// #WP19627

		$this->add_section( 'static_front_page', array(
			'title'          => __( 'Static Front Page' ),
		//	'theme_supports' => 'static-front-page',
			'priority'       => 120,
			'description'    => __( 'Your theme supports a static front page.' ),
		) );

		$this->add_setting( 'show_on_front', array(
			'default'        => get_option( 'show_on_front' ),
			'capability'     => 'manage_options',
			'type'           => 'option',
		//	'theme_supports' => 'static-front-page',
		) );

		$this->add_control( 'show_on_front', array(
			'label'   => __( 'Front page displays' ),
			'section' => 'static_front_page',
			'type'    => 'radio',
			'choices' => array(
				'posts' => __( 'Your latest posts' ),
				'page'  => __( 'A static page' ),
			),
		) );

		$this->add_setting( 'page_on_front', array(
			'type'       => 'option',
			'capability' => 'manage_options',
		//	'theme_supports' => 'static-front-page',
		) );

		$this->add_control( 'page_on_front', array(
			'label'      => __( 'Front page' ),
			'section'    => 'static_front_page',
			'type'       => 'dropdown-pages',
		) );

		$this->add_setting( 'page_for_posts', array(
			'type'           => 'option',
			'capability'     => 'manage_options',
		//	'theme_supports' => 'static-front-page',
		) );

		$this->add_control( 'page_for_posts', array(
			'label'      => __( 'Posts page' ),
			'section'    => 'static_front_page',
			'type'       => 'dropdown-pages',
		) );
	}

	/**
	 * Callback for validating the header_textcolor value.
	 *
	 * Accepts 'blank', and otherwise uses sanitize_hex_color_no_hash().
	 * Returns default text color if hex color is empty.
	 *
	 * @since 3.4.0
	 *
	 * @param string $color
	 * @return string
	 */
	public function _sanitize_header_textcolor( $color ) {
		if ( 'blank' === $color )
			return 'blank';

		$color = sanitize_hex_color_no_hash( $color );
		if ( empty( $color ) )
			$color = get_theme_support( 'custom-header', 'default-text-color' );

		return $color;
	}
}