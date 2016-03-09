<?php
/**
 * Add a new section to a settings page.
 *
 * Part of the Settings API. Use this to define new settings sections for an admin page.
 * Show settings sections in your admin page callback function with do_settings_sections().
 * Add settings fields to your section with add_settings_field()
 *
 * The $callback argument should be the name of a function that echoes out any
 * content you want to show at the top of the settings section before the actual
 * fields. It can output nothing if you want.
 *
 * @since 2.7.0
 *
 * @global $wp_settings_sections Storage array of all settings sections added to admin pages
 *
 * @param string $id       Slug-name to identify the section. Used in the 'id' attribute of tags.
 * @param string $title    Formatted title of the section. Shown as the heading for the section.
 * @param string $callback Function that echos out any content at the top of the section (between heading and fields).
 * @param string $page     The slug-name of the settings page on which to show the section. Built-in pages include 'general', 'reading', 'writing', 'discussion', 'media', etc. Create your own using add_options_page();
 */
function fields_api_add_settings_section($id, $title, $callback, $page) {
	global $wp_settings_sections;

	if ( 'misc' == $page ) {
		_deprecated_argument( __FUNCTION__, '3.0', sprintf( __( 'The "%s" options group has been removed. Use another settings group.' ), 'misc' ) );
		$page = 'general';
	}

	if ( 'privacy' == $page ) {
		_deprecated_argument( __FUNCTION__, '3.5', sprintf( __( 'The "%s" options group has been removed. Use another settings group.' ), 'privacy' ) );
		$page = 'reading';
	}

	$wp_settings_sections[$page][$id] = array('id' => $id, 'title' => $title, 'callback' => $callback);

	/**
	 * WP Fields API implementation >>>
	 */

	/**
	 * @var $wp_fields WP_Fields_API
	 */
	global $wp_fields;

	$section_args = array(
		'label'                => $title,
		'description_callback' => $callback,
		'form'                 => 'settings-' . $page,
	);

	$wp_fields->add_section( 'settings', $id, null, $section_args );

	/**
	 * <<< WP Fields API implementation
	 */
}

/**
 * Add a new field to a section of a settings page
 *
 * Part of the Settings API. Use this to define a settings field that will show
 * as part of a settings section inside a settings page. The fields are shown using
 * do_settings_fields() in do_settings-sections()
 *
 * The $callback argument should be the name of a function that echoes out the
 * html input tags for this setting field. Use get_option() to retrieve existing
 * values to show.
 *
 * @since 2.7.0
 * @since 4.2.0 The `$class` argument was added.
 *
 * @global $wp_settings_fields Storage array of settings fields and info about their pages/sections
 *
 * @param string $id       Slug-name to identify the field. Used in the 'id' attribute of tags.
 * @param string $title    Formatted title of the field. Shown as the label for the field
 *                         during output.
 * @param string $callback Function that fills the field with the desired form inputs. The
 *                         function should echo its output.
 * @param string $page     The slug-name of the settings page on which to show the section
 *                         (general, reading, writing, ...).
 * @param string $section  Optional. The slug-name of the section of the settings page
 *                         in which to show the box. Default 'default'.
 * @param array  $args {
 *     Optional. Extra arguments used when outputting the field.
 *
 *     @type string $label_for When supplied, the setting title will be wrapped
 *                             in a `<label>` element, its `for` attribute populated
 *                             with this value.
 *     @type string $class     CSS Class to be added to the `<tr>` element when the
 *                             field is output.
 * }
 */
