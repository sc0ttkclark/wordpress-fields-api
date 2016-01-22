<?php
/**
 * This is an general implementation for Fields API that can be extended
 *
 * @package    WordPress
 * @subpackage Fields_API
 */

/**
 * Class WP_Fields_API_Implementation
 */
class WP_Fields_API_Implementation {

	/**
	 * @var WP_Fields_API_Implementation
	 */
	private static $instance;

	/**
	 * @var string Implementation name
	 */
	public $implementation_name = 'implementation';

	/**
	 * @var string Object type
	 */
	public $object_type = 'implementation';

	/**
	 * Runs methods needed for implementation on 'fields_register' action
	 */
	protected function __construct() {

		$this->register();

	}

	/**
	 * Setup instance for singleton
	 *
	 * @return WP_Fields_API_Implementation
	 */
	public static function get_instance() {

		if ( empty( self::$instance ) ) {
			self::$instance = new self;
		}

		return self::$instance;

	}

	/**
	 * Register screens, sections, controls, and fields
	 */
	public function register() {

		/**
		 * @var $wp_fields WP_Fields_API
		 */
		global $wp_fields;

		// Register control types
		$wp_fields->register_control_type( 'control-type-id', 'Control_Class_Name' );

		// Set $object_name for a specific Post Type, Taxonomy, or Comment Type
		$object_name = null;

		// Add screen(s)
		$wp_fields->add_screen( $this->object_type, 'screen-id', $object_name );

		// Add section(s)
		$wp_fields->add_section( $this->object_type, 'section-id', $object_name, array(
			'title' => __( 'Section Heading' ),
		    'screen' => 'screen-id',
		) );

		$field_args = array(
			// 'sanitize_callback' => array( $this, 'my_sanitize_callback' ),
			'control'                   => array(
				'type'                  => 'text',
				'section'               => 'section-id',
				'label'                 => __( 'Control Label' ),
				'description'           => __( 'Description of control' ),
				// 'capabilities_callback' => array( $this, 'my_capabilities_callback' ),
			),
		);

		$wp_fields->add_field( $this->object_type, 'field-id', $object_name, $field_args );

		//////////////
		// Examples //
		//////////////

		// Section
		$wp_fields->add_section( $this->object_type, 'example-my-fields', $object_name, array(
			'title' => __( 'Fields API Example - My Fields' ),
		    'screen' => 'screen-id',
		) );

		// Add example for each control type
		$control_types = array(
			'text',
			'checkbox',
			'multi-checkbox',
			'radio',
			'select',
			'dropdown-pages',
			'color',
			'media',
			'upload',
			'image',
		);

		$option_types = array(
			'multi-checkbox',
			'radio',
			'select',
		);

		foreach ( $control_types as $control_type ) {
			$id    = 'example_my_' . $control_type . '_field';
			$label = sprintf( __( '%s Field' ), ucwords( str_replace( '-', ' ', $control_type ) ) );

			$field_args = array(
				// Add a control to the field at the same time
				'control' => array(
					'type'    => $control_type,
					'section' => 'example-my-fields',
					'label'   => $label,
				),
			);

			if ( in_array( $control_type, $option_types ) ) {
				$field_args['control']['choices'] = array(
					''         => 'N/A',
					'option-1' => 'Option 1',
					'option-2' => 'Option 2',
					'option-3' => 'Option 3',
					'option-4' => 'Option 4',
					'option-5' => 'Option 5',
				);
			}

			$wp_fields->add_field( $this->object_type, $id, $object_name, $field_args );
		}

	}

	/**
	 * Handle saving of fields
	 *
	 * @param string      $screen_id   Screen ID
	 * @param int|null    $item_id     Item ID
	 * @param string|null $object_name Object name
	 */
	public function save_fields( $screen_id, $item_id = null, $object_name = null ) {

		$implementation_nonce = $this->object_type . '_' . $screen_id;

		if ( ! empty( $_REQUEST['wp_fields_api_fields_save'] ) && false !== wp_verify_nonce( $_REQUEST['wp_fields_api_fields_save'], $implementation_nonce ) ) {
			/**
			 * @var $wp_fields WP_Fields_API
			 */
			global $wp_fields;

			$controls = $wp_fields->get_controls( $this->object_type, $object_name );

			foreach ( $controls as $control ) {
				if ( empty( $control->field ) ) {
					continue;
				}

				// Pass $object_name and $item_id into control
				$control->object_name = $object_name;
				$control->item_id = $item_id;

				$field = $control->field;

				// Pass $object_name and $item_id into field
				$field->object_name = $object_name;

				// Get value from $_POST
				$value = null;

				if ( ! empty( $_POST[ 'field_' . $control->id ] ) ) {
					$value = $_POST[ 'field_' . $control->id ];
				}

				// Sanitize
				$value = $field->sanitize( $value );

				// Save value
				$field->save( $value, $item_id );
			}
		}

	}

	/**
	 * Render screen for implementation
	 *
	 * @param string   $screen_id      Screen ID
	 * @param int|null $item_id        Item ID
	 * @param string|null $object_name Object name
	 */
	public function render_screen( $screen_id, $item_id = null, $object_name = null ) {

		/**
		 * @var $wp_fields WP_Fields_API
		 */
		global $wp_fields;

		$screen = $wp_fields->get_screen( $this->object_type, $screen_id, $object_name );

		if ( $screen ) {
			$implementation_nonce = $this->object_type . '_' . $screen->id;

			wp_nonce_field( $implementation_nonce, 'wp_fields_api_fields_save' );

			// Pass $object_name and $item_id to Screen
			$screen->object_name = $object_name;
			$screen->item_id     = $item_id;

			$sections = $wp_fields->get_sections( $this->object_type, $object_name, $screen->id );

			if ( ! empty( $sections ) ) {
				foreach ( $sections as $section ) {
					$this->render_section( $section, $item_id, $object_name );
				}
			}
		}

	}

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

		$controls = $wp_fields->get_controls( $this->object_type, $object_name, $section->id );

		if ( ! empty( $controls ) ) {
			$content = $section->get_content();

			if ( $content ) {
				?>
				<h3><?php echo $content; ?></h3>
				<?php
			}

			?>
			<table class="form-table fields-api-section">
				<?php
					foreach ( $controls as $control ) {
						$this->render_control( $control, $item_id, $object_name );
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

		// Setup field name
		$control->input_attrs['name'] = 'field_' . $control->id;
		?>
			<tr class="field-<?php echo esc_attr( $control->id ); ?>-wrap fields-api-control">
				<th>
					<?php if ( 0 < strlen( $label ) ) { ?>
						<label for="field-<?php echo esc_attr( $control->id ); ?>"><?php echo esc_html( $label ); ?></label>
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