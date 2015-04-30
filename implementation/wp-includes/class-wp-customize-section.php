<?php
/**
 * WordPress Customize Section classes
 *
 * @package WordPress
 * @subpackage Customize
 * @since 3.4.0
 */

/**
 * Customize Section class.
 *
 * A UI container for controls, managed by the WP_Customize_Manager class.
 *
 * @since 3.4.0
 *
 * @see WP_Customize_Manager
 */
class WP_Customize_Section extends WP_Fields_API_Section {

	/**
	 * Constructor.
	 *
	 * Any supplied $args override class property defaults.
	 *
	 * @since 3.4.0
	 *
	 * @param WP_Customize_Manager $manager Customizer bootstrap instance.
	 * @param string               $id      An specific ID of the section.
	 * @param array                $args    Section arguments.
	 */
	public function __construct( $manager, $id, $args = array() ) {

		$this->manager = $manager;

		parent::__construct( $this->type, $id, $args );

		add_action( "fields_api_section_active_{$this->object}", array( $this, 'customize_section_active' ), 10, 2 );
		add_action( "fields_api_render_section_{$this->object}", array( $this, 'customize_render_section' ) );
		add_action( "fields_api_render_section_{$this->object}_{$this->id}", array( $this, 'customize_render_section_id' ) );

	}

	/**
	 * Check whether section is active to current Customizer preview.
	 *
	 * @access public
	 *
	 * @param bool                 $active  Whether the Fields API section is active.
	 * @param WP_Fields_API_Section $section {@see WP_Fields_API_Section} instance.
	 *
	 * @return bool Whether the section is active to the current preview.
	 */
	final public function customize_section_active( $active, $section ) {

		/**
		 * Filter response of {@see WP_Customize_Section::active()}.
		 *
		 * @since 4.1.0
		 *
		 * @param bool                 $active  Whether the Customizer section is active.
		 * @param WP_Customize_Section $section {@see WP_Customize_Section} instance.
		 */
		$active = apply_filters( 'customize_section_active', $active, $section );

		return $active;

	}

	/**
	 * Backwards compatibility for fields_api_render_section
	 */
	public function customize_render_section() {

		/**
		 * Fires before rendering a Customizer section.
		 *
		 * @since 3.4.0
		 *
		 * @param WP_Customize_Section $this WP_Customize_Section instance.
		 */
		do_action( 'customize_render_section', $this );

	}

	/**
	 * Backwards compatibility for fields_api_render_section
	 */
	public function customize_render_section_id() {

		/**
		 * Fires before rendering a specific Customizer section.
		 *
		 * The dynamic portion of the hook name, `$this->id`, refers to the ID
		 * of the specific Customizer section to be rendered.
		 *
		 * @since 3.4.0
		 */
		do_action( "customize_render_section_{$this->id}" );

	}

	/**
	 * Render the section, and the controls that have been added to it.
	 *
	 * @since 3.4.0
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
					<li class="customize-section-description-container">
						<p class="description customize-section-description"><?php echo $this->description; ?></p>
					</li>
				<?php endif; ?>
			</ul>
		</li>
		<?php
	}
}

/**
 * Customize Themes Section class.
 *
 * A UI container for theme controls, which behaves like a backwards Panel.
 *
 * @since 4.2.0
 *
 * @see WP_Customize_Section
 */
class WP_Customize_Themes_Section extends WP_Customize_Section {

	/**
	 * Customize section type.
	 *
	 * @since 4.2.0
	 * @access public
	 * @var string
	 */
	public $type = 'themes';

	/**
	 * Render the themes section, which behaves like a panel.
	 *
	 * @since 4.2.0
	 * @access protected
	 */
	protected function render() {
		$classes = 'accordion-section control-section control-section-' . $this->type;
		?>
		<li id="accordion-section-<?php echo esc_attr( $this->id ); ?>" class="<?php echo esc_attr( $classes ); ?>">
			<h3 class="accordion-section-title">
				<?php
				if ( $this->manager->is_theme_active() ) {
					/* translators: %s: theme name */
					printf( __( '<span>Active theme</span> %s' ), $this->title );
				} else {
					/* translators: %s: theme name */
					printf( __( '<span>Previewing theme</span> %s' ), $this->title );
				}
				?>

				<button type="button" class="button change-theme"><?php _ex( 'Change', 'theme' ); ?></button>
			</h3>
			<div class="customize-themes-panel control-panel-content themes-php">
				<h2>
					<?php _e( 'Themes' ); ?>
					<span class="title-count theme-count"><?php echo count( $this->controls ) + 1 /* Active theme */; ?></span>
				</h2>

				<h3 class="accordion-section-title customize-section-title">
					<?php
					if ( $this->manager->is_theme_active() ) {
						/* translators: %s: theme name */
						printf( __( '<span>Active theme</span> %s' ), $this->title );
					} else {
						/* translators: %s: theme name */
						printf( __( '<span>Previewing theme</span> %s' ), $this->title );
					}
					?>
					<button type="button" class="button customize-theme"><?php _e( 'Customize' ); ?></button>
				</h3>

				<div class="theme-overlay" tabindex="0" role="dialog" aria-label="<?php esc_attr_e( 'Theme Details' ); ?>"></div>

				<div id="customize-container"></div>
				<?php if ( count( $this->controls ) > 4 ) : ?>
					<p><label for="themes-filter">
						<span class="screen-reader-text"><?php _e( 'Search installed themes...' ); ?></span>
						<input type="text" id="themes-filter" placeholder="<?php esc_attr_e( 'Search installed themes...' ); ?>" />
					</label></p>
				<?php endif; ?>
				<div class="theme-browser rendered">
					<ul class="themes accordion-section-content">
					</ul>
				</div>
			</div>
		</li>
<?php }
}

/**
 * Customizer section representing widget area (sidebar).
 *
 * @since 4.1.0
 *
 * @see WP_Customize_Section
 */
class WP_Customize_Sidebar_Section extends WP_Customize_Section {

	/**
	 * Type of this section.
	 *
	 * @since 4.1.0
	 * @access public
	 * @var string
	 */
	public $type = 'sidebar';

	/**
	 * Unique identifier.
	 *
	 * @since 4.1.0
	 * @access public
	 * @var string
	 */
	public $sidebar_id;

	/**
	 * Gather the parameters passed to client JavaScript via JSON.
	 *
	 * @since 4.1.0
	 *
	 * @return array The array to be exported to the client as JSON.
	 */
	public function json() {
		$json = parent::json();
		$json['sidebarId'] = $this->sidebar_id;
		return $json;
	}

	/**
	 * Whether the current sidebar is rendered on the page.
	 *
	 * @since 4.1.0
	 * @access public
	 *
	 * @return bool Whether sidebar is rendered.
	 */
	public function active_callback() {
		return $this->manager->widgets->is_sidebar_rendered( $this->sidebar_id );
	}
}
