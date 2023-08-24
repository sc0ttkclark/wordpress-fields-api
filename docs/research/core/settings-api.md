# Settings API

## Registering Forms

To create a custom form with the settings API, you need to first register a new options page:

```php
add_action( 'admin_menu', 'fields_test_options_page' );
function fields_test_options_page() {
	add_options_page(
		'Your option page title',
		'Your menu title',
		'manage_options',
		'your-menu-slug',
		'fields_test_options'
	);
}
```

Then, register a setting, add a settings section, and add a field to that section.

```php
add_action( 'admin_init', 'fields_test_settings_init' );
function fields_test_settings_init() {
	register_setting( 'your-setting-form', 'fields_test_setting' );

	add_settings_section(
		'fields_test_settings_section',
		__( 'Fields Test Section Title', 'fields-test' ),
		'fields_test_settings_callback',
		'your-setting-form'
	);

	add_settings_field(
		'fields_test_0',
		__( 'Field test 0', 'fields-test' ),
		'fields_test_render',
		'your-setting-form',
		'fields_test_settings_section'
	);
}
```

Next, write the renderer for the input field & values.

```php
function fields_test_render() {
	$options = get_option( 'fields_test_setting' );
	?>
	<input type='text' name='fields_test_setting' value='<?php echo esc_attr( $options ); ?>'>
	<?php
}
```

Optionally, use `fields_test_settings_callback` to add a text description of your settings section, between the header
and the table containing fields.

```php
function fields_test_settings_callback() {
	_e( 'Fields Test Section Description', 'fields_test_settings_section' );
}
```

Define the output for your admin settings page:

```php
function fields_test_options() {
	?>
	<form action='options.php' method='post'>

		<h1>Fields Test Settings API Admin Page</h1>

		<?php
		settings_fields( 'your-setting-form' );
		do_settings_sections( 'your-setting-form' );
		submit_button();
		?>

	</form>
	<?php
}
```

## Registering Sections

Defines a section that contains fields on an admin page. Callback names a function that echoes content between the
heading and contained fields. `$page` is a slug-formatted name used by `add_settings_field()`
and `do_settings_sections()`

```php
add_settings_section(
	$id,
	$title,
	$callback,
	$page,
	[
		'before_section' => '',
		'after_section'  => '',
		'section_class'  => '',
	],
);
```

Output the separate sections declared for a given `$page` (form). Sections have predetermined HTML with each section
inside a table.

```php
do_settings_sections( $page );
```

## Registering Fields

The settings API requires you to register a setting, which declares the option name and data type; this does not
register a field, but is required to save the field.

```php
register_setting(
	$option_group,
	$option_name,
	[
		'type' => 'string',
		'description' => '',
		'sanitize_callback' => '',
		'show_in_rest' => [
			'name'   => 'email',
			'schema' => [
				'format' => 'email',
			],
		],
		'default' => '',
	]
);
```

Adding a settings fields adds field output to a field produced using `do_settings_fields()`
inside `do_settings_sections()`.

* `$page` is the settings page where the field will be shown.
* `$section` is the section the field is in.
* `$callback` is a function that echos the input field, not including the label element.

```php
add_settings_field(
	$id,
	$title,
	$callback,
	$page,
	$section,
	[
		'label_for' => 'some-id',
		'class' => 'some-css-class',
	]
);
```

Prints all settings registered for this section & page. Each field is inside one row of the containing section table.

```php
do_settings_fields( $page, $section );
```

# Lessons to be learned from the Settings API approach

The Settings API has been around since the end of 2008 and is a core API within WordPress itself.

The Settings API itself is mostly data structure and still requires a lot of HTML markup to be written by the
developer to piece together the implementation of a custom settings page.

Function calls are used everywhere in the Settings API for registering settings and outputting them.

| Registration Type  | Supports JSON Files | Supports Multiple Content Types | Form / HTML markup required |
|--------------------|---------------------|---------------------------------|-----------------------------|
| PHP function calls | No                  | No, this is only for Settings   | Yes                         |
