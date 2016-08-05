# Fields API Progress

<!-- START doctoc generated TOC please keep comment here to allow auto update -->
<!-- DON'T EDIT THIS SECTION, INSTEAD RE-RUN doctoc TO UPDATE -->
**Table of Contents**  *generated with [DocToc](https://github.com/thlorenz/doctoc)*

- [Users](#users)
    - [Contributors](#contributors)
    - [Details](#details)
    - [Examples](#examples)
    - [Remaining tasks](#remaining-tasks)
- [Post Types](#post-types)
    - [Contributors](#contributors-1)
    - [Details](#details-1)
    - [Remaining tasks](#remaining-tasks-1)
- [Taxonomies](#taxonomies)
    - [Contributors](#contributors-2)
    - [Details](#details-2)
    - [Examples](#examples-1)
    - [Remaining tasks](#remaining-tasks-2)
- [Comments](#comments)
    - [Contributors](#contributors-3)
    - [Details](#details-3)
    - [Remaining tasks](#remaining-tasks-3)
- [Settings](#settings)
    - [Contributors](#contributors-4)
    - [Details](#details-4)
    - [Remaining tasks](#remaining-tasks-4)
- [Widgets](#widgets)
    - [Contributors](#contributors-5)
    - [Details](#details-5)
    - [Remaining tasks](#remaining-tasks-5)
- [Nav Menu Items](#nav-menu-items)
    - [Contributors](#contributors-6)
    - [Details](#details-6)
    - [Remaining tasks](#remaining-tasks-6)
- [Media](#media)
    - [Contributors](#contributors-7)
    - [Remaining tasks](#remaining-tasks-7)
- [REST API](#rest-api)
    - [Contributors](#contributors-8)
    - [Remaining tasks](#remaining-tasks-8)

<!-- END doctoc generated TOC please keep comment here to allow auto update -->

---

## Overview

* There's still work to be done to [Restructure classes and simplify code](https://github.com/sc0ttkclark/wordpress-fields-api/pull/79) with major assistance by @tlovett1.
* Not all control types are working including Media and Color. We need to put additional effort into the JS side of things.

---

## Users

#### Contributors

@sc0ttkclark, @technosailor

#### Details

* Our [internal implementation](https://github.com/sc0ttkclark/wordpress-fields-api/blob/develop/implementation/wp-includes/fields-api/forms/class-wp-fields-api-form-user-edit.php) of the Edit User screen replaces core's entirely using the Fields API.
* Backwards compatible with existing actions and hooks being used to extend the Edit User screen.

#### Examples

* [Basic Starter Example](https://github.com/sc0ttkclark/wordpress-fields-api/blob/develop/docs/examples/user/_starter.php)
* [Address Fields](https://github.com/sc0ttkclark/wordpress-fields-api/blob/develop/docs/examples/user/address.php)

#### Remaining tasks

* Add custom markup and styling to match uniform layout.

---

## Post Types

#### Contributors

@sc0ttkclark, @brentvr

#### Details

* Our [internal implementation](https://github.com/sc0ttkclark/wordpress-fields-api/blob/develop/implementation/wp-includes/fields-api/forms/class-wp-fields-api-form-post.php) of the Edit Post screen will replace core's entirely using the Fields API.
* Backwards compatible with some existing actions and hooks being used to extend the Edit Post screen.

#### Remaining tasks

* Create Basic Starter example code.
* Create example use-case(s) and code.
* Register sections / controls based on what's on the Post Editor screen currently.
* Add additional output type for outputting a section with no meta box.
* Complete backwards compatibility with existing actions and hooks.

---

## Taxonomies

#### Contributors

@sc0ttkclark, @technosailor

#### Details

* Our internal implementations of the [Add Term](https://github.com/sc0ttkclark/wordpress-fields-api/blob/develop/implementation/wp-includes/fields-api/forms/class-wp-fields-api-form-term-add.php) and [Edit Term](https://github.com/sc0ttkclark/wordpress-fields-api/blob/develop/implementation/wp-includes/fields-api/forms/class-wp-fields-api-form-term.php) screen replaces core's entirely using the Fields API.
* Backwards compatible with existing actions and hooks being used to extend the Add Term and Edit Term screens.

#### Examples

* [Basic Starter Example](https://github.com/sc0ttkclark/wordpress-fields-api/blob/develop/docs/examples/term/_starter.php)

#### Remaining tasks

* Create example use-case(s) and code.

---

## Comments

#### Contributors

@sc0ttkclark

#### Details

* Our [internal implementation](https://github.com/sc0ttkclark/wordpress-fields-api/blob/develop/implementation/wp-includes/fields-api/forms/class-wp-fields-api-form-comment.php) of the Edit Comment screen will replace core's entirely using the Fields API.
* Backwards compatible with some existing actions and hooks being used to extend the Edit Comment screen.

#### Remaining tasks

* Create Basic Starter example code.
* Create example use-case(s) and code.
* Register sections / controls based on what's on the Comment Editor screen currently.
* Add additional output type for outputting a section with no meta box.
* Complete backwards compatibility with existing actions and hooks.

---

## Settings

#### Contributors

@sc0ttkclark, @technosailor

#### Details

* Our [internal implementation](https://github.com/sc0ttkclark/wordpress-fields-api/blob/develop/implementation/wp-includes/fields-api/forms/settings/) of the Settings pages replace core's entirely using the Fields API.
  * [General](https://github.com/sc0ttkclark/wordpress-fields-api/blob/develop/implementation/wp-includes/fields-api/forms/settings/class-wp-fields-api-form-settings-general.php)
  * [Reading](https://github.com/sc0ttkclark/wordpress-fields-api/blob/develop/implementation/wp-includes/fields-api/forms/settings/class-wp-fields-api-form-settings-reading.php)
  * [Writing](https://github.com/sc0ttkclark/wordpress-fields-api/blob/develop/implementation/wp-includes/fields-api/forms/settings/class-wp-fields-api-form-settings-writing.php)
  * [Permalink](https://github.com/sc0ttkclark/wordpress-fields-api/blob/develop/implementation/wp-includes/fields-api/forms/settings/class-wp-fields-api-form-settings-permalink.php)
* [Register Sections and Fields to Settings API](https://github.com/sc0ttkclark/wordpress-fields-api/blob/develop/implementation/wp-includes/fields-api/forms/settings/class-wp-fields-api-settings-api.php) based on what's been registered to the Fields API
* [Settings API calling Fields API for config and rendering](https://github.com/sc0ttkclark/wordpress-fields-api/blob/develop/implementation/wp-admin/includes/template.php), not testable because functions can't be overridden in wp-admin/includes/template.php

#### Remaining tasks

* Implementations for Discussion and Media settings pages.
* Create Basic Starter example code.
* Create example use-case(s) and code.
* Create custom control types for some of the advanced / unique inputs.

---

## Widgets

#### Contributors

@nicholasio, @sc0ttkclark

#### Details

* Our [internal implementation](https://github.com/sc0ttkclark/wordpress-fields-api/pull/69) of the Widget forms will integrate with WP_Widget to allow form fields to be registered for widgets and rendered/saved automatically without the custom code every widget ends up needing currently.

#### Remaining tasks

* `WP_Widget` integration
* Create Basic Starter example code.
* Create example use-case(s) and code.

---

## Nav Menu Items

#### Contributors

@diddledan, @sc0ttkclark

#### Details

* Our [internal implementation](https://github.com/sc0ttkclark/wordpress-fields-api/pull/68) of the Nav Menu Items overrides the core Walker class used for building the Nav Menu Item forms, and uses the Fields API to output registered controls.

#### Remaining tasks

* Create Basic Starter example code.
* Create example use-case(s) and code.
* More compatibility work for CSS
* Look into Customizer integration
* Getting / saving values for nav menu item fields

---

## Media

#### Contributors

Looking for contributors

#### Remaining tasks

* Add sections and controls to Media Edit
* Add sections and controls to Media Modal Add / Edit
* Create Basic Starter example code.
* Create example use-case(s) and code.

---

## REST API

#### Contributors

@sc0ttkclark

#### Remaining tasks

* We currently have limited direct integration with the REST API, but we'd like to work with the REST API team towards [implementing REST API options](https://github.com/sc0ttkclark/wordpress-fields-api/issues/39) and building in addition configurations the REST API can consume for it's endpoint(s).
* `register_meta()` integration for `show_in_rest`