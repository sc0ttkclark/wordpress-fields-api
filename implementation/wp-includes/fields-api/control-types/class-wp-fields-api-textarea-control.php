<?php
/**
 * Fields API Textarea Control class.
 *
 * @see WP_Fields_API_Control
 */
class WP_Fields_API_Textarea_Control extends WP_Fields_API_Control {

	/**
	 * {@inheritdoc}
	 */
	public function render_content() {

		?>
		<label>
			<?php if ( ! empty( $this->label ) ) : ?>
				<span class="fields-control-title"><?php echo esc_html( $this->label ); ?></span>
			<?php endif;
			if ( ! empty( $this->description ) ) : ?>
				<span class="description fields-control-description"><?php echo $this->description; ?></span>
			<?php endif; ?>
			<textarea <?php $this->input_attrs(); ?> <?php $this->link(); ?>><?php echo esc_textarea( $this->value( $this->item_id ) ); ?></textarea>
		</label>
		<?php

	}

	/**
	 * Fetch a checkbox value and allows overriding for separation from field value and 'default' value.
	 *
	 * @return string The requested checkbox value.
	 */
	final public function checkbox_value() {

		$value = $this->value();

		if ( isset( $this->checkbox_value ) ) {
			$value = $this->checkbox_value;
		}

		return $value;

	}

}