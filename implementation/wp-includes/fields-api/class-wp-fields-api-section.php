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
	 * Form in which to show the section, making it a sub-section.
	 *
	 * @access public
	 * @var string|WP_Fields_API_Form
	 */
	public $form = '';

	/**
	 * Type of this section.
	 *
	 * @access public
	 * @var string
	 */
	public $type = 'default';

	/**
	 * Item ID of current item
	 *
	 * @access public
	 * @var int|string
	 */
	public $item_id = 0;

	/**
	 * Render the section, and the controls that have been added to it.
	 */
	protected function render() {

		?>
		<div class="fields-form-<?php echo esc_attr( $this->object_type ); ?>-section section-<?php echo esc_attr( $this->id ); ?>-wrap fields-api-section">
			<?php
				if ( ! empty( $this->label ) && $this->display_label ) {
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

		/**
		 * @var $wp_fields WP_Fields_API
		 */
		global $wp_fields;

		$controls = $wp_fields->get_controls( $this->object_type, $this->object_name, $this->id );

		foreach ( $controls as $control ) {
			// Pass $object_name and $item_id to Control
			$control->object_name = $this->object_name;
			$control->item_id     = $this->item_id;

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