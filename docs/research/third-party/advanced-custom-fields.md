# Advanced Custom Fields

## Registering Forms

> [acf\_register\_form()](https://www.advancedcustomfields.com/resources/acf_register_form/)

```php
add_action( 'acf/init', 'my_acf_form_init' );
function my_acf_form_init() {
	// Check function exists.
	if ( function_exists( 'acf_register_form' ) ) {
		// Register form.
		acf_register_form( [
			'id'		   => 'new-event',
			'post_id'	   => 'new_post',
			'new_post'	   => [
				'post_type'   => 'event',
				'post_status' => 'publish',
			],
			'post_title'   => true,
			'post_content' => true,
		] );
	}
}
```

### Rendering

You can render forms and specify which field group(s) and/or field(s) to include.

> [acf\_form()](https://www.advancedcustomfields.com/resources/acf_form/)

```php
<?php acf_form( $settings ); ?>
```

## Registering Sections (Field Groups)

> [Register fields via PHP](https://www.advancedcustomfields.com/resources/register-fields-via-php/)

```php
add_action( 'acf/init', 'my_acf_add_local_field_groups' );
function my_acf_add_local_field_groups() {
	acf_add_local_field_group( [
		'key'	  => 'group_1',
		'title'	=> 'My Group',
		'fields'   => [],
		'location' => [
			[
				[
					'param'	=> 'post_type',
					'operator' => '==',
					'value'	=> 'post',
				],
			],
		],
	] );
}
```

## Registering Fields

> [Register fields via PHP](https://www.advancedcustomfields.com/resources/register-fields-via-php/)

```php
add_action( 'acf/init', 'my_acf_add_local_fields' );
function my_acf_add_local_fields() {
	acf_add_local_field( [
		'key'	=> 'field_1',
		'label'  => 'Sub Title',
		'name'   => 'sub_title',
		'type'   => 'text',
		'parent' => 'group_1',
	] );
}
```

## Registering through JSON files

> [Local JSON](https://www.advancedcustomfields.com/resources/local-json/)

ACF allows for registering groups of fields through JSON structures. These are handled by the ACF Pro feature for Local
JSON. When a file is detected, it still must be manually imported to "sync" to the database. Any changes within the
admin dashboard can also be "synced" to the JSON file too.

The JSON files can look something like this:

```json
[
  {
	"key": "group_UNIQUEID",
	"title": "Field group title here",
	"fields": [
	  {
		"key": "field_UNIQUEID",
		"label": "Field label here",
		"name": "field_name_here",
		"type": "text",
		"instructions": "",
		"required": 0,
		"conditional_logic": 0,
		"wrapper": {
		  "width": "50",
		  "class": "",
		  "id": ""
		},
		"default_value": "",
		"placeholder": "",
		"prepend": "",
		"append": "",
		"maxlength": ""
	  }
	],
	"location": [
	  [
		{
		  "param": "post_type",
		  "operator": "==",
		  "value": "post"
		}
	  ]
	],
	"menu_order": 0,
	"position": "normal",
	"active": 1
  }
]
```

# Lessons to be learned from the ACF approach

ACF has chosen to attach groups of fields to single/multiple content types at once. The approach is less architecture
focused and more use-case focused.

With ACF, you choose to add a group of fields to existing content types. Newer versions of ACF includes the ability to
create custom post types and custom taxonomies but the result is the same in the end.

ACF's approach is popular for developers and the easy-to-use UI has led to a high adoption rate.

Whether we could effectively follow a similar approach using location-based rules with the Fields API remains to be
seen.

ACF uses function calls to register groups and fields instead of hooking into a filter.

| Registration Type  | Supports JSON Files | Supports Multiple Content Types   | Form / HTML markup required   |
|--------------------|---------------------|-----------------------------------|-------------------------------|
| PHP function calls | Yes                 | Yes, with conditional logic rules | No, the API does this for you |
