<?php
/**
 * This is an implementation for Fields API for the Nav Menu Items in the menu editor screens of the WordPress Dashboard.
 *
 * @package    WordPress
 * @subpackage Fields_API
 */

/**
 * Class WP_Fields_API_User_Profile
 */
class WP_Fields_API_Nav_Menu_Items {

	public function __construct() {

		add_filter( 'wp_edit_nav_menu_walker', array( $this, 'swap_edit_nav_menu_walker' ), 10, 2 );

		add_action( 'wp_update_nav_menu_item', array( $this, 'save_fields' ), 10, 3 );

	}

	/**
	 * Swap-out the default Nav_Menu_Walker implementation
	 *
	 * @param string $walker
	 * @param int    $menu_id
	 */
	function swap_edit_nav_menu_walker( $walker, $menu_id ) {

		return 'WP_Fields_API_Walker_Nav_Menu_Edit';

	}

	/**
	 * Handle saving of Nav Menu Item fields
	 *
	 * @param int   $menu_id
	 * @param int   $menu_item_db_id
	 * @param array $args
	 */
	public function save_fields( $menu_id, $menu_item_db_id, $args ) {

		if ( ! empty( $_REQUEST['wp_fields_api_fields_save'] ) && false !== wp_verify_nonce( $_REQUEST['wp_fields_api_fields_save'], 'wp_fields_api_nav_menu_item' ) ) {
			/**
			 * @var $wp_fields WP_Fields_API
			 */
			global $wp_fields;

			$controls = $wp_fields->get_controls( 'nav_menu_item' );

			foreach ( $controls as $control ) {
				if ( empty( $control->field ) ) {
					continue;
				}

				$field = $control->field;

				// Get value from $_POST
				$value = null;

				if ( ! empty( $_POST[ 'field_' . $control->id ] ) ) {
					$value = $_POST[ 'field_' . $control->id ];
				}

				// Sanitize
				$value = $field->sanitize( $value );

				// Save value
				$field->save( $value, $menu_item_db_id );
			}
		}

	}

	/**
	 * Register controls for Nav Menu Items
	 *
	 * @todo Move out of wp-admin implementation
	 */
	public function register_controls() {

		/**
		 * @var $wp_fields WP_Fields_API
		 */
		global $wp_fields;

		// Register control types
		$wp_fields->register_control_type( 'menu-item-text', 'WP_Fields_API_Nav_Menu_Control' );
		$wp_fields->register_control_type( 'menu-item-url', 'WP_Fields_API_Nav_Menu_URL_Control' );

		// Add Edit Nav Menu Item screen
		$wp_fields->add_screen( 'nav_menu_item', 'edit-menu-item' );

		$wp_fields->add_section( 'nav_menu_item', 'basic-details', 'edit-menu-item', array(
			'title' => __( 'Basic Details' ),
		) );

		$field_args = array(
			'control' => array(
				'type'    => 'menu-item-url',
				'section' => 'basic-details',
				'label'   => __( 'URL' ),
			),
		);

		$wp_fields->add_field( 'nav_menu_item', 'menu-item-url', 'edit-menu-item', $field_args );

		$field_args = array(
			'control' => array(
				'type'    => 'text',
				'section' => 'basic-details',
				'label'   => __( 'Navigation Label' ),
			),
		);

		$wp_fields->add_field( 'nav_menu_item', 'menu-item-title', 'edit-menu-item', $field_args );

		$field_args = array(
			'control' => array(
				'type'    => 'text',
				'section' => 'basic-details',
				'label'   => __( 'Title Attribute' ),
			),
		);

		$wp_fields->add_field( 'nav_menu_item', 'menu-item-attr-title', 'edit-menu-item', $field_args );

		$field_args = array(
			'control' => array(
				'type'    => 'text',
				'section' => 'basic-details',
				'label'   => __( 'Open link in a new tab' ),
			),
		);

		$wp_fields->add_field( 'nav_menu_item', 'menu-item-target', 'edit-menu-item', $field_args );

		$field_args = array(
			'control' => array(
				'type'    => 'text',
				'section' => 'basic-details',
				'label'   => __( 'CSS Classes (optional)' ),
			),
		);

		$wp_fields->add_field( 'nav_menu_item', 'menu-item-classes', 'edit-menu-item', $field_args );

		$field_args = array(
			'control' => array(
				'type'    => 'text',
				'section' => 'basic-details',
				'label'   => __( 'Link Relationship (XFN)' ),
			),
		);

		$wp_fields->add_field( 'nav_menu_item', 'menu-item-xfn', 'edit-menu-item', $field_args );

		$field_args = array(
			'control' => array(
				'type'    => 'textarea',
				'section' => 'basic-details',
				'label'   => __( 'Description' ),
				'description' => __( 'The description will be displayed in the menu if the current theme supports it.')
			),
		);

		$wp_fields->add_field( 'nav_menu_item', 'menu-item-description', 'edit-menu-item', $field_args );

	}
}


