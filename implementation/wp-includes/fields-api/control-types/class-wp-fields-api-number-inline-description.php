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
	 * Inline text to show next to input
	 *
	 * @var string
	 */
	public $inline_text = '';

	/**
	 * {@inheritdoc}
	 */
	protected function render_content() {

		?>
		<input type="number" <?php $this->input_attrs(); ?> value="<?php echo esc_attr( $this->value() ); ?>" <?php $this->link(); ?> />
		<?php
		if( $this->inline_text ) {
			echo '&nbsp;' . esc_attr( $this->inline_text );
		}

	}
}