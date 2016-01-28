<?php
/**
 * WordPress Customize Panel classes
 *
 * @package WordPress
 * @subpackage Customize
 * @since 4.0.0
 */

/**
 * Customize Panel class.
 *
 * A UI container for sections, managed by the WP_Customize_Manager.
 *
 * @since 4.0.0
 *
 * @see WP_Customize_Manager
 */
class WP_Customize_Panel extends WP_Fields_API_Form {

	/**
	 * Object type.
	 *
	 * @access public
	 * @var string
	 */
	public $object_type = 'customizer';

	/**
	 * WP_Customize_Manager instance.
	 *
	 * @access public
	 * @var WP_Customize_Manager
	 */
	public $manager;

	/**
	 * Constructor.
	 *
	 * Any supplied $args override class property defaults.
	 *
	 * @since 4.0.0
	 *
	 * @param WP_Customize_Manager $manager Customizer bootstrap instance.
	 * @param string               $id      An specific ID for the panel.
	 * @param array                $args    Panel arguments.
	 */
	public function __construct( $manager, $id, $args = array() ) {

		$this->manager = $manager;

		$this->object_name = $this->manager->get_customizer_object_name();

		parent::__construct( $this->object_type, $id, $args );

		if ( ! has_filter( "fields_api_form_active_{$this->object_type}", array( 'WP_Customize_Panel', 'customize_panel_active' ) ) ) {
			add_filter( "fields_api_form_active_{$this->object_type}", array( 'WP_Customize_Panel', 'customize_panel_active' ), 10, 2 );
		}

		if ( ! has_action( "fields_api_render_form_{$this->object_type}", array( 'WP_Customize_Panel', 'customize_render_panel' ) ) ) {
			add_action( "fields_api_render_form_{$this->object_type}", array( 'WP_Customize_Panel', 'customize_render_panel' ) );
		}

		if ( '' !== $this->id ) {
			add_action( "fields_api_render_form_{$this->object_type}_{$this->object_name}_{$this->id}", array( $this, 'customize_render_panel_id' ) );
		}

	}

	/**
	 * Check whether panel is active to current Customizer preview.
	 *
	 * @access public
	 *
	 * @param bool                 $active  Whether the Fields API form is active.
	 * @param WP_Fields_API_Form  $form {@see WP_Fields_API_Form} instance.
	 *
	 * @return bool Whether the panel is active to the current preview.
	 */
	public static function customize_panel_active( $active, $form ) {

		/**
		 * Filter response of {@see WP_Customize_Panel::active()}.
		 *
		 * @since 4.1.0
		 *
		 * @param bool                 $active  Whether the Customizer panel is active.
		 * @param WP_Customize_Panel   $form {@see WP_Customize_Panel} instance.
		 */
		$active = apply_filters( 'customize_panel_active', $active, $form );

		return $active;

	}

	/**
	 * Backwards compatibility for fields_api_render_panel
	 *
	 * @param WP_Customize_Panel $form {@see WP_Fields_API_Form} instance.
	 */
	public static function customize_render_panel( $form ) {

		/**
		 * Fires before rendering a Customizer panel.
		 *
		 * @since 4.0.0
		 *
		 * @param WP_Customize_Panel $form WP_Customize_Panel instance.
		 */
		do_action( 'customize_render_panel', $form );

	}

	/**
	 * Backwards compatibility for fields_api_render_panel
	 */
	public function customize_render_panel_id() {

		/**
		 * Fires before rendering a specific Customizer panel.
		 *
		 * The dynamic portion of the hook name, `$this->id`, refers to
		 * the ID of the specific Customizer panel to be rendered.
		 *
		 * @since 4.0.0
		 */
		do_action( "customize_render_panel_{$this->id}" );

	}

	/**
	 * Render the panel container, and then its contents (via `this->render_content()`) in a subclass.
	 *
	 * Panel containers are now rendered in JS by default, see {@see WP_Customize_Panel::print_template()}.
	 *
	 * @since 4.0.0
	 * @access protected
	 */
	protected function render() {}

	/**
	 * Render the panel UI in a subclass.
	 *
	 * Panel contents are now rendered in JS by default, see {@see WP_Customize_Panel::print_template()}.
	 *
	 * @since 4.1.0
	 * @access protected
	 */
	protected function render_content() {}

	/**
	 * Render the panel's JS templates.
	 *
	 * This function is only run for panel types that have been registered with
	 * WP_Customize_Manager::register_panel_type().
	 *
	 * @since 4.3.0
	 *
	 * @see WP_Customize_Manager::register_panel_type()
	 */
	public function print_template() {
		?>
		<script type="text/html" id="tmpl-customize-panel-<?php echo esc_attr( $this->type ); ?>-content">
			<?php $this->content_template(); ?>
		</script>
		<script type="text/html" id="tmpl-customize-panel-<?php echo esc_attr( $this->type ); ?>">
			<?php $this->render_template(); ?>
		</script>
        <?php
	}