function fields_api_add_settings_field($id, $title, $callback, $page, $section = 'default', $args = array()) {
	global $wp_settings_fields;

	if ( 'misc' == $page ) {
		_deprecated_argument( __FUNCTION__, '3.0', __( 'The miscellaneous options group has been removed. Use another settings group.' ) );
		$page = 'general';
	}

	if ( 'privacy' == $page ) {
		_deprecated_argument( __FUNCTION__, '3.5', __( 'The privacy options group has been removed. Use another settings group.' ) );
		$page = 'reading';
	}

	$wp_settings_fields[$page][$section][$id] = array('id' => $id, 'title' => $title, 'callback' => $callback, 'args' => $args);

	/**
	 * WP Fields API implementation >>>
	 */

	/**
	 * @var $wp_fields WP_Fields_API
	 */
	global $wp_fields;

	$control_args = array(
		'label'           => $title,
		'render_callback' => $callback,
		'section'         => $section,
		'settings_args'   => $args,
	);

	$wp_fields->add_control( 'settings', $id, null, $control_args );

	/**
	 * <<< WP Fields API implementation
	 */
}

/**
 * Prints out all settings sections added to a particular settings page
 *
 * Part of the Settings API. Use this in a settings page callback function
 * to output all the sections and fields that were added to that $page with
 * add_settings_section() and add_settings_field()
 *
 * @global $wp_settings_sections Storage array of all settings sections added to admin pages
 * @global $wp_settings_fields Storage array of settings fields and info about their pages/sections
 * @since 2.7.0
 *
 * @param string $page The slug name of the page whose settings sections you want to output
 */
function fields_api_do_settings_sections( $page ) {
	/**
	 * WP Fields API implementation >>>
	 */

	/**
	 * @var $wp_fields WP_Fields_API
	 */
	global $wp_fields;

	$form = $wp_fields->get_form( 'settings', 'settings-' . $page );

	if ( $form ) {
		$form->maybe_render();
	}

	return;

	/**
	 * <<< WP Fields API implementation
	 */

	global $wp_settings_sections, $wp_settings_fields;

	if ( ! isset( $wp_settings_sections[$page] ) )
		return;

	foreach ( (array) $wp_settings_sections[$page] as $section ) {
		if ( $section['title'] )
			echo "<h2>{$section['title']}</h2>\n";

		if ( $section['callback'] )
			call_user_func( $section['callback'], $section );

		if ( ! isset( $wp_settings_fields ) || !isset( $wp_settings_fields[$page] ) || !isset( $wp_settings_fields[$page][$section['id']] ) )
			continue;
		echo '<table class="form-table">';
		do_settings_fields( $page, $section['id'] );
		echo '</table>';
	}
}

/**
 * Print out the settings fields for a particular settings section
 *
 * Part of the Settings API. Use this in a settings page to output
 * a specific section. Should normally be called by do_settings_sections()
 * rather than directly.
 *
 * @global $wp_settings_fields Storage array of settings fields and their pages/sections
 *
 * @since 2.7.0
 *
 * @param string $page Slug title of the admin page who's settings fields you want to show.
 * @param string $section Slug title of the settings section who's fields you want to show.
 */
function fields_api_do_settings_fields($page, $section) {
	/**
	 * WP Fields API implementation >>>
	 */

	/**
	 * @var $wp_fields WP_Fields_API
	 */
	global $wp_fields;

	$section = $wp_fields->get_section( 'settings', $section );

	if ( $section ) {
		$section->maybe_render();
	}

	return;

	/**
	 * <<< WP Fields API implementation
	 */

	global $wp_settings_fields;

	if ( ! isset( $wp_settings_fields[$page][$section] ) )
		return;

	foreach ( (array) $wp_settings_fields[$page][$section] as $field ) {
		$class = '';

		if ( ! empty( $field['args']['class'] ) ) {
			$class = ' class="' . esc_attr( $field['args']['class'] ) . '"';
		}

		echo "<tr{$class}>";

		if ( ! empty( $field['args']['label_for'] ) ) {
			echo '<th scope="row"><label for="' . esc_attr( $field['args']['label_for'] ) . '">' . $field['title'] . '</label></th>';
		} else {
			echo '<th scope="row">' . $field['title'] . '</th>';
		}

		echo '<td>';
		call_user_func($field['callback'], $field['args']);
		echo '</td>';
		echo '</tr>';
	}
}