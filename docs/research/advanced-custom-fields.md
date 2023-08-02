# Advanced Custom Fields

## Registering Forms

> [acf\_register\_form()](https://www.advancedcustomfields.com/resources/acf_register_form/)

```php
add_action( 'acf/init', 'my_acf_form_init' );
function my_acf_form_init() {
	// Check function exists.
	if ( function_exists( 'acf_register_form' ) ) {
		// Register form.
		acf_register_form( array(
			'id'           => 'new-event',
			'post_id'      => 'new_post',
			'new_post'     => array(
				'post_type'   => 'event',
				'post_status' => 'publish'
			),
			'post_title'   => true,
			'post_content' => true,
		) );
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
	acf_add_local_field_group( array(
		'key'      => 'group_1',
		'title'    => 'My Group',
		'fields'   => array(),
		'location' => array(
			array(
				array(
					'param'    => 'post_type',
					'operator' => '==',
					'value'    => 'post',
				),
			),
		),
	) );
}
```

## Registering Fields

> [Register fields via PHP](https://www.advancedcustomfields.com/resources/register-fields-via-php/)

```php
add_action( 'acf/init', 'my_acf_add_local_fields' );
function my_acf_add_local_fields() {
	acf_add_local_field( array(
		'key'    => 'field_1',
		'label'  => 'Sub Title',
		'name'   => 'sub_title',
		'type'   => 'text',
		'parent' => 'group_1'
	) );
}
```
