WordPress Fields API
=======================

This is a core proposal for a new wide-reaching API for WordPress core. It is not a guarantee of final functionality, but it's an evolving prototype that can be installed as a plugin for easy testing and usage throughout development.

This was initially a project of the [WordPress core Options/Metadata team](http://make.wordpress.org/core/components/options-meta/) but is currently led by Scott Kingsley Clark with oversight by WordPress core lead developer Helen Hou-Sandi.

## Examples - Fields, Sections, Controls

### Customizer

Example code taken from [http://codex.wordpress.org/Theme_Customization_API](http://codex.wordpress.org/Theme_Customization_API)

```php
function fields_api_example_customizer_register( $wp_fields ) {

	// This is functionally equivalent to hooking into customize_register and using $wp_customize

	// 1. Define a new section (if desired) to the Theme Customizer
	$wp_fields->add_section( 'customizer', 'mytheme_options',
		array(
			// Visible title of section
			'title'       => __( 'MyTheme Options', 'mytheme' ),

			// Determines what order this appears in
			'priority'    => 35,

			// Capability needed to tweak
			'capability'  => 'edit_theme_options',

			// Descriptive tooltip
			'description' => __( 'Allows you to customize some example settings for MyTheme.', 'mytheme' )
		)
	);

	// 2. Register new settings to the WP database...
	$wp_fields->add_setting( 'customizer', 'link_textcolor',
		array(
			// Default setting/value to save
			'default'    => '#2BA6CB',

			// Is this an 'option' or a 'theme_mod'?
			'type'       => 'theme_mod',

			// Optional. Special permissions for accessing this setting.
			'capability' => 'edit_theme_options',

			// What triggers a refresh of the setting? 'refresh' or 'postMessage' (instant)?
			'transport'  => 'postMessage'
		)
	);

	// 3. Finally, we define the control itself (which links a setting to a section and renders the HTML controls)...
	$wp_fields->add_control( 'customizer',
		// Instantiate the color control class
		new WP_Fields_API_Color_Control(
			// Set a unique ID for the control
			'mytheme_link_textcolor',
		
			array(
				//Admin-visible name of the control
				'label'    => __( 'Link Color', 'mytheme' ),

				//ID of the section this control should render in (can be one of yours, or a WordPress default section)
				'section'  => 'colors',

				//Which setting to load and manipulate (serialized is okay)
				'settings' => 'link_textcolor',

				//Determines the order this control appears in for the specified section
				'priority' => 10
			)
		)
	);

	// 4. We can also change built-in settings by modifying properties. For instance, let's make some stuff use live preview JS...
	$wp_fields->get_setting( 'customizer', 'blogname' )->transport         = 'postMessage';
	$wp_fields->get_setting( 'customizer', 'blogdescription' )->transport  = 'postMessage';
	$wp_fields->get_setting( 'customizer', 'header_textcolor' )->transport = 'postMessage';
	$wp_fields->get_setting( 'customizer', 'background_color' )->transport = 'postMessage';
   
}
add_action( 'fields_register', 'fields_api_example_customizer_register' );
```

### Register fields to a Post Type

```php
function fields_api_example_post_field_register( $wp_fields ) {

	// This is a *new* API

	// 1. Define a new section (if desired) to the Theme Customizer
	$wp_fields->add_section( 'post_type', 'my_cpt', 'mytheme_options',
		array(
			// Visible title of section
			'title'       => __( 'MyTheme Options', 'mytheme' ),

			// Determines what order this appears in
			'priority'    => 35,

			// Capability needed to tweak
			'capability'  => 'edit_theme_options',

			// Descriptive tooltip
			'description' => __( 'Allows you to customize some example settings for MyTheme.', 'mytheme' )
		)
	);

	// 2. Register new settings to the WP database...
	$wp_fields->add_setting( 'post_type', 'my_cpt', 'link_textcolor',
		array(
			// Default setting/value to save
			'default'    => '#2BA6CB',

			// Is this an 'option' or a 'theme_mod'?
			'type'       => 'theme_mod',

			// Optional. Special permissions for accessing this setting.
			'capability' => 'edit_theme_options',

			// What triggers a refresh of the setting? 'refresh' or 'postMessage' (instant)?
			'transport'  => 'postMessage'
		)
	);

	// 3. Finally, we define the control itself (which links a setting to a section and renders the HTML controls)...
	$wp_fields->add_control( 'post_type', 'my_cpt',
		// Instantiate the color control class
		new WP_Fields_API_Color_Control(
			// Set a unique ID for the control
			'mytheme_link_textcolor',
		
			array(
				//Admin-visible name of the control
				'label'    => __( 'Link Color', 'mytheme' ),

				//ID of the section this control should render in (can be one of yours, or a WordPress default section)
				'section'  => 'colors',

				//Which setting to load and manipulate (serialized is okay)
				'settings' => 'link_textcolor',

				//Determines the order this control appears in for the specified section
				'priority' => 10
			)
		)
	);

	// 4. We can also change built-in settings by modifying properties. For instance, let's make some stuff use live preview JS...
	$wp_fields->get_setting( 'post_type', 'my_cpt', 'blogname' )->transport         = 'postMessage';
	$wp_fields->get_setting( 'post_type', 'my_cpt', 'blogdescription' )->transport  = 'postMessage';
	$wp_fields->get_setting( 'post_type', 'my_cpt', 'header_textcolor' )->transport = 'postMessage';
	$wp_fields->get_setting( 'post_type', 'my_cpt', 'background_color' )->transport = 'postMessage';
   
}
add_action( 'fields_register', 'fields_api_example_post_field_register' );
```

### Register fields to a User profile

```php
function fields_api_example_user_field_register()  {

	// This is a *new* API

	// 1. Define a new section (if desired) to the Theme Customizer
	$wp_fields->add_section( 'user', 'mytheme_options',
		array(
			// Visible title of section
			'title'       => __( 'MyTheme Options', 'mytheme' ),

			// Determines what order this appears in
			'priority'    => 35,

			// Capability needed to tweak
			'capability'  => 'edit_theme_options',

			// Descriptive tooltip
			'description' => __( 'Allows you to customize some example settings for MyTheme.', 'mytheme' )
		)
	);

	// 2. Register new settings to the WP database...
	$wp_fields->add_setting( 'user', 'link_textcolor',
		array(
			// Default setting/value to save
			'default'    => '#2BA6CB',

			// Is this an 'option' or a 'theme_mod'?
			'type'       => 'theme_mod',

			// Optional. Special permissions for accessing this setting.
			'capability' => 'edit_theme_options',

			// What triggers a refresh of the setting? 'refresh' or 'postMessage' (instant)?
			'transport'  => 'postMessage'
		)
	);

	// 3. Finally, we define the control itself (which links a setting to a section and renders the HTML controls)...
	$wp_fields->add_control( 'user',
		// Instantiate the color control class
		new WP_Fields_API_Color_Control(
			// Set a unique ID for the control
			'mytheme_link_textcolor',
		
			array(
				//Admin-visible name of the control
				'label'    => __( 'Link Color', 'mytheme' ),

				//ID of the section this control should render in (can be one of yours, or a WordPress default section)
				'section'  => 'colors',

				//Which setting to load and manipulate (serialized is okay)
				'settings' => 'link_textcolor',

				//Determines the order this control appears in for the specified section
				'priority' => 10
			)
		)
	);

	// 4. We can also change built-in settings by modifying properties. For instance, let's make some stuff use live preview JS...
	$wp_fields->get_setting( 'user', 'blogname' )->transport         = 'postMessage';
	$wp_fields->get_setting( 'user', 'blogdescription' )->transport  = 'postMessage';
	$wp_fields->get_setting( 'user', 'header_textcolor' )->transport = 'postMessage';
	$wp_fields->get_setting( 'user', 'background_color' )->transport = 'postMessage';

}
add_action( 'init', 'fields_api_example_user_field_register' );
```

## Contributing

If you are interested in contributing, feel free to contact me @sc0ttkclark on [WordPress Slack](https://make.wordpress.org/chat/) and I'll help you get into the mix. There are also [GitHub issues](https://github.com/sc0ttkclark/wordpress-fields-api/issues) you can feel free to chime in on, or provide Pull Requests.

### Pull Requests

To submit a pull request, please base it off of the `master` branch.is your oyster and we're eager to help you get those pearls!

## LICENSE

GPLv2 or later. See [License](LICENSE.txt).