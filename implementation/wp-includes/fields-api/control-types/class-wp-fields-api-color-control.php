<?php
/**
 * Fields API Color Control class.
 *
 * @see WP_Fields_API_Control
 */
class WP_Fields_API_Color_Control extends WP_Fields_API_Control {

	/**
	 * @access public
	 * @var string
	 */
	public $type = 'color';

	/**
	 * @access public
	 * @var array
	 */
	public $statuses = array();

	/**
	 * Constructor.
	 *
	 * @since 3.4.0
	 * @uses WP_Fields_API_Control::__construct()
	 *
	 * @param WP_Customize_Manager $manager Customizer bootstrap instance.
	 * @param string               $id      Control ID.
	 * @param array                $args    Optional. Arguments to override class property defaults.
	 */
	public function __construct( $manager, $id, $args = array() ) {

		$this->statuses = array(
			'' => __( 'Default' ),
		);

		parent::__construct( $manager, $id, $args );

	}

	/**
	 * Enqueue scripts/styles for the color picker.
	 *
	 * @since 3.4.0
	 */
	public function enqueue() {
		wp_enqueue_script( 'wp-color-picker' );
		wp_enqueue_style( 'wp-color-picker' );
	}

	/**
	 * Refresh the parameters passed to the JavaScript via JSON.
	 *
	 * @uses WP_Fields_API_Control::json()
	 */
	public function json() {

		$json = parent::json();

		$json['statuses']     = $this->statuses;
		$json['defaultValue'] = $this->setting->default;

		return $json;

	}

	/**
	 * Don't render the control content from PHP, as it's rendered via JS on load.
	 */
	public function render_content() {

		// @todo Figure out what to do for render_content vs content_template for purposes of Customizer vs other Fields implementations

	}

	/**
	 * Render a JS template for the content of the color picker control.
	 */
	public function content_template() {

		// @todo Figure out what to do for render_content vs content_template for purposes of Customizer vs other Fields implementations
		?>
		<# var defaultValue = '';
		if ( data.defaultValue ) {
			if ( '#' !== data.defaultValue.substring( 0, 1 ) ) {
				defaultValue = '#' + data.defaultValue;
			} else {
				defaultValue = data.defaultValue;
			}
			defaultValue = ' data-default-color=' + defaultValue; // Quotes added automatically.
		} #>
		<label>
			<# if ( data.label ) { #>
				<span class="customize-control-title">{{{ data.label }}}</span>
			<# } #>
			<# if ( data.description ) { #>
				<span class="description customize-control-description">{{{ data.description }}}</span>
			<# } #>
			<div class="customize-control-content">
				<input class="color-picker-hex" type="text" maxlength="7" placeholder="<?php esc_attr_e( 'Hex Value' ); ?>" {{ defaultValue }} />
			</div>
		</label>
		<?php
	}
}