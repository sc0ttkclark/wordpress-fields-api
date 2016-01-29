<?php
/**
 * This is a custom Form type specifically for rendering as a table
 *
 * @package    WordPress
 * @subpackage Fields_API
 */

/**
 * Class WP_Fields_API_Table_Form
 */
class WP_Fields_API_Table_Form extends WP_Fields_API_Form {

	/**
	 * {@inheritdoc}
	 */
	public function render_controls( $controls, $item_id = null, $object_name = null ) {

		?>
		<table class="form-table">
			<?php
				parent::render_controls( $controls, $item_id, $object_name );
			?>
		</table>
		<?php

	}

	/**
	 * {@inheritdoc}
	 */
	public function render_control( $control, $item_id = null, $object_name = null ) {

		// Pass $object_name and $item_id to Control
		$control->object_name = $object_name;
		$control->item_id     = $item_id;

		$label       = trim( $control->label );
		$description = trim( $control->description );

		// Avoid outputting them in render_content()
		$control->label       = '';
		$control->description = '';

		$input_id = 'field-' . $control->id;

		if ( isset( $control->input_attrs['id'] ) ) {
			$input_id = $control->input_attrs['id'];
		}
		?>
			<tr <?php $control->wrap_attrs(); ?>>
				<th>
					<?php if ( 0 < strlen( $label ) ) { ?>
						<label for="<?php echo esc_attr( $input_id ); ?>"><?php echo esc_html( $label ); ?></label>
					<?php } ?>
				</th>
				<td>
					<?php $control->render_content(); ?>

					<?php if ( 0 < strlen( $description ) ) { ?>
						<p class="description"><?php echo wp_kses_post( $description ); ?></p>
					<?php } ?>
				</td>
			</tr>
		<?php

	}

}