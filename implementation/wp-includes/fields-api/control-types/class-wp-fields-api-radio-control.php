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

		$input_attrs = $this->get_input_attrs();

		$choice_attrs = array(
			'type' => 'radio',
			'name' => $input_attrs['name'],
		);

		$current_value = $this->value();

		foreach ( $this->choices as $value => $label ) :
			$option_attrs = $choice_attrs;

			$option_attrs['value'] = $value;

			if ( $value == $current_value ) {
				$option_attrs['checked'] = 'checked';
			}
			?>
			<label>
				<input <?php $this->render_attrs( $option_attrs ); $this->link(); ?> />
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