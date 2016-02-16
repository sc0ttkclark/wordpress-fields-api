<?php
/**
 * @package    WordPress
 * @subpackage Fields_API
 */

/**
 * Fields API Repeater Control class.
 *
 * @see WP_Fields_API_Control
 */
class WP_Fields_API_Repeater_Control extends WP_Fields_API_Control {

	/**
	 * {@inheritdoc}
	 */
	public $type = 'repeater';

	/**
	 * {@inheritdoc}
	 */
	public function enqueue() {

		wp_enqueue_script( 'wp-util' );
		wp_enqueue_script( 'backbone' );
		wp_enqueue_script(
			'fields-api-repeatable-control',
			WP_FIELDS_API_URL . 'implementation/wp-includes/fields-api/js/repeatable-control.js',
			array( 'wp-util', 'backbone' ),
			'0.0.1',
			true
		);

	}

	/**
	 * {@inheritdoc}
	 */
	protected function render_content() {

		$this->input_attrs['class'] = 'repeater-field';
		?>
		<button type="button" class="button button-secondary add-field">Add Field</button>
		<br />
		<input type="<?php echo esc_attr( $this->type ); ?>" <?php $this->input_attrs(); ?>
			value="<?php echo esc_attr( $this->value() ); ?>" <?php $this->link(); ?> />
		<?php

	}

	/**
	 * {@inheritdoc}
	 */
	public function content_template() {

		?>
		<input type="<?php echo esc_attr( $this->type ); ?>" class="repeater-field"
			id="{{ data.input_id }}" name="{{ data.input_name }}" value="{{ data.value }}" />
		<?php

	}

}
