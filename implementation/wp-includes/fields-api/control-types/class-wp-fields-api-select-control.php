<?php
/**
 * @package    WordPress
 * @subpackage Fields_API
 */

/**
 * Fields API Select Control class.
 *
 * @see WP_Fields_API_Control
 */
class WP_Fields_API_Select_Control extends WP_Fields_API_Control {

	/**
	 * {@inheritdoc}
	 */
	public $type = 'select';

	/**
	 * {@inheritdoc}
	 */
	protected function render_content() {

		if ( empty( $this->choices ) ) {
			return;
		}
		?>
		<select <?php $this->input_attrs(); ?> <?php $this->link(); ?>>
			<?php
			foreach ( $this->choices as $value => $label )
				echo '<option value="' . esc_attr( $value ) . '"' . selected( $this->value(), $value, false ) . '>' . $label . '</option>';
			?>
		</select>
		<?php

	}

}