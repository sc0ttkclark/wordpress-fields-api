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
	 * Container type
	 * 
	 * @var string
	 */
	public $container_type = 'section';

	/**
	 * Container children type
	 * 
	 * @var string
	 */
	public $child_container_type = 'control';

	/**
	 * Hidden controls
	 *
	 * @access protected
	 * @var WP_Fields_API_Control[]
	 */
	protected $hidden_controls = array();

	/**
	 * Label to render
	 * 
	 * @var string
	 */
	public $label;

	/**
	 * Show label or not
	 * 
	 * @var bool
	 */
	public $display_label;

	/**
	 * Description to render
	 *
	 * @var string
	 */
	public $description;

	/**
	 * Description callback function to execute
	 * 
	 * @var callback
	 */
	public $description_callback;

	/**
	 * Render the section, and the controls that have been added to it.
	 */
	protected function render() {

		?>
		<div class="fields-form-<?php echo esc_attr( $this->get_object_type() ); ?>-section section-<?php echo esc_attr( $this->id ); ?>-wrap fields-api-section">
			<?php
				if ( $this->label && $this->display_label ) {
					?>
					<h3><?php $this->render_label(); ?></h3>
					<?php
				}

				$this->render_description();

				$this->render_controls();
				$this->render_hidden_controls();

				/**
				 * Fires after rendering Fields API section.
				 *
				 * @param WP_Fields_API_Section $this WP_Fields_API_Section instance.
				 */
				do_action( "fields_after_render_section_{$this->object_type}", $this );

				/**
				 * Fires after rendering Fields API controls for a section.
				 *
				 * The dynamic portion of the hook name, `$this->id`, refers to
				 * the ID of the specific Fields API section rendered.
				 *
				 * @param WP_Fields_API_Section $this WP_Fields_API_Section instance.
				 */
				do_action( "fields_after_render_section_{$this->object_type}_{$this->id}", $this );
			?>
		</div>
		<?php

	}

	/**
	 * Render controls for section
	 */
	protected function render_controls() {

		$controls = $this->get_children();

		foreach ( $controls as $control ) {

			if ( ! $control->check_capabilities() ) {
				continue;
			}

			if ( 'hidden' === $control->type ) {
				$this->hidden_controls[] = $control;

				continue;
			}

			$control->maybe_render();
		}

		/**
		 * Fires after rendering Fields API controls for a section.
		 *
		 * @param WP_Fields_API_Section $this WP_Fields_API_Section instance.
		 */
		do_action( "fields_after_render_section_controls_{$this->object_type}", $this );

		/**
		 * Fires after rendering Fields API controls for a section.
		 *
		 * The dynamic portion of the hook name, `$this->id`, refers to
		 * the ID of the specific Fields API section to have controls rendered.
		 *
		 * @param WP_Fields_API_Section $this WP_Fields_API_Section instance.
		 */
		do_action( "fields_after_render_section_controls_{$this->object_type}_{$this->id}", $this );

	}

	/**
	 * Get the container label.
	 *
	 * @return string Label of the container.
	 */
	public function render_label() {
		if ( $this->label && $this->display_label ) {
			echo esc_html( $this->label );
		}
	}

	/**
	 * Render hidden controls for section
	 *
	 * @param WP_Fields_API_Control[] $controls Control objects
	 */
	protected function render_hidden_controls() {

		$controls = $this->hidden_controls;

		foreach ( $controls as $control ) {
			$control->maybe_render();
		}

		$this->hidden_controls = array();

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
				<?php if ( $control->label && $control->display_label ) { ?>
					<label for="<?php echo esc_attr( $input_id ); ?>">
						<?php $control->render_label(); ?>
					</label>
				<?php } ?>

				<?php $control->maybe_render(); ?>
				<?php $control->render_description(); ?>
			</div>
		<?php

	}

	/**
	 * Get the container description.
	 *
	 * @return string Description of the container.
	 */
	public function render_description() {
		if ( is_callable( $this->description_callback ) ) {
			call_user_func( $this->description_callback, $this );
			return;
		}
		if ( $this->description ) {
		?>
			<p class="description">
				<?php echo wp_kses_post( $this->description ); ?>
			</p>
		<?php
		}
	}

}