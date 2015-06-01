# WordPress Fields API

[![Travis](https://secure.travis-ci.org/sc0ttkclark/wordpress-fields-api.png?branch=master)](http://travis-ci.org/sc0ttkclark/wordpress-fields-api)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/sc0ttkclark/wordpress-fields-api/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/sc0ttkclark/wordpress-fields-api/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/sc0ttkclark/wordpress-fields-api/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/sc0ttkclark/wordpress-fields-api/?branch=master)
[![License](https://img.shields.io/badge/license-GPL--2.0%2B-green.svg)](https://github.com/sc0ttkclark/wordpress-fields-api/blob/master/LICENSE.txt)

This is a core proposal for a new wide-reaching API for WordPress core. It is not a guarantee of final functionality, but it's an evolving prototype that can be installed as a plugin for easy testing and usage throughout development.

This was initially a project of the [WordPress core Options/Metadata team](http://make.wordpress.org/core/components/options-meta/) but is currently led by Scott Kingsley Clark with oversight by WordPress core lead developer Helen Hou-Sandí.

Please note: This plugin is not in running condition, it is still in the early stages of development.

## Why a Fields API?

There are over a hundred (I stopped counting, sue me) plugins in the plugin repository that add meta boxes and fields to post types, settings, users, and even more if you include all of the themes and plugins that hook into the customizer. Many plugins build their own abstraction level for doing this, and custom field plugins are the biggest culprit of not following any standards for which to there is a significant need to unite these APIs to make them more consistent. At the same time, being able to provide a detailed structure for a site will take the capabilities of apps that extend WordPress (or interact with it) to the next level.

Each of the APIs that this aims to unite all have the same essential needs. Based on the Customizer, we can enable developers to do more because they won't have to jump between different interfaces.

## What about Fields UI?

I am not focusing on any UI aspects at the moment besides implementation of the API underneath getting the field data for UI to use in core itself. It will be easier to tackle the API and the UI separately for both the purpose of development and core inclusion.

## Progress so far

* Fields API has been abstracted from the Customizer classes
* All fields, controls, sections, and screens can now utilize "late init". This is huge because it uses less memory on every page load and once this goes into WP core and is utilized by themes, plugins, and core itself -- there is potential for a large amount of things registered through this API, if used to it's full potential.

## Unknowns / To dos

There are still a lot of areas the API is not represented in code or in examples.

* Customizer Manager (need to look at `add_dynamic_settings`, not sure if it needs to be abstracted into `$wp_fields`)
* `$wp_fields->add_field( ... )` needs more thought on how to universally handle global objects versus named objects (Customizer / Settings are global, where as Post Types and Taxonomies are named), need to think about the method parameters and how we want to approach it
* Field types need fleshing out, you shouldn't have to init a class, late-init at the very least should be utilized where possible
* register_meta and how this interacts behind the scenes with it
* get/add/update/delete for Meta / Settings API interaction has not yet been determined
* Examples of how this API replaces existing core handling for the Post Editor or User Profile -- ex. Taxonomy / Page Attributes meta boxes etc

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

	// 2. Register new fields
	$wp_fields->add_field( 'customizer', 'link_textcolor',
		array(
			// Default setting/value to save
			'default'    => '#2BA6CB',

			// Is this an 'option' or a 'theme_mod'?
			'type'       => 'theme_mod',

			// Optional. Special permissions for accessing this field.
			'capability' => 'edit_theme_options',

			// What triggers a refresh of the field? 'refresh' or 'postMessage' (instant)?
			'transport'  => 'postMessage',
			
			// Optional. Add an associated control (otherwise it won't show up in the UI)
			'control'    => array(
				// Set a unique ID for the control
				'id'       => 'mytheme_link_textcolor',
				
				// Admin-visible name of the control
				'label'    => __( 'Link Color', 'mytheme' ),

				// ID of the section this control should render in (can be one of yours, or a WordPress default section)
				'section'  => 'mytheme_options',

				// Determines the order this control appears in for the specified section
				'priority' => 10
				
				// Control type
				'type'     => 'color'
			)
		)
	);

	// 3. We can also change built-in fields by modifying properties.
	// 		For instance, let's make some stuff use live preview JS...
	$wp_fields->get_field( 'customizer', 'blogname' )->transport         = 'postMessage';
	$wp_fields->get_field( 'customizer', 'blogdescription' )->transport  = 'postMessage';
	$wp_fields->get_field( 'customizer', 'header_textcolor' )->transport = 'postMessage';
	$wp_fields->get_field( 'customizer', 'background_color' )->transport = 'postMessage';
   
}
add_action( 'fields_register', 'fields_api_example_customizer_register' );
```

### Register fields to a User profile

```php
function fields_api_example_user_field_register()  {

	// This is a *new* API

	// 1. Define a new section (if desired) for User area
	$wp_fields->add_section( 'user', 'mytheme_user_social_fields',
		array(
			// Visible title of section
			'title' => __( 'Social Fields', 'mytheme' )
		)
	);

	// 2. Register new fields
	$wp_fields->add_field( 'user', 'twitter',
		array(
			// Optional. Add an associated control (otherwise it won't show up in the UI)
			'control' => array(
				// Set a unique ID for the control
				'id'      => 'mytheme_user_twitter',
				
				// Admin-visible name of the control
				'label'   => __( 'Twitter Username', 'mytheme' ),

				// ID of the section this control should render in (can be one of yours, or a WordPress default section)
				'section' => 'mytheme_user_social_fields',
				
				// Control type
				'type'    => 'text'
			)
		)
	);
	
	$wp_fields->add_field( 'user', 'google_plus',
		array(
			// Optional. Add an associated control (otherwise it won't show up in the UI)
			'control' => array(
				// Set a unique ID for the control
				'id'      => 'mytheme_user_google_plus',
				
				// Admin-visible name of the control
				'label'   => __( 'Google+ Profile URL', 'mytheme' ),

				// ID of the section this control should render in (can be one of yours, or a WordPress default section)
				'section' => 'mytheme_user_social_fields',
				
				// Control type
				'type'    => 'text'
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

	// 1. Define a new screen for the Settings
	$wp_fields->add_screen( 'settings', 'mytheme_settings_sharing',
		array(
			// Visible title of section
			'title'      => __( 'Sharing', 'mytheme' ),

			// Alternative page title
			'page_title' => __( 'MyTheme Sharing.', 'mytheme' )

			// Capability needed to tweak
			'capability' => 'manage_options',
		)
	);
	
	// 2. Define a new section for the Settings
	$wp_fields->add_section( 'settings', 'mytheme_setting_sharing',
		array(
			// Visible title of section
			'title'      => __( 'Sharing', 'mytheme' ),

			// Capability needed to tweak
			'capability' => 'manage_options'
		)
	);

	// 3. Register new fields
	$wp_fields->add_field( 'settings', 'mytheme_sharing_buttons',
		array(
			// Default setting/value to save
			'default'    => '1',

			// Optional. Special permissions for accessing this setting.
			'capability' => 'manage_options',
			
			// Optional. Add an associated control (otherwise it won't show up in the UI)
			'control'    => array(
				// Don't set 'id' and it will automatically be generated for you as
				// 'id' => 'fields_settings_mytheme_sharing_buttons',
				
				// Admin-visible name of the control
				'label'       => __( 'Show Sharing Buttons?', 'mytheme' ),
				
				// Admin-visible name of the control
				'description' => __( 'This will show sharing buttons below blog posts on singular templates', 'mytheme' ),

				// ID of the section this control should render in (can be one of yours, or a WordPress default section)
				'section'     => 'mytheme_setting_sharing',
				
				// Control type
				'type'        => 'checkbox'
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

	// 1. Define a new section (meta box) for fields to appear in
	$wp_fields->add_section( 'post_type', 'my_cpt', 'my_meta_box',
		array(
			// Visible title of section
			'title'       => __( 'My Meta Box', 'mytheme' ),

			// Determines what order this appears in
			'priority'    => 'high',

			// Determines what order this appears in
			'context'     => 'side',

			// Capability needed to tweak
			'capability'  => 'my_custom_capability'
		)
	);

	// 2. Register new fields
	$wp_fields->add_field( 'post_type', 'my_cpt', 'my_custom_field',
		array(
			// Default field/value to save
			'default'    => 'All about that post',

			// Optional. Special permissions for accessing this field.
			'capability' => 'my_custom_capability',
			
			// Optional. Add an associated control (otherwise it won't show up in the UI)
			'control' => array(
				// Don't set 'id' and it will automatically be generated for you as
				// 'id' => 'fields_post_type_my_cpt_my_custom_field',
				
				// Admin-visible name of the control
				'label'   => __( 'My Custom Field', 'mytheme' ),

				// ID of the section this control should render in (can be one of yours, or a WordPress default section)
				'section' => 'my_meta_box',
				
				// Control type
				'type'    => 'text'
			)
		)
	);
   
}
add_action( 'fields_register', 'fields_api_example_post_field_register' );
```

## Contributing

If you are interested in contributing, feel free to contact us in #core-fields on [WordPress Slack](https://make.wordpress.org/chat/) and we'll help you get into the mix. There are also [GitHub issues](https://github.com/sc0ttkclark/wordpress-fields-api/issues) you can feel free to chime in on, or provide Pull Requests.

### Pull Requests

To submit a pull request, please base it off of the `master` branch.

## LICENSE

GPLv2 or later. See [License](LICENSE.txt).