	/**
	 * An Underscore (JS) template for rendering this panel's container.
	 *
	 * Class variables for this panel class are available in the `data` JS object;
	 * export custom variables by overriding WP_Customize_Panel::json().
	 *
	 * @see WP_Customize_Panel::print_template()
	 *
	 * @since 4.3.0
	 * @access protected
	 */
	protected function render_template() {
		?>
		<li id="accordion-panel-{{ data.id }}" class="accordion-section control-section control-panel control-panel-{{ data.type }}">
			<h3 class="accordion-section-title" tabindex="0">
				{{ data.title }}
				<span class="screen-reader-text"><?php _e( 'Press return or enter to open this panel' ); ?></span>
			</h3>
			<ul class="accordion-sub-container control-panel-content"></ul>
		</li>
		<?php
	}

	/**
	 * An Underscore (JS) template for this panel's content (but not its container).
	 *
	 * Class variables for this panel class are available in the `data` JS object;
	 * export custom variables by overriding WP_Customize_Panel::json().
	 *
	 * @see WP_Customize_Panel::print_template()
	 *
	 * @since 4.3.0
	 * @access protected
	 */
	protected function content_template() {
		?>
		<li class="panel-meta customize-info accordion-section <# if ( ! data.description ) { #> cannot-expand<# } #>">
			<button class="customize-panel-back" tabindex="-1"><span class="screen-reader-text"><?php _e( 'Back' ); ?></span></button>
			<div class="accordion-section-title">
				<span class="preview-notice"><?php
					/* translators: %s is the site/panel title in the Customizer */
					echo sprintf( __( 'You are customizing %s' ), '<strong class="panel-title">{{ data.title }}</strong>' );
				?></span>
				<button class="customize-help-toggle dashicons dashicons-editor-help" tabindex="0" aria-expanded="false"><span class="screen-reader-text"><?php _e( 'Help' ); ?></span></button>
			</div>
			<# if ( data.description ) { #>
				<div class="description customize-panel-description">
					{{{ data.description }}}
				</div>
			<# } #>
		</li>
		<?php
	}
}

/**
 * Customize Nav Menus Panel Class
 *
 * Needed to add form options.
 *
 * @since 4.3.0
 *
 * @see WP_Customize_Panel
 */
class WP_Customize_Nav_Menus_Panel extends WP_Customize_Panel {

	/**
	 * Control type.
	 *
	 * @since 4.3.0
	 * @access public
	 * @var string
	 */
	public $type = 'nav_menus';

	/**
	 * Render form options for Menus.
	 *
	 * @since 4.3.0
	 * @access public
	 */
	public function render_form_options() {
		// Essentially adds the form options.
		add_filter( 'manage_nav-menus_columns', array( $this, 'wp_nav_menu_manage_columns' ) );

		// Display form options.
		$screen = WP_Screen::get( 'nav-menus.php' );
		$screen->render_form_options();
	}

	/**
	 * Returns the advanced options for the nav menus page.
	 *
	 * Link title attribute added as it's a relatively advanced concept for new users.
	 *
	 * @since 4.3.0
	 * @access public
	 *
	 * @return array The advanced menu properties.
	 */
	public function wp_nav_menu_manage_columns() {
		return array(
			'_title'      => __( 'Show advanced menu properties' ),
			'cb'          => '<input type="checkbox" />',
			'link-target' => __( 'Link Target' ),
			'attr-title'  => __( 'Title Attribute' ),
			'css-classes' => __( 'CSS Classes' ),
			'xfn'         => __( 'Link Relationship (XFN)' ),
			'description' => __( 'Description' ),
		);
	}

	/**
	 * An Underscore (JS) template for this panel's content (but not its container).
	 *
	 * Class variables for this panel class are available in the `data` JS object;
	 * export custom variables by overriding WP_Customize_Panel::json().
	 *
	 * @since 4.3.0
	 * @access protected
	 *
	 * @see WP_Customize_Panel::print_template()
	 */
	protected function content_template() {
		?>
		<li class="panel-meta customize-info accordion-section <# if ( ! data.description ) { #> cannot-expand<# } #>">
			<button type="button" class="customize-panel-back" tabindex="-1">
				<span class="screen-reader-text"><?php _e( 'Back' ); ?></span>
			</button>
			<div class="accordion-section-title">
				<span class="preview-notice">
					<?php
					/* Translators: %s is the site/panel title in the Customizer. */
					printf( __( 'You are customizing %s' ), '<strong class="panel-title">{{ data.title }}</strong>' );
					?>
				</span>
				<button type="button" class="customize-help-toggle dashicons dashicons-editor-help" aria-expanded="false">
					<span class="screen-reader-text"><?php _e( 'Help' ); ?></span>
				</button>
				<button type="button" class="customize-form-options-toggle" aria-expanded="false">
					<span class="screen-reader-text"><?php _e( 'Menu Options' ); ?></span>
				</button>
			</div>
			<# if ( data.description ) { #>
			<div class="description customize-panel-description">{{{ data.description }}}</div>
			<# } #>
			<?php $this->render_form_options(); ?>
		</li>
	<?php
	}
}