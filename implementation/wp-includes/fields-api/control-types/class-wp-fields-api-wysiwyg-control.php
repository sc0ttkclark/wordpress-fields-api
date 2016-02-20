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

		?>
		<div id="wp-{{ data.input_id }}-wrap" class="wp-core-ui wp-editor-wrap html-active">
			<div id="wp-{{ data.input_id }}-editor-container" class="wp-editor-container">
				<div id="qt_{{ data.input_id }}_toolbar" class="quicktags-toolbar"></div>
				<textarea class="wp-editor-area" rows="20" cols="40" name="{{ data.input_name }}" id="{{ data.input_id }}">{{{ data.value }}}</textarea>
			</div>
		</div>

		<?php

	}

}