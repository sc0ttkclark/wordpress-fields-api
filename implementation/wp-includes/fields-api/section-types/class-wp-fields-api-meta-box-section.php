<?php
/**
 * WordPress Fields API Meta Box Section class
 *
 * @package    WordPress
 * @subpackage Fields API
 */

/**
 * Fields API Meta Box Section class. Ultimately renders controls in a table
 *
 * @see WP_Fields_API_Table_Section
 */
class WP_Fields_API_Meta_Box_Section extends WP_Fields_API_Table_Section {

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
	 * @param string             $object_name Object name, if 'comment' then it's the comment object type
	 * @param WP_Post|WP_Comment $object      Current Object
	 */
	public static function add_meta_boxes( $object_name, $object = null ) {

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
				$object_name = $object->post_type;
			} elseif ( ! empty( $object->comment_ID ) ) {
				$item_id     = $object->comment_ID;
				$object_type = 'comment';
				$object_name = $object->comment_type;

				if ( empty( $object_name ) ) {
					$object_name = 'comment';
				}
			} elseif ( 'comment' == $object_name ) {
				$item_id     = $object->comment_ID;
				$object_type = 'comment';
			}
		}

		$form_id = $object_type . '-edit';

		// Get registered sections
		$sections = $wp_fields->get_sections( $object_type, $object_name, $form_id );

		foreach ( $sections as $section ) {
			// Skip non meta boxes
			if ( ! is_a( $section, 'WP_Fields_API_Meta_Box_Section' ) ) {
				continue;
			}

			/**
			 * @var $section WP_Fields_API_Meta_Box_Section
			 */

			// Pass object name into section
			$section->object_name = $object_name;

			if ( ! $section->check_capabilities() ) {
				continue;
			}

			// Meta boxes don't display section titles
			$section->display_label = false;

			// Set callback arguments
			$callback_args = array(
				'fields_api' => true,
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
				array( $section, 'render_meta_box' ),
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
	public function render_meta_box( $object, $box ) {

		if ( empty( $box['args'] ) || empty( $box['args']['fields_api'] ) ) {
			return;
		}

		$item_id     = 0;
		$object_name = null;

		if ( ! empty( $object->ID ) ) {
			// Get Post ID and type
			$item_id     = $object->ID;
			$object_name = $object->post_type;
		} elseif ( ! empty( $object->comment_ID ) ) {
			// Get Comment ID and type
			$item_id     = $object->comment_ID;
			$object_name = $object->comment_type;

			if ( empty( $object_name ) ) {
				$object_name = 'comment';
			}
		}

		$form = $this->get_form();

		if ( $form ) {
			$form->item_id     = $item_id;
			$form->object_name = $object_name;
		}

		$form->object_name = $object_name;

		$this->maybe_render();

	}

}