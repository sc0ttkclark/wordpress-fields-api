# Fields API Registration

## Screens

Screens in the Fields API are more commonly linked to what appear in the WordPress Admin area as WP_Screen. A few come with the Fields API itself, but you can register new screens to your heart's content and output them wherever you'd like.

See [Creating an Implementation](https://github.com/sc0ttkclark/wordpress-fields-api/blob/master/docs/creating-an-implementation.md) for examples of creating your own intelligent screens.

### Registering screens

When a screen needs no saving or rendering mechanism (see [Creating an Implementation](https://github.com/sc0ttkclark/wordpress-fields-api/blob/master/docs/creating-an-implementation.md)), it can be registered through the `fields_register` action, using code like this:

```php
// Object type and Screen ID
$object_type = 'my-xyz';
$screen_id = 'my-screen';
	
// Set this to a specific post type, taxonomy,
// or comment type you want to register for
$object_name = null;

// Register screen
$wp_fields->add_screen( $object_type, $screen_id, $object_name );
```

**Please note:** Registering screens are not normally required for working within the existing WordPress Admin area unless you need custom admin screens. They can also be used on the front-end as well.
 
## Sections

Sections are groupings of controls that give context to what they are for.

### Registering sections

Sections can be registered through the `fields_register` action, using code like this:

```php
// Object type and Screen ID
$object_type = 'term';
$screen_id = 'term-edit';
	
// Set this to a specific post type, taxonomy,
// or comment type you want to register for
$object_name = 'xyz';

// Section ID and options
$section_id = 'my-section'; // @todo Fill in section ID
$section_args = array(
	'title'  => __( 'My Section', 'my-text-domain' ), // @todo Fill in section heading, update text domain
	'screen' => $screen_id,
);

// Register section
$wp_fields->add_section( $object_type, $section_id, $object_name, $section_args );
```

## Controls

Controls are the input fields used to display field values and allow for changes to those field values.

### Control Types

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
// Object type and Screen ID
$object_type = 'term';
$screen_id = 'term-edit';
	
// Set this to a specific post type, taxonomy,
// or comment type you want to register for
$object_name = 'xyz';

// Control ID and options
$control_id = 'my-control'; // @todo Fill in control ID
$control_args = array(
	'type'        => 'text', // @todo Change control type if needed
	'section'     => $section_id,
	'label'       => __( 'My Field', 'my-text-domain' ), // @todo Fill in label, update text domain
	'description' => __( 'Description of My Field', 'my-text-domain' ), // @todo Fill in description, update text domain
	'fields'      => 'my-field', // @todo Fill in field ID
);

// Register control
$wp_fields->add_control( $object_type, $control_id, $object_name, $control_args );
```

## Fields

Fields handle getting and saving of values and should be namespaced properly (`{my_}field_name`) for your project. They should be unique for the object type and object name you use it on, otherwise it will be overwritten each time a duplicate one is added.

### Registering fields (and control)

Fields can be registered through the `fields_register` action, using code like this:

```php
// Object type
$object_type = 'term';
	
// Set this to a specific post type, taxonomy,
// or comment type you want to register for
$object_name = 'xyz';

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
$wp_fields->add_field( $object_type, $field_id, $object_name, $field_args );
```

### Registering fields (standalone)

Fields can be registered on their own, without associating to a control, through the `fields_register` action, using code like this:

```php
// Object type
$object_type = 'term';
	
// Set this to a specific post type, taxonomy,
// or comment type you want to register for
$object_name = 'xyz';

// Field ID and options
$field_id = 'my-field'; // @todo Fill in field ID
$field_args = array();

// Register field
$wp_fields->add_field( $object_type, $field_id, $object_name, $field_args );
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

	// Object name: XYZ
	$object_name = 'xyz'; // @todo Change to any taxonomy name

	// Screen: Term Edit
	$screen_id = 'term-edit'; // @todo Also available is term-add

	/////////////////////////
	// Section: My Section //
	/////////////////////////

	$section_id = 'my-section'; // @todo Fill in section ID
	$section_args = array(
		'title'  => __( 'My Section', 'my-text-domain' ), // @todo Fill in section heading, update text domain
		'screen' => $screen_id,
	);

	$wp_fields->add_section( $object_type, $section_id, $object_name, $section_args );

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

	$wp_fields->add_field( $object_type, $field_id, $object_name, $field_args );


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