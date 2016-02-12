<?php
/**
 * @package    WordPress
 * @subpackage Fields_API
 */

/**
 * Fields API Textarea Control class.
 *
 * @see WP_Fields_API_Control
 */
class WP_Fields_API_Textarea_Control extends WP_Fields_API_Control {

	/**
	 * {@inheritdoc}
	 */
	public $type = 'textarea';

	/**
	 * {@inheritdoc}
	 */
	protected function render_content() {

		?>
		<textarea <?php $this->input_attrs(); ?> <?php $this->link(); ?>><?php echo esc_textarea( $this->value() ); ?></textarea>
		<?php

	}

	/**
	 * {@inheritdoc}
	 */
	public function content_template() {

		?>
		<textarea name="{{ data.input_name }}" id="{{ data.input_id }}">{{{ data.value }}}</textarea>
		<?php

	}

}