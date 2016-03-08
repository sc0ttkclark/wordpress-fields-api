# The difference between Object Types and Object Subtypes

## Object Types

Object Types are types of objects in WordPress, but can also represent custom objects from plugins or themes.

There are a few object types baked into the Fields API:

* `user` (WP_User)
* `post` (WP_Post)
* `term` (WP_Term)
* `comment` (WP_Comment)
* `settings` (Settings API)
* `customizer` (Customizer API)
* Support for custom object types is baked in through hooks

## Object Subtypes

Object Subtypes are names of subsets of data, like Post Types, Taxonomies, or Comment Types. 

There are only a few Object Types that Object Subtypes apply to:

* `post` (Post Types: `post`, `page`, `your_cpt`)
* `term` (Taxonomies: `category`, `post_tag`, `your_ct`)
* `comment` (Comment Types: `comment`, `ping`, `your_cct`)

Object types that do not need an object name include:

* `user`
* `settings`
* `customizer`

Use of object names can be used by custom object types at their discretion.

### Default Object Subtypes and Handling

The default object name is based on the object type, it adds an underscore to the object type -- `_{$object_type}`

When no object name is provided (or it matches the default object name) then those forms/sections/controls/fields apply to all object names for an object type and will be inherited.

An example of that can be seen in the `WP_Fields_API_Form_Term` implementation class, which adds the term `name`, `slug`, `parent`, and `description` fields to *all* taxonomies. Visibility ultimately rests with the section or control capabilities callback, which can further limit access to a specific section or control and prevent them from being viewed/used.