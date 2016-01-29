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
	 * Render section for implementation
	 *
	 * @param WP_Fields_API_Section $section     Section object
	 * @param int|null              $item_id     Item ID
	 * @param string|null           $object_name Object name
	 */
	public function render_section( $section, $item_id = null, $object_name = null ) {

		/**
		 * @var $wp_fields WP_Fields_API
		 */
		global $wp_fields;

		// Pass $object_name and $item_id to Section
		$section->object_name = $object_name;
		$section->item_id     = $item_id;

		$controls = $wp_fields->get_controls( $this->object_type, $section->object_name, $section->id );

		if ( ! empty( $controls ) ) {
			$content = $section->get_content();

			if ( $content && $section->display_title ) {
				?>
				<h3><?php echo $content; ?></h3>
				<?php
			}

			?>
			<table class="form-table fields-form-<?php echo esc_attr( $this->object_type ); ?>-section section-<?php echo esc_attr( $section->id ); ?>-wrap fields-api-section">
				<?php
					foreach ( $controls as $control ) {
						$this->render_control( $control, $item_id, $section->object_name );
					}
				?>
			</table>
			<?php
		}

	}

	/**
	 * Render control for implementation
	 *
	 * @param WP_Fields_API_Control $control     Control object
	 * @param int|null              $item_id     Item ID
	 * @param string|null           $object_name Object name
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