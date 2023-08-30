# Research

## Overview

We will gather research about different APIs for custom field plugins and others with their own Fields
APIs for their forms.

The research we gather will give us a better picture of where things are at and what we can learn from each
implementation.

### Core

* [Settings API](research/core/settings-api.md)
* [Meta Box API](research/core/meta-box-api.md) (Post Types, Media, Comments)
* [List Tables](research/core/list-tables.md)
  * Quick Edit
  * Custom Columns
* [Taxonomies](research/core/taxonomies.md)
    * Add term form
    * Edit term form (separate from the Add term form in how you work with it)
* [Users](research/core/users.md)
    * [User profile / edit form](research/core/user-edit.md)
    * [Add new user form](research/core/user-new.md)
* Media - Media modal
* Comments - Add new comment (front of site)
* Block Editor
    * [Post sections/attributes](research/core/object-settings-sidebar.md) (on the Post/Page tab)
    * [Block sections/controls for Inspector panel](research/core/block-settings-sidebar.md)
* Customizer API
* Nav Menus (classic)
    * Nav Menu form 
    * Nav Menu Item form
* [Widgets (classic)](research/core/widgets.md)

### Third Party

* [Advanced Custom Fields](research/third-party/advanced-custom-fields.md)
* [Meta Box](research/third-party/meta-box.md)
* Pods

## What makes a Fields API?

A Fields API provides the ability to register fields to any given form. With custom fields, that often becomes the Post
Editor screen or other add/edit screens for WP objects. With forms plugins, that can be registering forms themselves or
extending form structures.

The goal of a Fields API is to avoid writing markup and to abstract those things so they offer the most comprehensive
customizations in how those forms appear in different contexts.

Some Fields APIs focus on data structures similar to defining a database table and the corresponding columns. Other
Fields APIs focus entirely on the form fields to output with some amount of handling for data saving included.
