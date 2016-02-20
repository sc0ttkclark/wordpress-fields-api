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
	 * {@inheritdoc}
	 */
	protected function render_content() {
		
		$this->input_attrs = $this->get_input_attrs();
		$settings = array( 'textarea_name' => $this->input_attrs['name'] );
		
		// actually render the tinyMCE box
		wp_editor( $this->value(), $this->input_attrs['id'], $settings );

	}

	/**
	 * {@inheritdoc}
	 */
	public function content_template() {

		$settings = array( 'textarea_name' => '{{ data.input_name }}' );
		wp_editor( '{{{ data.value }}}', '{{ data.input_id }}', $settings );

	}

}