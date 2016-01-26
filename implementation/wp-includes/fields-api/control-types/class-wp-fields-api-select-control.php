<?php
/**
 * Fields API Select Control class.
 *
 * @see WP_Fields_API_Control
 */
class WP_Fields_API_Select_Control extends WP_Fields_API_Control {

	/**
	 * {@inheritdoc}
	 */
	public function render_content() {

		if ( empty( $this->choices ) ) {
			return;
		}
		?>
		<label>
			<?php if ( ! empty( $this->label ) ) : ?>
				<span class="fields-control-title"><?php echo esc_html( $this->label ); ?></span>
			<?php endif;
			if ( ! empty( $this->description ) ) : ?>
				<span class="description fields-control-description"><?php echo $this->description; ?></span>
			<?php endif; ?>

			<select <?php $this->link(); ?>>
				<?php
				foreach ( $this->choices as $value => $label )
					echo '<option value="' . esc_attr( $value ) . '"' . selected( $this->value(), $value, false ) . '>' . $label . '</option>';
				?>
			</select>
		</label>
		<?php

	}

}