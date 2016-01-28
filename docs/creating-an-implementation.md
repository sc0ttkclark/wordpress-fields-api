# Fields API Implementations

## What is an Implementation?

Implementations are screens that are encapsulated with configuration. An implementation often contains a combination of registering sections, controls, and fields -- in addition to calling the `$scree->render()` and `$screen->save_fields()` methods.

## Creating an Implementation class

```php
class WP_Fields_API_Screen_My_XYZ extends WP_Fields_API_Screen {

	/**
	 * {@inheritdoc}
	 */
	public function register_fields( $wp_fields ) {
	
		////////////////
		// My Section //
		////////////////

		// May be useful to reference $this->id (Screen ID) if using across multiple object names
		$section_id = $this->id . '-my-section';
		
		$wp_fields->add_section( $this->object_type, $section_id, $this->object_name, array(
			'title'  => __( 'My Section', 'my-text-domain' ),
			'screen' => $this->id,
		) );

		$field_id = 'my-field';
		$field_args = array(
			'control' => array(
				'type'        => 'text',
				'section'     => $section_id,
				'label'       => __( 'My Field', 'my-text-domain' ),
				'description' => __( 'This is a description for My Field.', 'my-text-domain' ),
			),
		);

		$wp_fields->add_field( $this->object_type, $field_id, $this->object_name, $field_args );

	}

	/**
	 * {@inheritdoc}
	 */
	public function save_fields( $item_id = null, $object_name = null ) {

		// Do any custom saving you want to do here, or just run parent::save_fields()
		
		// You can return a WP_Error with your error(s) too
		
		// Run default save
		return parent::save_fields( $item_id, $object_name );

	}
	
	// You may also look at WP_Fields_API_Screen::render_section() to customize markup used
	// or WP_Fields_API_Screen::render_control() to customize markup used

}
```

## Rendering controls for your screen

```php
/**
 * @var $wp_fields WP_Fields_API
 */
global $wp_fields;

// Object type and Screen ID
$object_type = 'my-xyz';
$screen_id = 'my-screen';
	
// Set this to a specific post type, taxonomy,
// or comment type you want to register for
$object_name = null;

// Get the screen object
$screen = $wp_fields->get_screen( $object_type, $screen_id, $object_name );

// This is the current item ID, like a Post ID, Term ID
// Should be empty when adding new items
$item_id = 0;

// Render screen controls
$screen->render( $item_id, $object_name );
```

## Saving data for your screen

```php
/**
 * @var $wp_fields WP_Fields_API
 */
global $wp_fields;

// Object type and Screen ID
$object_type = 'my-xyz';
$screen_id = 'my-screen';
	
// Set this to a specific post type, taxonomy,
// or comment type you want to register for
$object_name = null;

// Get the screen object
$screen = $wp_fields->get_screen( $object_type, $screen_id, $object_name );

// This is the current item ID, like a Post ID, Term ID
// Should be empty when adding new items
$item_id = 0;

// Save screen fields
$screen->save_fields( $item_id, $object_name );
```

## Including your Implementation

If you are adding an implementation for a WordPress Admin screen, you'll want to add it to the `wordpress-fields-api.php` file inside the `_wp_fields_api_implementations()` function. Follow the same format as the ones already there.
 
Otherwise, you can add your implementation through the normal `fields_register` hook:
 
```php
/**
 * Include My Implementations
 */
function my_xyz_include_implementation() {

	$implementation_dir = 'path/to/your/files/';

	// Include your Implementation class
	require_once( $implementation_dir . 'class-wp-fields-api-screen-my-xyz.php' );

	// Object type and Screen ID
	$object_type = 'my-xyz';
	$screen_id = 'my-screen';
	
	// Set this to a specific post type, taxonomy,
	// or comment type you want to register for
	$object_name = null;
	
	// Run the registration of the encapsulated configuration
	WP_Fields_API_Screen_My_XYZ::register( $object_type, $screen_id, $object_name );

}
add_action( 'fields_register', 'my_xyz_include_implementation' );
```
