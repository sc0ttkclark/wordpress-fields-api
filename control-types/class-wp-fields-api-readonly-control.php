<?php
/**
 * @package    WordPress
 * @subpackage Fields_API
 */

/**
 * Fields API Readonly Control class.
 *
 * @see WP_Fields_API_Control
 */
class WP_Fields_API_Readonly_Control extends WP_Fields_API_Control {

	/**
	 * {@inheritdoc}
	 */
	public $type = 'readonly';

	/**
	 * {@inheritdoc}
	 */
	protected function render_content() {

		?>
        <code><?php echo esc_html( $this->value() ); ?></code>
		<?php

	}

	/**
	 * {@inheritdoc}
	 */
	public function content_template() {

		?>
        <code>{{ data.value }}</code>
		<?php

	}

}