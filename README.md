![WordPress Fields API](https://raw.githubusercontent.com/sc0ttkclark/wordpress-fields-api/assets/banner-github.png)

# WordPress Fields API v0.0.6 Alpha

[![Travis](https://secure.travis-ci.org/sc0ttkclark/wordpress-fields-api.png?branch=develop)](http://travis-ci.org/sc0ttkclark/wordpress-fields-api)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/sc0ttkclark/wordpress-fields-api/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/sc0ttkclark/wordpress-fields-api/?branch=develop)
[![codecov.io](http://codecov.io/github/sc0ttkclark/wordpress-fields-api/coverage.svg?branch=master)](http://codecov.io/github/sc0ttkclark/wordpress-fields-api?branch=develop)
[![License](https://img.shields.io/badge/license-GPL--2.0%2B-green.svg)](https://github.com/sc0ttkclark/wordpress-fields-api/blob/develop/LICENSE.txt)

This is a core proposal for a new wide-reaching API for WordPress core. It is currently an evolving prototype that can be installed as a plugin or included as a library for easy testing and usage throughout development.

This was initially a project of the [WordPress core Options/Metadata team](http://make.wordpress.org/core/components/options-meta/) but is currently led by Scott Kingsley Clark and Eric Andrew Lewis with oversight by WordPress core lead developer Helen Hou-Sandí.

Please note: This [Feature Project](https://make.wordpress.org/core/features/) is still in the early stages of development and should not be used on production sites. It should be assumed that until the v1.0 release, the Fields API could change significantly due to core scrutiny and the final merge proposal response.

* [Latest Progress Update](https://github.com/sc0ttkclark/wordpress-fields-api/blob/develop/docs/progress.md)
* [Posts about the WP Fields API on the Official WordPress.org Development blog](https://make.wordpress.org/core/tag/fields-api/)
* [Architecture Documentation](https://docs.google.com/document/d/17yUTO_vlkC7P4_2c6dIDxa5jQbXvfV9SofC7_GOwFME/edit)

## Documentation

* [Terminology](https://github.com/sc0ttkclark/wordpress-fields-api/blob/develop/docs/terminology.md)
* [Object Types and Object Subtypes](https://github.com/sc0ttkclark/wordpress-fields-api/blob/develop/docs/object-types-and-subtypes.md)
* [Registering Fields (in depth)](https://github.com/sc0ttkclark/wordpress-fields-api/blob/develop/docs/registering-fields.md)
* [Creating an Implementation (advanced)](https://github.com/sc0ttkclark/wordpress-fields-api/blob/develop/docs/creating-an-implementation.md)

### Visual Structure for Forms, Sections, and Controls

You can add new sections and controls to different sections of WordPress, but we're also in the process of replacing entire admin screens in WordPress with Fields API powered forms too.

![Structure Example](https://raw.githubusercontent.com/sc0ttkclark/wordpress-fields-api/develop/docs/terminology.png)

### Example Code

**User Profile Form:**

* [Starter example](https://github.com/sc0ttkclark/wordpress-fields-api/blob/develop/docs/examples/user/_starter.php)
* [Address section and fields example](https://github.com/sc0ttkclark/wordpress-fields-api/blob/develop/docs/examples/user/address.php)

**Term Add / Edit Form:**

* [Starter example](https://github.com/sc0ttkclark/wordpress-fields-api/blob/develop/docs/examples/term/_starter.php)

## Requirements

* WordPress 4.4 - No previous or more recent major version can be 100% supported and confirmed as working.
* Fields API installed as a plugin or included as a library

Every Fields API release has to be based off of the latest WordPress stable release. This means that over time, we can only support the last stable release of WordPress.

At the end of each WP release cycle we have to merge all of the Customizer and other implementations we have with the latest changes from core.

## Why a Fields API?

There are over a hundred (I had to stop counting) plugins in the plugin repository that add meta boxes and fields to post types, settings, users, and even more if you include all of the themes and plugins that hook into the customizer. Many plugins build their own abstraction level for doing this, and custom field plugins are the biggest culprit of not following any standards for which to there is a significant need to unite these APIs to make them more consistent. At the same time, being able to provide a detailed structure for a site will take the capabilities of apps that extend WordPress (or interact with it) to the next level.

Each of the APIs that this aims to unite all have the same essential needs. Based on the Customizer, we can enable developers to do more because they won't have to jump between different interfaces.

## What about Fields UI?

I am not focusing on any UI aspects at the moment besides implementation of the API underneath getting the field data for UI to use in core itself. It will be easier to tackle the API and the UI separately for both the purpose of development and core inclusion.

## Where we need help

There are still a lot of areas the API is not represented in code or in examples.

[Check out a full list of things we currently need help with](https://github.com/sc0ttkclark/wordpress-fields-api/labels/help%20wanted)

* Implementations are [in development amongst a few contributors](https://github.com/sc0ttkclark/wordpress-fields-api/labels/implementation)
* We need [use-cases and examples](https://github.com/sc0ttkclark/wordpress-fields-api/issues/22), to be fleshed out in `/examples/{implementation}/{example}.md` inside this repo
* [Core Proposal for Merge into WP 4.6](https://github.com/sc0ttkclark/wordpress-fields-api/issues/35) needs to be written up

## Contributing

If you are interested in contributing, feel free to contact us in #core-fields on [WordPress Slack](https://make.wordpress.org/chat/) and we'll help you get into the mix.

There are also [GitHub issues](https://github.com/sc0ttkclark/wordpress-fields-api/issues) you can feel free to chime in on or provide Pull Requests to.

### Pull Requests

To submit a pull request, please base it off of the `develop` branch which we use for ongoing development towards the next release. The `master` branch represents the last stable beta release.

### Testing

There are a few things that can be enabled for testing purposes:

* `define( 'WP_FIELDS_API_EXAMPLES', true );` Enable example section, controls, and fields for each form.
* `define( 'WP_FIELDS_API_TESTING', true );` Enable Fields API testing, as used by below.
* `?fields-api-memory-test=1` Enable memory testing of 25 example sections, set to any number greater than 1 to customize how many example sections to add. _Requires WP_FIELDS_API_TESTING on_
* `?no-fields-api=1` Disable Fields API from loading, useful for memory split testing. _Requires WP_FIELDS_API_TESTING to be turned on_
* `?no-fields-api-late-init=1` Disable Fields API Late Init, which means when any form, section, control, or field are added, the object will be setup right away instead of only as needed by current page. Useful for memory split testing. _Requires WP_FIELDS_API_TESTING to be turned on_

### Related Plugins

There are also a few other related plugins that may come in handy:

* [WP Fields Admin UI plugin](https://github.com/sc0ttkclark/wordpress-fields-admin-ui): Build and demo how the Fields API works with an easy to use administration interface that actually lets you create new Fields API configurations.
* [WP Fields API Debug Bar plugin](https://github.com/sc0ttkclark/wordpress-fields-api-debug-bar): See details on how many forms, sections, controls, fields, and other stats about your current Fields API configuration.

## LICENSE

GPLv2 or later. See [License](LICENSE.txt).