/**
 * Fields API Nav Menu Edit screen walker.
 *
 * @see Walker_Nav_Menu_Edit
 */
class WP_Fields_API_Walker_Nav_Menu_Edit extends Walker_Nav_Menu {
	// @todo: possibly add a filter or action into the original `Walker_Nav_Menu_Edit` class to publish the fields instead of duplicating the class.

	/**
	 * Starts the list before the elements are added.
	 *
	 * @see Walker_Nav_Menu::start_lvl()
	 *
	 * @since 3.0.0
	 *
	 * @param string $output Passed by reference.
	 * @param int    $depth  Depth of menu item. Used for padding.
	 * @param array  $args   Not used.
	 */
	public function start_lvl( &$output, $depth = 0, $args = array() ) {}

	/**
	 * Ends the list of after the elements are added.
	 *
	 * @see Walker_Nav_Menu::end_lvl()
	 *
	 * @since 3.0.0
	 *
	 * @param string $output Passed by reference.
	 * @param int    $depth  Depth of menu item. Used for padding.
	 * @param array  $args   Not used.
	 */
	public function end_lvl( &$output, $depth = 0, $args = array() ) {}

	/**
	 * Start the element output.
	 *
	 * @see Walker_Nav_Menu::start_el()
	 * @since 3.0.0
	 *
	 * @global int $_wp_nav_menu_max_depth
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param object $item   Menu item data object.
	 * @param int    $depth  Depth of menu item. Used for padding.
	 * @param array  $args   Not used.
	 * @param int    $id     Not used.
	 */
	public function start_el( &$output, $item, $depth = 0, $args = array(), $id = 0 ) {
		global $_wp_nav_menu_max_depth;

		/**
		 * @var $wp_fields WP_Fields_API
		 */
		global $wp_fields;

		$screen = $wp_fields->get_screen( 'nav_menu_item', 'edit-item' );

		$nonced = false;


		$_wp_nav_menu_max_depth = $depth > $_wp_nav_menu_max_depth ? $depth : $_wp_nav_menu_max_depth;

		ob_start();
		$item_id = esc_attr( $item->ID );
		$removed_args = array(
			'action',
			'customlink-tab',
			'edit-menu-item',
			'menu-item',
			'page-tab',
			'_wpnonce',
		);

		$original_title = '';
		if ( 'taxonomy' == $item->type ) {
			$original_title = get_term_field( 'name', $item->object_id, $item->object, 'raw' );
			if ( is_wp_error( $original_title ) )
				$original_title = false;
		} elseif ( 'post_type' == $item->type ) {
			$original_object = get_post( $item->object_id );
			$original_title = get_the_title( $original_object->ID );
		} elseif ( 'post_type_archive' == $item->type ) {
			$original_object = get_post_type_object( $item->object );
			if ( $original_object ) {
				$original_title = $original_object->labels->archives;
			}
		}

		$classes = array(
			'menu-item menu-item-depth-' . $depth,
			'menu-item-' . esc_attr( $item->object ),
			'menu-item-edit-' . ( ( isset( $_GET['edit-menu-item'] ) && $item_id == $_GET['edit-menu-item'] ) ? 'active' : 'inactive'),
		);

		$title = $item->title;

		if ( ! empty( $item->_invalid ) ) {
			$classes[] = 'menu-item-invalid';
			/* translators: %s: title of menu item which is invalid */
			$title = sprintf( __( '%s (Invalid)' ), $item->title );
		} elseif ( isset( $item->post_status ) && 'draft' == $item->post_status ) {
			$classes[] = 'pending';
			/* translators: %s: title of menu item in draft status */
			$title = sprintf( __('%s (Pending)'), $item->title );
		}

		$title = ( ! isset( $item->label ) || '' == $item->label ) ? $title : $item->label;

		$submenu_text = '';
		if ( 0 == $depth )
			$submenu_text = 'style="display: none;"';

		?>
		<li id="menu-item-<?php echo $item_id; ?>" class="<?php echo implode(' ', $classes ); ?>">
			<div class="menu-item-bar">
				<div class="menu-item-handle">
					<span class="item-title"><span class="menu-item-title"><?php echo esc_html( $title ); ?></span> <span class="is-submenu" <?php echo $submenu_text; ?>><?php _e( 'sub item' ); ?></span></span>
					<span class="item-controls">
						<span class="item-type"><?php echo esc_html( $item->type_label ); ?></span>
						<span class="item-order hide-if-js">
							<a href="<?php
								echo wp_nonce_url(
									add_query_arg(
										array(
											'action' => 'move-up-menu-item',
											'menu-item' => $item_id,
										),
										remove_query_arg($removed_args, admin_url( 'nav-menus.php' ) )
									),
									'move-menu_item'
								);
							?>" class="item-move-up" aria-label="<?php esc_attr_e( 'Move up' ) ?>">&#8593;</a>
							|
							<a href="<?php
								echo wp_nonce_url(
									add_query_arg(
										array(
											'action' => 'move-down-menu-item',
											'menu-item' => $item_id,
										),
										remove_query_arg($removed_args, admin_url( 'nav-menus.php' ) )
									),
									'move-menu_item'
								);
							?>" class="item-move-down" aria-label="<?php esc_attr_e( 'Move down' ) ?>">&#8595;</a>
						</span>
						<a class="item-edit" id="edit-<?php echo $item_id; ?>" href="<?php
							echo ( isset( $_GET['edit-menu-item'] ) && $item_id == $_GET['edit-menu-item'] ) ? admin_url( 'nav-menus.php' ) : add_query_arg( 'edit-menu-item', $item_id, remove_query_arg( $removed_args, admin_url( 'nav-menus.php#menu-item-settings-' . $item_id ) ) );
						?>" aria-label="<?php esc_attr_e( 'Edit menu item' ); ?>"><?php _e( 'Edit' ); ?></a>
					</span>
				</div>
			</div>
		<?php

		if ( $screen ) {
			$sections = $wp_fields->get_sections( 'nav_menu_item', null, $screen->id );

			if ( ! empty( $sections ) ) {
				foreach ( $sections as $section ) {
					$controls = $wp_fields->get_controls( 'nav_menu_item', null, $section->id );

					if ( $controls ) {
						$content = $section->get_content();

						if ( $content ) {
							if ( ! $nonced ) {
								$nonced = true;

								wp_nonce_field( 'wp_fields_api_nav_menu_item', 'wp_fields_api_fields_save' );
							}
							?>
							<div class="menu-item-settings" id="menu-item-settings-<?php echo $item_id; ?>">
								<?php foreach ( $controls as $control ) { ?>
									<?php
									// Pass $item->type and $item->id to Control
									if ( isset( $control->item_type ) ) {
										$control->item_type = $item->type;
									}
									$control->item_id   = $item->ID

									$label       = $control->label;
									$description = $control->description;

									// Avoid outputting them in render_content()
									$control->label       = '';
									$control->description = '';

									// Setup field name
									$control->input_attrs['name'] = 'field_' . $control->id;
									?>
									<p class="<?php echo esc_attr( $control->class; ) ?>">
										<?php if ( $label ) { ?>
											<label for="edit-menu-item-<?php echo esc_attr( $control->id ); ?>"><?php esc_html( $label ); ?></label>
										<?php } ?>
										<?php $control->render_content(); ?>
									</p>
								<?php } ?>
							</div>
							<?php
						}
					}
				}
			}
		}
	}
}

/**
 * Fields API Nav Menu Item URL Control
 *
 * @see WP_Fields_API_Nav_Menu_Control
 */
class WP_Fields_API_Nav_Menu_URL_Control extends WP_Fields_API_Text_Control {
	/**
	 * Item type of current nav menu item used to decide whether to show this control
	 *
	 * @access public
	 * @var int|string
	 */
	public $item_type;

	/**
	 * {@inheritdoc}
	 */
	public function render_content() {
		if ( 'custom' == $this->item_type ) {
			parent::render_content();
		}
	}
}
