<?php
/**
 * This is an implementation for Fields API for the Term Add New screen in the WordPress Dashboard
 *
 * @package    WordPress
 * @subpackage Fields_API
 */

/**
 * Class WP_Fields_API_Screen_Term_Add
 */
class WP_Fields_API_Screen_Term_Add extends WP_Fields_API_Screen_Term {

	/**
	 * {@inheritdoc}
	 */
	public function save_fields( $item_id = null, $object_name = null ) {

		$term_name = '';

		// Get tag name
		if ( isset( $_POST['tag-name'] ) ) {
			$term_name = $_POST['tag-name'];
		}

		// Save new term
		$success = wp_insert_term( $term_name, $object_name, $_POST );

		// Return if not successful
		if ( is_wp_error( $success ) ) {
			return $success;
		}

		// Save additional fields
		return parent::save_fields( $item_id, $object_name );

	}

	/**
	 * {@inheritdoc}
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
			<div class="section-<?php echo esc_attr( $section->id ); ?>-wrap fields-api-section">
				<?php
					foreach ( $controls as $control ) {
						$this->render_control( $control, $item_id, $section->object_name );
					}
				?>
			</div>
			<?php
		}

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

		// Setup field id / name
		$control->input_attrs['id']   = 'field-' . $control->id;
		$control->input_attrs['name'] = 'field_' . $control->id;

		// @todo Handle form-required and other classes
		?>
			<div class="form-field term-<?php echo esc_attr( $control->id ); ?>-wrap field-<?php echo esc_attr( $control->id ); ?>-wrap fields-api-control">
				<?php if ( 0 < strlen( $label ) ) { ?>
					<label for="field-<?php echo esc_attr( $control->id ); ?>"><?php echo esc_html( $label ); ?></label>
				<?php } ?>

				<?php $control->render_content(); ?>

				<?php if ( 0 < strlen( $description ) ) { ?>
					<p class="description"><?php echo wp_kses_post( $description ); ?></p>
				<?php } ?>
			</div>
		<?php

	}

}