# Fields API Registration (Shorthand)

<!-- START doctoc generated TOC please keep comment here to allow auto update -->
<!-- DON'T EDIT THIS SECTION, INSTEAD RE-RUN doctoc TO UPDATE -->
**Table of Contents**  *generated with [DocToc](https://github.com/thlorenz/doctoc)*

- [Prerequisite](#prerequisite)
- [Sections](#sections)
  - [Registering sections and controls](#registering-sections-and-controls)

<!-- END doctoc generated TOC please keep comment here to allow auto update -->

---

## Prerequisite

You should be familiar with the normal [Fields API Registration](https://github.com/sc0ttkclark/wordpress-fields-api/blob/develop/docs/registering-fields.md).

## Sections

Sections are groupings of controls that give context to what they are for.

### Registering sections and controls

Sections and controls can be registered together through the `fields_register` action, using code like this:

```php
// Form ID
$form_id = 'term-edit';

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
$wp_fields->add_section( $section_id, $section_args );
```
