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
				<input type="checkbox" value="<?php echo esc_attr( $value ); ?>" name="<?php echo esc_attr( $input_name ); ?>[]" <?php $this->link(); checked( $this->value(), $value ); ?> />
				<?php echo esc_html( $label ); ?><br/>
			</label>
			<?php
		endforeach;

	}

}