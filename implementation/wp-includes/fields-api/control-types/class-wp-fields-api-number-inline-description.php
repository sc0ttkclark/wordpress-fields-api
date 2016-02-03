<?php
/**
 * @package    WordPress
 * @subpackage Fields_API
 */

/**
 * Fields API Number field with inline description Control class.
 *
 * @see WP_Fields_API_Number_Inline_Description_Control
 */
class WP_Fields_API_Number_Inline_Description_Control extends WP_Fields_API_Control {

	/**
	 * {@inheritdoc}
	 */
	public $type = 'number-inline-desc';

	/**
	 * {@inheritdoc}
	 */
	protected function render_content() {

		if ( isset( $this->input_attrs['name'] ) ) {
			$input_name = $this->input_attrs['name'];
		} else {
			$input_name = $this->id;

			if ( ! empty( $this->input_name ) ) {
				$input_name = $this->input_name;
			}
		}

		?>
		<input type="number" <?php $this->input_attrs(); ?> value="<?php echo esc_attr( $this->value() ); ?>" <?php $this->link(); ?> />&nbsp;
		<?php
		if( $this->inline_text ) {
			echo esc_attr( $this->inline_text );
		}
		?>
		<?php
	}
}