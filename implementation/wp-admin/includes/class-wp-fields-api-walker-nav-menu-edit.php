<?php
/**
 * This is an implementation for Fields API for the Nav Menu Items in the menu editor screens of the WordPress Dashboard.
 *
 * @package    WordPress
 * @subpackage Fields_API
 */

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
		/**
		 * WP Fields API implementation >>>
		 */

		/**
		 * @var $wp_fields WP_Fields_API
		 */
		global $wp_fields;

		$form = $wp_fields->get_form( 'post', 'nav-menu-item-edit', 'nav_menu_item' );

		$form->item = $item;
		$form->item_id = $item_id;

		$form->render_nonce();

		$sections = $form->get_sections();

		foreach ( $sections as $section ) {
			$controls = $section->get_controls();
			?>
				<div class="menu-item-settings" id="menu-item-settings-<?php echo $item_id; ?>">
					<?php foreach ( $controls as $control ) { ?>
						<?php
							// Pass $item->type and $item->id to Control
							$control->nav_item_type = $item->type;

							if ( ! $control->check_capabilities() ) {
								continue;
							}

							$label       = $control->label;
							$description = $control->description;

							// Avoid outputting them in render_content()
							$control->label       = '';
							$control->description = '';

							// Setup field name
							$control->input_attrs['name'] = $control->id . '[' . $item_id . ']';

							// @todo Handle input class variations, if they are the same as in Customizer, maybe move this into fields_register config
							$control->input_attrs['class'] = 'widefat edit-' . $control->id;

							if ( in_array( $control->id, array( 'menu-item-classes', 'menu-item-xfn' ) ) ) {
								$control->input_attrs['class'] .= ' code';
							}

							// @todo Handle wrapper class variations, if they are the same as in Customizer, maybe move this into fields_register config
							$wrapper_class = '';
						?>
						<p class="<?php echo esc_attr( $wrapper_class ); ?>">
							<?php if ( $label ) { ?>
								<label for="edit-menu-item-<?php echo esc_attr( $control->id ); ?>">
									<?php echo esc_html( $label ); ?>
									<br />
							<?php } ?>

							<?php $control->maybe_render(); ?>

							<?php if ( $description ) { ?>
								<span class="description">
									<?php echo wp_kses_post( $description ); ?>
								</span>
							<?php } ?>

							<?php if ( $label ) { ?>
								</label>
							<?php } ?>
						</p>
					<?php } ?>
				</div>
			<?php
		}

		/**
		 * <<< WP Fields API implementation
		 */
	}
}
