<?php
/**
 * WordPress Fields API Section class
 *
 * @package WordPress
 * @subpackage Fields API
 */

/**
 * Fields API Section class.
 *
 * A UI container for controls, managed by the WP_Fields_API.
 */
class WP_Fields_API_Section extends WP_Fields_API_Container {

	/**
	 * {@inheritdoc}
	 */
	protected $container_type = 'section';

	/**
	 * Type of this section.
	 *
	 * @access public
	 * @var string
	 */
	public $type = 'default';

	/**
	 * Get the form for this section.
	 *
	 * @return WP_Fields_API_Form|null
	 */
	public function get_form() {

		return $this->get_parent();

	}

	/**
	 * Render the section, and the controls that have been added to it.
	 */
	protected function render() {

		?>
		<div class="fields-form-<?php echo esc_attr( $this->object_type ); ?>-section section-<?php echo esc_attr( $this->id ); ?>-wrap fields-api-section">
			<?php
				if ( $this->label && $this->display_label ) {
					?>
					<h3><?php $this->render_label(); ?></h3>
					<?php
				}

				$this->render_controls();
			?>
		</div>
		<?php

	}

	/**
	 * Render controls for section
	 *
	 * @param WP_Fields_API_Control[] $controls    Control objects
	 */
	protected function render_controls() {

		$controls = $this->get_controls();

		foreach ( $controls as $control ) {
			// Pass $object_name into control
			$control->object_name = $this->object_name;

			if ( ! $control->check_capabilities() ) {
				continue;
			}

			$this->render_control( $control );
		}

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
			<div <?php $control->wrap_attrs(); ?>>
				<?php if ( $control->label ) { ?>
					<label for="<?php echo esc_attr( $input_id ); ?>">
						<?php $control->render_label(); ?>
					</label>
				<?php } ?>

				<?php $control->maybe_render(); ?>

				<?php if ( $control->description ) { ?>
					<p class="description">
						<?php $control->render_description(); ?>
					</p>
				<?php } ?>
			</div>
		<?php

	}

}