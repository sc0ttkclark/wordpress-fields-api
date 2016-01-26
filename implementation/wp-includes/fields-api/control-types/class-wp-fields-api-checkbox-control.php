<?php
/**
 * Fields API Checkbox Control class.
 *
 * @see WP_Fields_API_Control
 */
class WP_Fields_API_Checkbox_Control extends WP_Fields_API_Control {

	/**
	 * Checkbox Value override.
	 *
	 * @access public
	 *
	 * @see WP_Fields_API_Checkbox_Control::checkbox_value()
	 *
	 * @var string Checkbox value, allowing for separation of value, 'default' value, and checkbox value.
	 */
	public $checkbox_value;

	/**
	 * {@inheritdoc}
	 */
	public function render_content() {

			$checkbox_value = $this->checkbox_value();
			$value = $this->value();

			$checked = false;

			if ( '' !== $value && $checkbox_value == $value ) {
				$checked = true;
			}
		?>
		<label>
			<input type="checkbox" value="<?php echo esc_attr( $checkbox_value ); ?>" <?php $this->link(); checked( $checked ); ?> />
			<?php echo esc_html( $this->label ); ?>
			<?php if ( ! empty( $this->description ) ) : ?>
				<span class="description fields-control-description"><?php echo $this->description; ?></span>
			<?php endif; ?>
		</label>
		<?php

	}

	/**
	 * Fetch a checkbox value and allows overriding for separation from field value and 'default' value.
	 *
	 * @return string The requested checkbox value.
	 */
	final public function checkbox_value() {

		$value = $this->value();

		if ( isset( $this->checkbox_value ) ) {
			$value = $this->checkbox_value;
		}

		return $value;

	}

}