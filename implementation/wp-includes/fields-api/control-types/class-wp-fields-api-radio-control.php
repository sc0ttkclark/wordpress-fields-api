<?php
/**
 * @package    WordPress
 * @subpackage Fields_API
 */

/**
 * Fields API Radio Control class.
 *
 * @see WP_Fields_API_Control
 */
class WP_Fields_API_Radio_Control extends WP_Fields_API_Control {

	/**
	 * {@inheritdoc}
	 */
	public $type = 'radio';

	/**
	 * {@inheritdoc}
	 */
	protected function render_content() {

		if ( empty( $this->choices ) ) {
			return;
		}

		if ( isset( $this->input_attrs['name'] ) ) {
			$input_name = $this->input_attrs['name'];
		} else {
			$input_name = $this->id;

			if ( ! empty( $this->input_name ) ) {
				$input_name = $this->input_name;
			}
		}

		foreach ( $this->choices as $value => $label ) :
			?>
			<label>
				<input type="radio" value="<?php echo esc_attr( $value ); ?>" name="<?php echo esc_attr( $input_name ); ?>" <?php $this->link(); checked( $this->value(), $value ); ?> />
				<?php echo wp_kses( $label, array(
					'a'     => array(
						'href'  => true,
					),
				) ); ?><br/>
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
				<input type="radio" name="{{ data.input_name }}" value="{{ option.value }}"
					id="{{ data.input_id }}-{{ option.id }}" {{{ option.checked }}} />
				{{ option.label }}
			</label>
		<# } #>
		<?php

	}

}