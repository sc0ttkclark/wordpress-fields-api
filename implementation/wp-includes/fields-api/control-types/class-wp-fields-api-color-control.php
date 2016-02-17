<?php
/**
 * @package    WordPress
 * @subpackage Fields_API
 */

/**
 * Fields API Color Control class.
 *
 * @see WP_Fields_API_Control
 */
class WP_Fields_API_Color_Control extends WP_Fields_API_Control {

	/**
	 * {@inheritdoc}
	 */
	public $type = 'color';

	/**
	 * {@inheritdoc}
	 */
	public function enqueue() {

		wp_enqueue_script( 'wp-color-picker' );
		wp_enqueue_style( 'wp-color-picker' );

	}

	/**
	 * {@inheritdoc}
	 */
	protected function render_content() {

		$this->input_attrs['class'] = 'color-picker-hex';
		$this->input_attrs['placeholder'] = __( 'Hex Value' );
		$this->input_attrs['data-default-color'] = $this->value();

		?>
		<input type="<?php echo esc_attr( $this->type ); ?>" <?php $this->input_attrs(); ?>
			value="<?php echo esc_attr( $this->value() ); ?>" <?php $this->link(); ?> />
		<?php

	}

	/**
	 * {@inheritdoc}
	 */
	public function content_template() {

		?>
		<input type="<?php echo esc_attr( $this->type ); ?>" name="{{ data.input_name }}"
			value="{{ data.value }}" id="{{ data.input_id }}" class="color-picker-hex"
			placeholder="<?php esc_attr_e( 'Hex Value' ); ?>" data-default-color="{{ data.value }}" />
		<?php

	}

}