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
	 * @var string Placeholder text for choices (default "- Select -", set to null for none)
	 */
	public $placeholder_text = '';

	/**
	 * {@inheritdoc}
	 */
	protected function render_content() {

		$choices = $this->choices;

		$placeholder_text = $this->placeholder_text;

		// Set default placeholder text
		if ( '' === $placeholder_text ) {
			$placeholder_text = __( '&mdash; Select &mdash;' );
		}

		// If $placeholder_text is not null, add placeholder to choices
		if ( null !== $placeholder_text ) {
			$choices = array_reverse( $choices, true );
			$choices['0'] = $placeholder_text;
			$choices = array_reverse( $choices, true );
		}

		if ( empty( $choices ) ) {
			return;
		}
		?>
		<select <?php $this->input_attrs(); ?> <?php $this->link(); ?>>
			<?php
			foreach ( $choices as $value => $label )
				echo '<option value="' . esc_attr( $value ) . '"' . selected( $this->value(), $value, false ) . '>' . $label . '</option>';
			?>
		</select>
		<?php

	}

	/**
	 * {@inheritdoc}
	 */
	public function content_template() {

		?>
		<select name="{{ data.input_name }}" <# if ( data.multiple ) { #> multiple="multiple"<# } #>>
			<# for ( key in data.options ) { var option = data.options[ key ]; #>
				<option value="{{ option.value }}" {{{ option.selected }}}>
					{{ option.label }}
				</option>
			<# } #>
		</select>
		<?php

	}

}