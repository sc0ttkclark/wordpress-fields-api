<?php
/**
 * @package    WordPress
 * @subpackage Fields_API
 */

/**
 * Fields API WYSIWYG Control class.
 *
 * @see WP_Fields_API_Control
 */
class WP_Fields_API_WYSIWYG_Control extends WP_Fields_API_Control {

	/**
	 * {@inheritdoc}
	 */
	public $type = 'wysiwyg';

	/**
	 * Editor settings for use with wp_editor() third parameter
	 *
	 * @access public
	 * @var array
	 */
	public $editor_settings = array();

	/**
	 * {@inheritdoc}
	 */
	protected function render_content() {

		// Get editor settings
		$settings = $this->editor_settings;

		// Get input attributes for textarea name
		$this->input_attrs = $this->get_input_attrs();

		// Set textarea name
		$settings['textarea_name'] = $this->input_attrs['name'];

		// actually render the tinyMCE box
		wp_editor( $this->value(), $this->input_attrs['id'], $settings );

	}

	/**
	 * {@inheritdoc}
	 */
	public function content_template() {

		// @todo This needs further work and testing, not sure if it works right now, custom control settings don't pass through either

		//$settings = array( 'textarea_name' => '{{ data.input_name }}' );
		//wp_editor( '{{{ data.value }}}', '{{ data.input_id }}', $settings );

	}

}