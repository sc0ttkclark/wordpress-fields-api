<?php
/**
 * WordPress Fields API Table Section class
 *
 * @package WordPress
 * @subpackage Fields API
 */

/**
 * Fields API Table Section class.
 *
 * @see WP_Fields_API_Section
 */
class WP_Fields_API_Table_Section extends WP_Fields_API_Section {

	/**
	 * {@inheritdoc}
	 */
	public $type = 'table';

	/**
	 * {@inheritdoc}
	 */
	protected function render_controls() {

		?>
        <table class="form-table">
			<?php parent::render_controls(); ?>
        </table>
		<?php

	}

	/**
	 * Render control wrapper, label, description, and control input
	 *
	 * @param WP_Fields_API_Control $control Control object
	 */
	protected function render_control( $control ) {

		$input_id = 'field-' . $control->id;

		if ( isset( $control->input_attrs['id'] ) ) {
			$input_id = $control->input_attrs['id'];
		}
		?>
        <tr <?php $control->wrap_attrs(); ?>>
            <th scope="row">
				<?php if ( $control->label && $control->display_label ) { ?>
                    <label for="<?php echo esc_attr( $input_id ); ?>">
						<?php $control->render_label(); ?>
                    </label>
				<?php } ?>
            </th>
            <td>
				<?php $control->maybe_render(); ?>
				<?php $control->render_description(); ?>
            </td>
        </tr>
		<?php

	}

}