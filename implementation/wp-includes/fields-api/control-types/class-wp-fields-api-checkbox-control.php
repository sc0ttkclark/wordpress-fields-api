<?php
/**
 * @package    WordPress
 * @subpackage Fields_API
 */

/**
 * Fields API Checkbox Control class.
 *
 * @see WP_Fields_API_Control
 */
class WP_Fields_API_Checkbox_Control extends WP_Fields_API_Control {

	/**
	 * {@inheritdoc}
	 */
	public $type = 'checkbox';

	/**
	 * Checkbox label used for the <input> label
	 *
	 * @access public
	 *
	 * @see WP_Fields_API_Checkbox_Control::render_checkbox_label()
	 *
	 * @var string
	 */
	public $checkbox_label;

	/**
	 * Checkbox value, allowing for separation of value, 'default' value, and checkbox value.
	 *
	 * @access public
	 *
	 * @see WP_Fields_API_Checkbox_Control::checkbox_value()
	 *
	 * @var string
	 */
	public $checkbox_value;

	/**
	 * Render checkbox label
	 */
	protected function render_checkbox_label() {

		$label = '';

		if ( $this->checkbox_label ) {
			$label = $this->checkbox_label;
		}

		if ( $label && $this->display_label ) {
			echo esc_html( $label );
		}

	}

	/**
	 * {@inheritdoc}
	 */
	protected function render_content() {

		$checkbox_value = $this->checkbox_value();
		$value = $this->value();

		$checked = false;

		if ( '' !== $value && $checkbox_value == $value ) {
			$checked = true;
		}
		?>
		<label>
			<input type="checkbox" value="<?php echo esc_attr( $checkbox_value ); ?>" <?php $this->link(); checked( $checked ); ?> />
			<?php $this->render_checkbox_label(); ?>
		</label>
		<?php

	}

	/**
	 * {@inheritdoc}
	 */
	public function content_template() {

		?>
		<label>
			<input type="checkbox" name="{{ data.input_name }}" value="{{ data.checkbox_value }}"
				id="{{ data.input_id }}" {{{ data.checked }}} />
			{{ data.label }}
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