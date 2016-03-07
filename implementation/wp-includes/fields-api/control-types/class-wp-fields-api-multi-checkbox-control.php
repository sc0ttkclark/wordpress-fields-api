<?php
/**
 * @package    WordPress
 * @subpackage Fields_API
 */

/**
 * Fields API Multiple Checkbox Control class.
 *
 * @see WP_Fields_API_Control
 */
class WP_Fields_API_Multi_Checkbox_Control extends WP_Fields_API_Control {

	/**
	 * {@inheritdoc}
	 */
	public $type = 'multi-checkbox';

	/**
	 * {@inheritdoc}
	 */
	protected function render_content() {

		if ( empty( $this->choices ) ) {
			return;
		}

		$input_attrs = $this->get_input_attrs();
		$input_name  = $input_attrs['name'];

		foreach ( $this->choices as $value => $label ) :
			?>
			<label>
				<input type="checkbox" value="<?php echo esc_attr( $value ); ?>" name="<?php echo esc_attr( $input_name ); ?>[]" <?php $this->link(); checked( $this->value(), $value ); ?> />
				<?php echo esc_html( $label ); ?><br/>
			</label>
			<?php
		endforeach;

	}

	/**
	 * {@inheritdoc}
	 */
	public function content_template() {

		?>
		<# for ( key in data.options ) { var option = data.options[ key ]; #>
			<label>
				<input type="checkbox" name="{{ data.input_name }}[]" value="{{ option.value }}"
					id="{{ data.input_id }}-{{ option.id }}" {{{ option.checked }}} />
				{{ option.label }}
			</label>
		<# } #>
		<?php

	}

}