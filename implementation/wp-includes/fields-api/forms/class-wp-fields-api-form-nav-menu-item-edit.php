<?php
/**
 * This is an implementation for Fields API for the Nav Menu Item editor in the WordPress Dashboard
 *
 * @package    WordPress
 * @subpackage Fields_API
 */

/**
 * Class WP_Fields_API_Form_Nav_Menu_Item_Edit
 */
class WP_Fields_API_Form_Nav_Menu_Item_Edit extends WP_Fields_API_Form {

	/**
	 * {@inheritdoc}
	 */
	public function register_fields( $wp_fields ) {

		add_filter( 'wp_edit_nav_menu_walker', array( $this, 'wp_edit_nav_menu_walker' ), 10, 2 );
		add_action( 'wp_update_nav_menu_item', array( $this, 'wp_update_nav_menu_item' ), 10, 3 );

		/////////////////////////
		// Core: Basic Details //
		/////////////////////////

		$section_id   = $this->id . '-basic-details';
		$section_args = array(
			'label'    => __( 'Basic Details' ),
			'controls' => array(),
		);

		// Control: URL
		$section_args['controls']['menu-item-url'] = array(
			'type'                  => 'url',
			'label'                 => __( 'URL' ),
			'capabilities_callback' => array( $this, 'capability_is_custom_nav_item' ),
			'internal'              => true,
		);

		// Control: Navigation Label
		$section_args['controls']['menu-item-title'] = array(
			'type'     => 'text',
			'label'    => __( 'Navigation Label' ),
			'internal' => true,
		);

		// Control: Title Attribute
		$section_args['controls']['menu-item-attr-title'] = array(
			'type'     => 'text',
			'label'    => __( 'Title Attribute' ),
			'internal' => true,
		);

		// Control: Open link in a new tab
		$section_args['controls']['menu-item-target'] = array(
			'type'     => 'checkbox',
			'label'    => __( 'Open link in a new tab' ),
			'internal' => true,
		);

		// Control: CSS Classes (optional)
		$section_args['controls']['menu-item-classes'] = array(
			'type'     => 'text',
			'label'    => __( 'CSS Classes (optional)' ),
			'internal' => true,
		);

		// Control: Link Relationship (XFN)
		$section_args['controls']['menu-item-xfn'] = array(
			'type'     => 'text',
			'label'    => __( 'Link Relationship (XFN)' ),
			'internal' => true,
		);

		// Control: Description
		$section_args['controls']['menu-item-description'] = array(
			'type'        => 'textarea',
			'label'       => __( 'Description' ),
			'description' => __( 'The description will be displayed in the menu if the current theme supports it.' ),
			'input_attrs' => array(
				'rows' => 3,
				'cols' => 20,
			),
			'internal'    => true,
		);

		$this->add_section( $section_id, $section_args );

	}

	/**
	 * {@inheritdoc}
	 */
	public function save_fields( $item_id = null, $object_subtype = null ) {

		// Save additional fields
		return parent::save_fields( $item_id, $object_subtype );

	}

	/**
	 * Swap-out the default Nav_Menu_Walker implementation
	 *
	 * @param string $walker
	 * @param int    $menu_id
	 */
	function wp_edit_nav_menu_walker( $walker, $menu_id ) {

		return 'WP_Fields_API_Walker_Nav_Menu_Edit';

	}

	/**
	 * Handle saving of Nav Menu Item fields
	 *
	 * @param int   $menu_id
	 * @param int   $menu_item_db_id
	 * @param array $args
	 */
	public function wp_update_nav_menu_item( $menu_id, $menu_item_db_id, $args ) {

		$this->save_fields( $menu_item_db_id );

	}

	/**
	 * Controls visible if not custom nav item
	 *
	 * @param WP_Fields_API_Control $control
	 *
	 * @return bool
	 */
	public function capability_is_not_custom_nav_item( $control ) {

		$has_access = true;

		if ( ! empty( $control->nav_item_type ) && 'custom' == $control->nav_item_type ) {
			$has_access = false;
		}

		return $has_access;

	}

	/**
	 * Controls visible if custom nav item
	 *
	 * @param WP_Fields_API_Control $control
	 *
	 * @return bool
	 */
	public function capability_is_custom_nav_item( $control ) {

		$has_access = true;

		if ( empty( $control->nav_item_type ) || 'custom' != $control->nav_item_type ) {
			$has_access = false;
		}

		return $has_access;

	}

}