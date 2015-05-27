WordPress Fields API
=======================

This is a core proposal for a new wide-reaching API for WordPress core. It is not a guarantee of final functionality, but it's an evolving prototype that can be installed as a plugin for easy testing and usage throughout development.

This was initially a project of the [WordPress core Options/Metadata team](http://make.wordpress.org/core/components/options-meta/) but is currently led by Scott Kingsley Clark with oversight by WordPress core lead developer Helen Hou-Sandí.

Please note: This plugin is not in running condition, it is still in the early stages of development.

## Why a Fields API?

There are over a hundred (I stopped counting, sue me) plugins in the plugin repository that add meta boxes and fields to post types, settings, users, and even more if you include all of the themes and plugins that hook into the customizer. Many plugins build their own abstraction level for doing this, and custom field plugins are the biggest culprit of not following any standards for which to there is a significant need to unite these APIs to make them more consistent. At the same time, being able to provide a detailed structure for a site will take the capabilities of apps that extend WordPress (or interact with it) to the next level.

Each of the APIs that this aims to unite all have the same essential needs. Based on the Customizer, we can enable developers to do more because they won't have to jump between 

## What about Fields UI?

I am not focusing on any UI aspects at the moment besides implementation of the API underneath getting the field data for UI to use in core itself. It will be easier to tackle the API and the UI separately for both the purpose of development and core inclusion.

## Progress so far

* Fields API has been abstracted from the Customizer classes
* All settings, panels, sections, and controls can now utilize late init. This is huge because it uses less memory on every page load and once this goes into WP core and is utilized by themes, plugins, and core itself -- there is potential for a large amount of things registered through this API, if used to it's full potential.

## Unknowns / To dos

There are still a lot of areas the API is not represented in code or in examples.

* Customizer Manager (need to look at `add_dynamic_settings`, not sure if it needs to be abstracted into `$wp_fields`)
* Field types need fleshing out, you shouldn't have to init a class, late-init at the very least should be utilized where possible
* register_meta and how this interacts behind the scenes with it
* get/add/update/delete for Meta / Settings API interaction has not yet been determined
* Examples below aren't fleshed out with use cases, only the initial [Customizer Example code](http://codex.wordpress.org/Theme_Customization_API) I pulled from the codex, the example panels / sections / settings / controls need to be fleshed out
* Examples of how this API replaces existing core handling for the Post Editor or User area -- ex. Taxonomy / Page Attributes meta boxes etc

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
	$wp_fields->add_field( 'customizer', 'link_textcolor',
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
				// Admin-visible name of the control
				'label'    => __( 'Link Color', 'mytheme' ),

				// ID of the section this control should render in (can be one of yours, or a WordPress default section)
				'section'  => 'mytheme_options',

				// Which setting to load and manipulate (serialized is okay)
				'settings' => 'link_textcolor',

				// Determines the order this control appears in for the specified section
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

### Register fields to a User profile

```php
function fields_api_example_user_field_register()  {

	// This is a *new* API

	// 1. Define a new section (if desired) for User area
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
	$wp_fields->add_field( 'user', 'link_textcolor',
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
				// Admin-visible name of the control
				'label'    => __( 'Link Color', 'mytheme' ),

				// ID of the section this control should render in (can be one of yours, or a WordPress default section)
				'section'  => 'mytheme_options',

				// Which setting to load and manipulate (serialized is okay)
				'settings' => 'link_textcolor',

				// Determines the order this control appears in for the specified section
				'priority' => 10
			)
		)
	);

}
add_action( 'fields_register', 'fields_api_example_user_field_register' );
```

### Settings API

```php
function fields_api_example_settings_register( $wp_fields ) {

	// This is a *new* API

	// 1. Define a new section for the Settings API
	$wp_fields->add_section( 'settings', 'mytheme_options',
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
	$wp_fields->add_field( 'settings', 'link_textcolor',
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
	$wp_fields->add_control( 'settings',
		// Instantiate the color control class
		new WP_Fields_API_Color_Control(
			// Set a unique ID for the control
			'mytheme_link_textcolor',
		
			array(
				// Admin-visible name of the control
				'label'    => __( 'Link Color', 'mytheme' ),

				// ID of the section this control should render in (can be one of yours, or a WordPress default section)
				'section'  => 'mytheme_options',

				// Which setting to load and manipulate (serialized is okay)
				'settings' => 'link_textcolor',

				// Determines the order this control appears in for the specified section
				'priority' => 10
			)
		)
	);
   
}
add_action( 'fields_register', 'fields_api_example_settings_register' );
```

### Register fields to a Post Type

```php
function fields_api_example_post_field_register( $wp_fields ) {

	// This is a *new* API

	// 0. Define a new panel (meta box) for fields to appear in
	$wp_fields->add_section( 'post_type', 'my_cpt', 'my_meta_box',
		array(
			// Visible title of section
			'title'       => __( 'My Meta Box', 'mytheme' ),

			// Determines what order this appears in
			'priority'    => 'high',

			// Determines what order this appears in
			'context'    => 'side',

			// Capability needed to tweak
			'capability'  => 'my_custom_capability',

			// Descriptive tooltip
			'description' => __( 'Allows you to customize some example settings for My CPT.', 'mytheme' )
		)
	);

	// 1. Register new fields
	$wp_fields->add_field( 'post_type', 'my_cpt', 'my_custom_field',
		array(
			// Default setting/value to save
			'default'    => 'All about that field',

			// Optional. Special permissions for accessing this setting.
			'capability' => 'my_custom_capability',
			
			// Optional. Add an associated control (otherwise it won't show up in the UI)
			'control' => array(
				// Admin-visible name of the control
				'label'    => __( 'My Custom Field', 'mytheme' ),

				// ID of the section this control should render in (can be one of yours, or a WordPress default section)
				'section'  => 'my_meta_box',
				
				// Control type
				'type'     => 'text'
			)
		)
	);
   
}
add_action( 'fields_register', 'fields_api_example_post_field_register' );
```

## Contributing

If you are interested in contributing, feel free to contact me @sc0ttkclark on [WordPress Slack](https://make.wordpress.org/chat/) and I'll help you get into the mix. There are also [GitHub issues](https://github.com/sc0ttkclark/wordpress-fields-api/issues) you can feel free to chime in on, or provide Pull Requests.

### Pull Requests

To submit a pull request, please base it off of the `master` branch.

## LICENSE

GPLv2 or later. See [License](LICENSE.txt).
