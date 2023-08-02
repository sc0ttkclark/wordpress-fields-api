<?php
/**
 * WordPress Fields API Meta Box Section class
 *
 * @package    WordPress
 * @subpackage Fields API
 */

/**
 * Fields API Meta Box Section class. Ultimately renders controls in a div
 *
 * @see WP_Fields_API_Section
 */
class WP_Fields_API_Meta_Box_Section extends WP_Fields_API_Section {

	/**
	 * {@inheritdoc}
	 */
	public $type = 'meta-box';

	/**
	 * Meta box context
	 *
	 * @var string
	 */
	public $mb_context = 'advanced';

	/**
	 * Add meta boxes for sections
	 *
	 * @param string             $object_subtype Object subtype, if 'comment' then it's the comment object type
	 * @param WP_Post|WP_Comment $object      Current Object
	 */
	public static function add_meta_boxes( $object_subtype, $object = null ) {

		/**
		 * @var $wp_fields WP_Fields_API
		 */
		global $wp_fields;

		$item_id     = 0;
		$object_type = 'post';

		if ( $object ) {
			if ( ! empty( $object->ID ) ) {
				// Get Post ID and type
				$item_id     = $object->ID;
				$object_type = 'post';
				$object_subtype = $object->post_type;
			} elseif ( ! empty( $object->comment_ID ) ) {
				$item_id     = $object->comment_ID;
				$object_type = 'comment';
				$object_subtype = $object->comment_type;

				if ( empty( $object_subtype ) ) {
					$object_subtype = 'comment';
				}
			} elseif ( 'comment' == $object_subtype ) {
				$item_id     = $object->comment_ID;
				$object_type = 'comment';
			}
		}

		$form_id = $object_type . '-edit';

		// Get form
		$form = $wp_fields->get_form( $form_id );

		if ( ! $form ) {
			return;
		}

		$form->item_id     = $item_id;
		$form->object_subtype = $object_subtype;

		// Get registered sections
		/**
		 * @var $sections WP_Fields_API_Section[]
		 */
		$sections = $form->get_children();

		foreach ( $sections as $section ) {
			// Skip non meta boxes
			if ( ! is_a( $section, 'WP_Fields_API_Meta_Box_Section' ) && ! is_a( $section, 'WP_Fields_API_Meta_Box_Table_Section' ) ) {
				continue;
			}

			/**
			 * @var $section WP_Fields_API_Meta_Box_Section
			 */

			// Pass Object subtype into section
			$section->object_subtype = $object_subtype;

			if ( ! $section->check_capabilities() ) {
				continue;
			}

			// Meta boxes don't display section titles
			$section->display_label = false;

			// Set callback arguments
			$callback_args = array(
				'fields_api' => true,
				'form'       => $form,
				'section'    => $section,
			);

			// Only normal context can be used
			if ( 'comment' == $section->object_type ) {
				$section->mb_context = 'normal';
			}

			// Convert priority
			$mb_priority = self::get_mb_priority( $section->priority );

			// Add meta box
			add_meta_box(
				$section->id,
				$section->label,
				array( 'WP_Fields_API_Meta_Box_Section', 'render_meta_box' ),
				null,
				$section->mb_context,
				$mb_priority,
				$callback_args
			);
		}

	}

	/**
	 * Get Meta Box Priority from Section priority
	 *
	 * @param $priority
	 *
	 * @return string
	 */
	public static function get_mb_priority( $priority ) {

		$priorities = array(
			'high'    => 0,
			'core'    => 100,
			'default' => 200,
			'low'     => 300,
		);

		if ( in_array( $priority, $priorities ) ) {
			if ( 240 <= $priority ) {
				$priority = 'low';
			} elseif ( 160 <= $priority ) {
				$priority = 'default';
			} elseif ( 80 <= $priority ) {
				$priority = 'core';
			} else {
				$priority = 'high';
			}
		} else {
			$priority = 'default';
		}

		return $priority;

	}

	/**
	 * Render meta box output for section
	 *
	 * @param WP_Post|WP_Comment $object Current Object
	 * @param array              $box    Meta box options
	 */
	public static function render_meta_box( $object, $box ) {

		/**
		 * @var $wp_fields WP_Fields_API
		 */
		global $wp_fields;

		if ( empty( $box['args'] ) || empty( $box['args']['fields_api'] ) || empty( $box['args']['form'] ) || empty( $box['args']['section'] ) ) {
			return;
		}

		/**
		 * @var $form    WP_Fields_API_Form
		 * @var $section WP_Fields_API_Section
		 */
		$form    = $box['args']['form'];
		$section = $box['args']['section'];

		$item_id     = 0;
		$object_type = 'post';
		$object_subtype = null;

		if ( $object ) {
			if ( ! empty( $object->ID ) ) {
				// Get Post ID and type
				$item_id     = $object->ID;
				$object_type = 'post';
				$object_subtype = $object->post_type;
			} elseif ( ! empty( $object->comment_ID ) ) {
				$item_id     = $object->comment_ID;
				$object_type = 'comment';
				$object_subtype = $object->comment_type;

				if ( empty( $object_subtype ) ) {
					$object_subtype = 'comment';
				}
			}
		}

		$form->item        = $object;
		$form->item_id     = $item_id;
		$form->object_subtype = $object_subtype;

		$form_nonce = $object_type . '_' . $form->id . '_' . $item_id;

		wp_nonce_field( $form_nonce, 'wp_fields_api_fields_save' );

		$section->maybe_render();

		// Render control templates
		if ( ! has_action( 'admin_print_footer_scripts', array( $wp_fields, 'render_control_templates' ) ) ) {
			add_action( 'admin_print_footer_scripts', array( $wp_fields, 'render_control_templates' ), 5 );
		}

	}

}