# Fields API Registration (Shorthand)

## Prerequisite

You should be familiar with the normal [Fields API Registration](https://github.com/sc0ttkclark/wordpress-fields-api/blob/master/docs/registering-fields.md).

## Sections

Sections are groupings of controls that give context to what they are for.

### Registering sections and controls

Sections and controls can be registered together through the `fields_register` action, using code like this:

```php
// Object type and Form ID
$object_type = 'term';
$form_id = 'term-edit';
	
// Set this to a specific post type, taxonomy,
// or comment type you want to register for
$object_name = 'xyz';

// Section ID and options
$section_id = 'my-section'; // @todo Fill in section ID
$section_args = array(
	'label'  => __( 'My Section', 'my-text-domain' ), // @todo Fill in section heading, update text domain
	'form' => $form_id,
	'controls' => array(
		// List of controls for this section
		'my-control' => array(
			'type'        => 'text', // @todo Change control type if needed
			'label'       => __( 'My Field', 'my-text-domain' ), // @todo Fill in label, update text domain
			'description' => __( 'Description of My Field', 'my-text-domain' ), // @todo Fill in description, update text domain
		),
	),
);

// Register section
$wp_fields->add_section( $object_type, $section_id, $object_name, $section_args );
```
