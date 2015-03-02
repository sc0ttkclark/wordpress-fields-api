# Meta Box

## Registering Forms

Meta Box is primarily based around registering fields into existing forms. It can be used to create post types or
register custom fields for post types. It can also be used to create custom forms based on database models, registered
via [custom models](https://docs.metabox.io/extensions/mb-custom-table/#custom-models).

[https://docs.metabox.io/extensions/mb-custom-table/#custom-models](https://docs.metabox.io/extensions/mb-custom-table/#custom-models)

```php
add_action( 'init', function () {
	mb_register_model(
		'transaction',
		[
			'table'     => 'transactions',
			'labels'    => [
				'name'          => 'Transactions',
				'singular_name' => 'Transaction',
			],
			'menu_icon' => 'dashicons-money-alt',
		]
	);
} );
```

The above registers a new menu item with the label 'Transactions' that will send data to a table called' transactions.

```php
add_action( 'init', function () {
	MetaBox\CustomTable\API::create(
		'transactions',                    // Table name.
		[                                  // Table columns (without ID).
			'created_at' => 'DATETIME',
			'amount'     => 'BIGINT',
			'email'      => 'VARCHAR(20)',
			'gateway'    => 'TEXT',
			'status'     => 'VARCHAR(20)',
			'screenshot' => 'TEXT',
		],
		[                                  // List of index keys.
			'email',
			'status',
		],
		true                               // Must be true for models.
	);
} );
```

The above registers a custom table for storing data in the registered model.

```php
// Step 3: Register fields for model, corresponding to the custom table structure.
add_filter( 'rwmb_meta_boxes', 'prefix_register_transaction_fields' );
function prefix_register_transaction_fields( $meta_boxes ) {
	$meta_boxes[] = [
		'title'        => 'Transaction Details',
		'models'       => [ 'transaction' ], // Model name
		'storage_type' => 'custom_table',    // Must be 'custom_table'
		'table'        => 'transactions',    // Custom table name
		'fields'       => [
			[
				'id'            => 'created_at',
				'type'          => 'datetime',
				'name'          => 'Created at',
				'js_options'    => [
					'timeFormat' => 'HH:mm:ss',
				],
				'admin_columns' => true,
			],
			[
				'id'            => 'amount',
				'type'          => 'number',
				'name'          => 'Amount',
				'append'        => 'USD',
				'admin_columns' => [
					'position' => 'after id',
					'sort'     => true,
				],
			],
			[
				'id'            => 'gateway',
				'name'          => 'Gateway',
				'admin_columns' => true,
			],
			[
				'id'            => 'status',
				'type'          => 'select',
				'name'          => 'Status',
				'options'       => [
					'pending'   => 'Pending',
					'completed' => 'Completed',
					'refunded'  => 'Refunded',
				],
				'admin_columns' => true,
			],
		],
	];

	$meta_boxes[] = [
		'title'        => 'Additional Transaction Details',
		'models'       => [ 'transaction' ], // Model name
		'storage_type' => 'custom_table',    // Must be 'custom_table'
		'table'        => 'transactions',    // Custom table name
		'fields'       => [
			[
				'id'            => 'email',
				'type'          => 'email',
				'name'          => 'Email',
				'admin_columns' => [
					'position'   => 'after amount',
					'searchable' => true,
				],
			],
			[
				'id'            => 'screenshot',
				'type'          => 'image_advanced',
				'name'          => 'Screenshots',
				'admin_columns' => true,
			],
		],
	];

	return $meta_boxes;
}
```

The above generates the custom form. Outside of custom database models, Meta Box is mostly based on extending existing
forms.

## Registering Sections

[https://docs.metabox.io/fields/fieldset-text/](https://docs.metabox.io/fields/fieldset-text/)

[https://docs.metabox.io/creating-fields-with-code/#registering-custom-fields-with-php](https://docs.metabox.io/creating-fields-with-code/#registering-custom-fields-with-php)

### Create a collection of ordered fields.

```php
add_filter( 'rwmb_meta_boxes', function ( $meta_boxes ) {
	$meta_boxes[] = [
		'title'      => 'Event details',
		'post_types' => 'event',
		'fields'     => [
			[
				'name' => 'Date and time',
				'id'   => 'datetime',
				'type' => 'datetime',
			],
			[
				'name' => 'Location',
				'id'   => 'location',
				'type' => 'text',
			],
			[
				'name'          => 'Map',
				'id'            => 'map',
				'type'          => 'osm',
				'address_field' => 'location',
			],
		],
	];

	// Add more field groups if you want
	// $meta_boxes[] = ...

	return $meta_boxes;
} );
```

### Declare a group of fields

Creates a group of fields within a fieldset. Only supports simple text fields.

```php
...
[
	'id'      => 'field_id',
	'name'    => 'Fieldset Text',
	'type'    => 'fieldset_text',
	'options' => [
		'name'    => 'Name',
		'address' => 'Address',
		'email'   => 'Email',
	],
],
...
```

## Registering Fields

See above. Fields are declared within arrays, and can't be assigned to a form outside of registering the entire form
group.

**_There is no code for this_** as you cannot currently register fields to a section/form separately.