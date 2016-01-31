=== Fields API - A proposal for WordPress core ===
Contributors: sc0ttkclark, helen, technosailor, idealien, nicholasio, celloexpressions, diddledan
Tags: beta, custom fields, fields
Requires at least: 4.4
Tested up to: 4.4
Stable tag: 0.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This is a core proposal for a new wide-reaching API for WordPress core. It is currently an evolving prototype that can be installed as a plugin for easy testing and usage throughout development.

This was initially a project of the [WordPress core Options/Metadata team](http://make.wordpress.org/core/components/options-meta/) but is currently led by Scott Kingsley Clark with oversight by WordPress core lead developer Helen Hou-Sandí.

**Please note:** This plugin is still in the early stages of development and should not be used on production sites. It should be assumed that until the v1.0 release, the Fields API could change significantly due to core scrutiny and the final merge proposal response.

= Documentation =

* [Terminology](https://github.com/sc0ttkclark/wordpress-fields-api/blob/master/docs/terminology.md)
* [Object Types and Object Names](https://github.com/sc0ttkclark/wordpress-fields-api/blob/master/docs/object-types-and-names.md)
* [Registering Fields (in depth)](https://github.com/sc0ttkclark/wordpress-fields-api/blob/master/docs/registering-fields.md)
* [Creating an Implementation (advanced)](https://github.com/sc0ttkclark/wordpress-fields-api/blob/master/docs/creating-an-implementation.md)

= Example Code =

**User Profile Form:**

* [Starter example](https://github.com/sc0ttkclark/wordpress-fields-api/blob/master/docs/examples/user/_starter.php)
* [Address section and fields example](https://github.com/sc0ttkclark/wordpress-fields-api/blob/master/docs/examples/user/address.php)

**Term Add / Edit Form:**

* [Starter example](https://github.com/sc0ttkclark/wordpress-fields-api/blob/master/docs/examples/term/_starter.php)

= Related links =

* [Posts about the WP Fields API on the Official WordPress.org Development blog](https://make.wordpress.org/core/tag/fields-api/)
* [Architecture Documentation](https://docs.google.com/document/d/17yUTO_vlkC7P4_2c6dIDxa5jQbXvfV9SofC7_GOwFME/edit)
* [See the official GitHub for more information and bug reporting](https://github.com/sc0ttkclark/wordpress-fields-api)
* Join us in WordPress Slack in the #core-fields channel for our meetings Mondays at 21:00 UTC