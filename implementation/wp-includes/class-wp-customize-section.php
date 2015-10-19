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
	 * @access public
	 * @var WP_Customize_Manager
	 */
	public $manager;

	/**
	 * @var array Internal mapping of backwards compatible properties
	 */
	private $property_map = array(
		'panel' => 'screen'
	);

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

		$this->object_name = $manager->get_customizer_object_name();

		// Backwards compatibility for old property names
		foreach ( $this->property_map as $backcompat_arg => $actual_arg ) {
			if ( isset( $args[ $backcompat_arg ] ) ) {
				$args[ $actual_arg ] = $args[ $backcompat_arg ];

				unset( $args[ $backcompat_arg ] );
			}
		}

		parent::__construct( $this->type, $id, $args );

		add_action( "fields_api_section_active_{$this->object_type}", array( $this, 'customize_section_active' ), 10, 2 );
		add_action( "fields_api_render_section_{$this->object_type}", array( $this, 'customize_render_section' ) );

		if ( '' !== $this->id ) {
			add_action( "fields_api_render_section_{$this->object_type}_{$this->id}", array( $this, 'customize_render_section_id' ) );
		}

	}

	/**
	 * Check whether section is active to current Customizer preview.
	 *
	 * @since 4.1.0
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
	 * Render the section UI in a subclass.
	 *
	 * Sections are now rendered in JS by default, see {@see WP_Customize_Section::print_template()}.
	 *
	 * @since 3.4.0
	 */
	protected function render() {}

	/**
	 * Render the section's JS template.
	 *
	 * This function is only run for section types that have been registered with
	 * WP_Customize_Manager::register_section_type().
	 *
	 * @since 4.3.0
	 * @access public
	 *
	 * @see WP_Customize_Manager::render_template()
	 */
	public function print_template() {
        ?>
		<script type="text/html" id="tmpl-customize-section-<?php echo $this->type; ?>">
			<?php $this->render_template(); ?>
		</script>
        <?php
	}

	/**
	 * An Underscore (JS) template for rendering this section.
	 *
	 * Class variables for this section class are available in the `data` JS object;
	 * export custom variables by overriding WP_Customize_Section::json().
	 *
	 * @since 4.3.0
	 * @access protected
	 *
	 * @see WP_Customize_Section::print_template()
	 */
	protected function render_template() {
		?>
		<li id="accordion-section-{{ data.id }}" class="accordion-section control-section control-section-{{ data.type }}">
			<h3 class="accordion-section-title" tabindex="0">
				{{ data.title }}
				<span class="screen-reader-text"><?php _e( 'Press return or enter to open' ); ?></span>
			</h3>
			<ul class="accordion-section-content">
				<li class="customize-section-description-container">
					<div class="customize-section-title">
						<button class="customize-section-back" tabindex="-1">
							<span class="screen-reader-text"><?php _e( 'Back' ); ?></span>
						</button>
						<h3>
							<span class="customize-action">
								{{{ data.customizeAction }}}
							</span>
							{{ data.title }}
						</h3>
					</div>
					<# if ( data.description ) { #>
						<div class="description customize-section-description">
							{{{ data.description }}}
						</div>
					<# } #>
				</li>
			</ul>
		</li>
		<?php
	}

	/**
	 * Gather the parameters passed to client JavaScript via JSON.
	 *
	 * @return array The array to be exported to the client as JSON.
	 */
	public function json() {

		$array = parent::json();

		if ( $this->screen ) {
			/* translators: &#9656; is the unicode right-pointing triangle, and %s is the section title in the Customizer */
			$array['customizeAction'] = sprintf( __( 'Customizing &#9656; %s' ), esc_html( $this->manager->get_panel( $this->screen )->title ) );
		} else {
			$array['customizeAction'] = __( 'Customizing' );
		}

		// Backwards compatibility for old property names
		foreach ( $this->property_map as $backcompat_arg => $actual_arg ) {
			if ( isset( $array[ $actual_arg ] ) ) {
				$array[ $backcompat_arg ] = $array[ $actual_arg ];
			}
		}

		return $array;

	}

	/**
	 * Magic method for handling backwards compatible properties
	 *
	 * @param string $get
	 *
	 * @return mixed|null
	 */
	public function __get( $get ){

		if ( isset( $this->property_map[ $get ] ) ) {
			$property = $this->property_map[ $get ];

			return $this->{$property};
		}

		return null;

	}

	/**
	 * Magic method for handling backwards compatible properties
	 *
	 * @param string $set
	 * @param mixed  $val
	 */
	public function __set( $set, $val ) {

		if ( isset( $this->property_map[ $set ] ) ) {
			$property = $this->property_map[ $set ];

			$this->{$property} = $val;
		}

	}

	/**
	 * Magic method for handling backwards compatible properties
	 *
	 * @param string $isset
	 *
	 * @return bool
	 */
	public function __isset( $isset ) {

		if ( isset( $this->property_map[ $isset ] ) ) {
			$property = $this->property_map[ $isset ];

			return isset( $this->{$property} );
		}

		return false;

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
					echo '<span class="customize-action">' . __( 'Active theme' ) . '</span> ' . $this->title;
				} else {
					echo '<span class="customize-action">' . __( 'Previewing theme' ) . '</span> ' . $this->title;
				}
				?>

				<button type="button" class="button change-theme" tabindex="0"><?php _ex( 'Change', 'theme' ); ?></button>
			</h3>
			<div class="customize-themes-panel control-panel-content themes-php">
				<h3 class="accordion-section-title customize-section-title">
					<span class="customize-action"><?php _e( 'Customizing' ); ?></span>
					<?php _e( 'Themes' ); ?>
					<span class="title-count theme-count"><?php echo count( $this->controls ) + 1 /* Active theme */; ?></span>
				</h3>
				<h3 class="accordion-section-title customize-section-title">
					<?php
					if ( $this->manager->is_theme_active() ) {
						echo '<span class="customize-action">' . __( 'Active theme' ) . '</span> ' . $this->title;
					} else {
						echo '<span class="customize-action">' . __( 'Previewing theme' ) . '</span> ' . $this->title;
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

/**
 * Customize Menu Section Class
 *
 * Custom section only needed in JS.
 *
 * @since 4.3.0
 *
 * @see WP_Customize_Section
 */
class WP_Customize_Nav_Menu_Section extends WP_Customize_Section {

	/**
	 * Control type.
	 *
	 * @since 4.3.0
	 * @access public
	 * @var string
	 */
	public $type = 'nav_menu';

	/**
	 * Get section parameters for JS.
	 *
	 * @since 4.3.0
	 * @access public
	 * @return array Exported parameters.
	 */
	public function json() {
		$exported = parent::json();
		$exported['menu_id'] = intval( preg_replace( '/^nav_menu\[(\d+)\]/', '$1', $this->id ) );

		return $exported;
	}
}

/**
 * Customize Menu Section Class
 *
 * Implements the new-menu-ui toggle button instead of a regular section.
 *
 * @since 4.3.0
 *
 * @see WP_Customize_Section
 */
class WP_Customize_New_Menu_Section extends WP_Customize_Section {

	/**
	 * Control type.
	 *
	 * @since 4.3.0
	 * @access public
	 * @var string
	 */
	public $type = 'new_menu';

	/**
	 * Render the section, and the controls that have been added to it.
	 *
	 * @since 4.3.0
	 * @access protected
	 */
	protected function render() {
		?>
		<li id="accordion-section-<?php echo esc_attr( $this->id ); ?>" class="accordion-section-new-menu">
			<button type="button" class="button-secondary add-new-menu-item add-menu-toggle" aria-expanded="false">
				<?php echo esc_html( $this->title ); ?>
			</button>
			<ul class="new-menu-section-content"></ul>
		</li>
		<?php
	}
}