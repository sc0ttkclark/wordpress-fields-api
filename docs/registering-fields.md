# Fields API Registration

<!-- START doctoc generated TOC please keep comment here to allow auto update -->
<!-- DON'T EDIT THIS SECTION, INSTEAD RE-RUN doctoc TO UPDATE -->
**Table of Contents**  *generated with [DocToc](https://github.com/thlorenz/doctoc)*

- [Forms](#forms)
  - [Registering forms](#registering-forms)
- [Sections](#sections)
  - [Registering sections](#registering-sections)
- [Controls](#controls)
  - [Control Types](#control-types)
  - [Registering controls](#registering-controls)
- [Fields](#fields)
  - [Registering fields (and control)](#registering-fields-and-control)
  - [Registering fields (standalone)](#registering-fields-standalone)
- [Bringing it all together to register your configuration](#bringing-it-all-together-to-register-your-configuration)
- [Custom Control Types](#custom-control-types)

<!-- END doctoc generated TOC please keep comment here to allow auto update -->

---

## Forms

Forms in the Fields API are more commonly linked to what appear in the WordPress Admin area as WP_Screen. A few come with the Fields API itself, but you can register new forms to your heart's content and output them wherever you'd like.

See [Custom Component Classes](https://github.com/sc0ttkclark/wordpress-fields-api/blob/develop/docs/custom-component-classes.md) for examples of creating forms with custom saving and rendering.

### Registering forms

When a form needs no custom saving or rendering mechanism (see [Creating an Implementation](https://github.com/sc0ttkclark/wordpress-fields-api/blob/develop/docs/creating-an-implementation.md)), it can be registered through the `fields_register` action, using code like this:

```php
// Object type and Form ID
$object_type = 'my-xyz';
$form_id = 'my-form';

// Register form
$wp_fields->add_form( $object_type, $form_id, );
```

**Please note:** Registering forms are not normally required for working within the existing WordPress Admin area unless you need custom admin forms. They can also be used on the front-end as well.

## Sections

Sections are groupings of controls that give context to what they are for.

### Registering sections

Sections can be registered through the `fields_register` action, using code like this:

```php
// Form ID
$form_id = 'term-edit';

// Section ID and options
$section_id = 'my-section'; // @todo Fill in section ID
$section_args = array(
	'label'  => __( 'My Section', 'my-text-domain' ), // @todo Fill in section heading, update text domain
	'form' => $form_id,
);

// Register section
$wp_fields->add_section( $section_id, $section_args );
```

## Controls

Controls are the input fields used to display field values and allow for changes to those field values.

### Control Types

Control types are the types of controls that are registered and available for general use. Some are included with the Fields API, but custom control types can also be registered for advanced usage.

* `text` - Text input
* `{html5-type}` - Use any HTML5 input type you'd like `<input type="{type} ... />`
* `textarea` - Textarea input
* `checkbox` - Checkbox input
* `multi-checkbox` - Multiple checkbox inputs
* `radio` - Radio inputs
* `select` - Select input (uses `'choices' => array(...)` option when registering control)
* `dropdown-pages` - Pages dropdown input
* `dropdown-terms` - Terms dropdown input
* `color` - Color picker input
* `media` - Media modal input
* `media-file` - Media file input

### Registering controls

Controls can be registered through the `fields_register` action, using code like this:

```php
// Section ID
$section_id = 'term-edit';

// Control ID and options
$control_id = 'my-control';
$control_args = array(
	'type'        => 'text', // @todo Change control type if needed
	'section'     => $section_id,
	'label'       => __( 'My Field', 'my-text-domain' ), // @todo Fill in label, update text domain
	'description' => __( 'Description of My Field', 'my-text-domain' ), // @todo Fill in description, update text domain
	'field'       => 'my-field', // @todo Fill in field ID
);

// Register control
$wp_fields->add_control( $control_id, $control_args );
```

## Fields

Fields handle getting and saving of values and should be namespaced properly (`{my_}field_name`) for your project. They should be completely unique, otherwise it will be overwritten each time a duplicate one is added.

### Registering fields (and control)

Fields can be registered through the `fields_register` action, using code like this:

```php
// Section ID
$section_id = 'my-section'; // @todo Fill in section ID

// Field ID and options
$field_id = 'my-field'; // @todo Fill in field ID
$field_args = array(
	// You can register a control for this field at the same time
	'control' => array(
		// Control ID defaults to the same as the Field ID
		'type'        => 'text', // @todo Change control type if needed
		'section'     => $section_id,
		'label'       => __( 'My Field', 'my-text-domain' ), // @todo Fill in label, update text domain
		'description' => __( 'Description of My Field', 'my-text-domain' ), // @todo Fill in description, update text domain
	),
);

// Register field (and control)
$wp_fields->add_field( $field_id, $field_args );
```

### Registering fields (standalone)

Fields can be registered on their own, without associating to a control, through the `fields_register` action, using code like this:

```php
// Field ID and options
$field_id = 'my-field'; // @todo Fill in field ID
$field_args = array();

// Register field
$wp_fields->add_field( $field_id, $field_args );
```

## Bringing it all together to register your configuration

```php
/**
 * Register Fields API configuration
 *
 * @param WP_Fields_API $wp_fields
 */
function example_my_term_xyz( $wp_fields ) {

	// Object type: Term
	$object_type = 'term';

	// Form: Term Edit
	$form_id = 'term-edit'; // @todo Also available is term-add

	/////////////////////////
	// Section: My Section //
	/////////////////////////

	$section_id = 'my-section'; // @todo Fill in section ID
	$section_args = array(
		'label'  => __( 'My Section', 'my-text-domain' ), // @todo Fill in section heading, update text domain
		'form' => $form_id,
	);

	$wp_fields->add_section( $section_id, $section_args );

	// My Field
	$field_id = 'my-field';
	$field_args = array(
		// You can register a control for this field at the same time
		'control' => array(
			'type'        => 'text', // @todo Change control type if needed
			'section'     => $section_id,
			'label'       => __( 'My Field', 'my-text-domain' ), // @todo Fill in label, update text domain
			'description' => __( 'Description of My Field', 'my-text-domain' ), // @todo Fill in description, update text domain
		),
	);

	$wp_fields->add_field( $field_id, $field_args );


}
add_action( 'fields_register', 'example_my_term_xyz' );
```

## Custom Control Types

Custom control types can be registered through the `fields_register_controls` action, using code like this:

```php
/**
 * Register Fields API custom control type
 *
 * @param WP_Fields_API $wp_fields
 */
function example_my_xyz_control_type( $wp_fields ) {

	$control_type_path = 'path/to/my/files/';

	// Include control type file
	require_once( $control_type_path . 'class-wp-fields-api-my-xyz-type-control.php' );

	// Register control type
	$wp_fields->register_control_type( 'my-xyz-type', 'WP_Fields_API_My_XYZ_Type_Control' );

}
add_action( 'fields_register_controls', 'example_my_xyz_control_type' );
```
`WP_Fields_API_My_XYZ_Type_Control` must extend `WP_Fields_API_Control`
