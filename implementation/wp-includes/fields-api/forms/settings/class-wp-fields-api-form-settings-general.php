<?php
/**
 * This is an implementation for Fields API for the General Settings form in the WordPress Dashboard
 *
 * @package    WordPress
 * @subpackage Fields_API
 */

/**
 * Class WP_Fields_API_Form_Settings_General
 */
class WP_Fields_API_Form_Settings_General extends WP_Fields_API_Form {

	/**
	 * {@inheritdoc}
	 */
	public function save_fields( $item_id = null, $object_name = null ) {

	}

	public function register_fields( $wp_fields ) {
		$this->register_control_types( $wp_fields );

		echo '<pre>';print_r( $wp_fields->get_sections() );echo'</pre>';
		$wp_fields->add_section( $this->object_type, $this->id . '-options-general', null, array(
				'title'  => __( 'General Settings' ),
				'form' => $this->id,
		) );
		echo '<pre>';print_r( $wp_fields->get_sections() );echo'</pre>';exit;

		$field_args = array(
				'control' => array(
						'type'        => 'text',
						'section'     => $this->id . '-options-general',
						'label'       => __( 'Site Title' ),
						//'description' => __( 'Usernames cannot be changed.' ),
						//'input_attrs' => array(
						//		'disabled' => 'disabled',
						//),
						'internal'    => true,
				),
		);

		$wp_fields->add_field( 'options', 'blogname', null, $field_args );
	}

	/**
	 * {@inheritdoc}
	 */
	public function render_section( $section, $item_id = null, $object_name = null ) {

		/**
		 * @var $wp_fields WP_Fields_API
		 */
		global $wp_fields, $allowed_tags;

		// Pass $object_name and $item_id to Section
		$section->object_name = $object_name;
		$section->item_id     = $item_id;

		$controls = $wp_fields->get_controls( $this->object_type, $section->object_name, $section->id );

		if ( ! empty( $controls ) ) {
			$content = $section->get_content();

			if ( $content && $section->display_title ) {
				?>
				<h3><?php echo wp_kses( $content, $allowed_tags ); ?></h3>
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

		$input_id = 'field-' . $control->id;

		if ( isset( $control->input_attrs['id'] ) ) {
			$input_id = $control->input_attrs['id'];
		}
		?>
		<tr>
			<th scope="row" <?php $control->wrap_attrs(); ?>>
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