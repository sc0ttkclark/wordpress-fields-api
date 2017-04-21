# Fields API Terminology

<!-- START doctoc generated TOC please keep comment here to allow auto update -->
<!-- DON'T EDIT THIS SECTION, INSTEAD RE-RUN doctoc TO UPDATE -->
**Table of Contents**  *generated with [DocToc](https://github.com/thlorenz/doctoc)*

- [Object Types](#object-types)
- [Object Subtypes](#object-subtypes)
- [Forms](#forms)
- [Sections](#sections)
- [Controls](#controls)
  - [Control Types](#control-types)
  - [Datasources](#datasources)
- [Fields](#fields)
- [Visual Structure for Forms, Sections, and Controls](#visual-structure-for-forms-sections-and-controls)

<!-- END doctoc generated TOC please keep comment here to allow auto update -->

---

## Object Types

Object Types are types of objects in WordPress, but can also represent custom objects from plugins or themes.

See [Object Types and Subtypes](https://github.com/sc0ttkclark/wordpress-fields-api/blob/develop/docs/object-types-and-subtypes.md) for more details.

## Object Subtypes

Object Subtypes are names of subsets of data, like Post Types, Taxonomies, or Comment Types.

See [Object Types and Subtypes](https://github.com/sc0ttkclark/wordpress-fields-api/blob/develop/docs/object-types-and-subtypes.md) for more details

## Forms

Forms in the Fields API are more commonly linked to what appear in the WordPress Admin area as WP_Screen. A few come with the Fields API itself, but you can register new forms to your heart's content and output them wherever you'd like.

See [Registering Fields](https://github.com/sc0ttkclark/wordpress-fields-api/blob/develop/docs/registering-fields.md) for more details and examples.

## Sections

Sections are groupings of controls that give context to what they are for.

See [Registering Fields](https://github.com/sc0ttkclark/wordpress-fields-api/blob/develop/docs/registering-fields.md) for more details and examples.

## Controls

Controls are the input fields used to display field values and allow for changes to those field values.

See [Registering Fields](https://github.com/sc0ttkclark/wordpress-fields-api/blob/develop/docs/registering-fields.md) for more details and examples.

### Control Types

Control types are the types of controls that are registered and available for general use. Some are included with the Fields API, but custom control types can also be registered for advanced usage.

See [Registering Fields](https://github.com/sc0ttkclark/wordpress-fields-api/blob/develop/docs/registering-fields.md) for more details and examples.

### Datasources

Datasources are used by multiple option Control types to provide data in an abstract way. Some are included with the Fields API, but custom datasources can also be registered for advanced usage.

## Fields

Fields handle getting and saving of values and should be namespaced properly (`{my_}field_name`) for your project. They should be unique for the object type and Object subtype you use it on, otherwise it will be overwritten each time a duplicate one is added.

See [Registering Fields](https://github.com/sc0ttkclark/wordpress-fields-api/blob/develop/docs/registering-fields.md) for more details and examples.

## Visual Structure for Forms, Sections, and Controls

![Structure Example](https://raw.githubusercontent.com/sc0ttkclark/wordpress-fields-api/master/docs/terminology.png)
