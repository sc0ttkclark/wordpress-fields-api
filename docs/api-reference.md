The Fields API is encapsulated by the `$wp_fields` global. That global exposes a number of important methods. A description of each of those API methods is as follows:

## API methods

* `$wp_fields->add_form( $object_type, $id, $args = array() )`
  * `$object_type` (string) - Object Types are types of objects in WordPress, but can also represent custom objects from plugins or themes. See [Object Types and Subtypes](https://github.com/sc0ttkclark/wordpress-fields-api/blob/master/docs/object-types-and-subtypes.md) for more details.
  * `$id` (string) - Unique form ID
  * `$args` (array) - Lets you optionally pass arbitrary key value pairs to the component. Supported key/value pairs are as follows  
    * `object_subtype` (string) - Object Subtypes are names of subsets of data, like Post Types, Taxonomies, or Comment Types. See [Object Types and Subtypes](https://github.com/sc0ttkclark/wordpress-fields-api/blob/master/docs/object-types-and-subtypes.md) for more details.
    * `capability` (string) - WordPress capability required to interact with component. See [Roles and Capabilities](https://codex.wordpress.org/Roles_and_Capabilities).
    * `priority` (integer) - Allows you to sort the component. Higher priority components are shown first.
    * `type` (string) - Allows you to use a registered component type instead of the default. See register type API methods below.
    * `sections` (array) - Allows you to register sections for the form
  * Returns `$form` WP_Fields_API_Form

* `$wp_fields->add_field( $id, $args = array() )`
  * `$id` (string) - Unique field ID
  * `$args` (array) - Lets you optionally pass arbitrary key value pairs to the component. Supported key/value pairs are as follows  
    * `object_subtype` (string) - Object Subtypes are names of subsets of data, like Post Types, Taxonomies, or Comment Types. See [Object Types and Subtypes](https://github.com/sc0ttkclark/wordpress-fields-api/blob/master/docs/object-types-and-subtypes.md) for more details.
    * `capability` (string) - WordPress capability required to interact with component. See [Roles and Capabilities](https://codex.wordpress.org/Roles_and_Capabilities).
    * `priority` (integer) - Allows you to sort the component. Higher priority components are shown first.
    * `type` (string) - Allows you to use a registered component type instead of the default. See register type API methods below.
  * Returns `$field` WP_Fields_API_Field

* `$wp_fields->get_form( $id )`
  * `$id` (string|WP_Fields_API_Form) - Form ID or object to get.
  * Returns `$form` WP_Fields_API_Form

* `$wp_fields->get_field( $id )`
  * `$id` (string|WP_Fields_API_Field) - Field ID or object to get.
  * Returns `$field` WP_Fields_API_Field

* `$wp_fields->remove_form( $id )`
  * `$id` (string|WP_Fields_API_Form) - Form ID or object to remove.

* `$wp_fields->remove_field( $id )`
  * `$id` (string|WP_Fields_API_Field) - Field ID or object to remove.

* `$wp_fields->register_form_type( $type, $form_class = 'WP_Fields_API_Form' )`
  * `$type` (string) - A unique slug to reference the type by
  * `$form_class` (string) - The name of the class to instantiate objects with using this type. Must extend `WP_Fields_API_Form`.

* `$wp_fields->register_section_type( $type, $section_class = 'WP_Fields_API_Section' )`
  * `$type` (string) - A unique slug to reference the type by
  * `$section_class` (string) - The name of the class to instantiate objects with using this type. Must extend `WP_Fields_API_Section`.

* `$wp_fields->register_control_type( $type, $control_class = 'WP_Fields_API_Control' )`
  * `$type` (string) - A unique slug to reference the type by
  * `$control_class` (string) - The name of the class to instantiate objects with using this type. Must extend `WP_Fields_API_Control`.

* `$wp_fields->register_field_type( $type, $field_class = 'WP_Fields_API_Field' )`
  * `$type` (string) - A unique slug to reference the type by
  * `$field_class` (string) - The name of the class to instantiate objects with using this type. Must extend `WP_Fields_API_Field`.

## Form methods

* `$form->add_section( $id, $args = array() )`
  * `$id` (string) - Unique section ID
  * `$args` (array|WP_Fields_API_Section) - Lets you optionally pass arbitrary key value pairs to the component. Supported key/value pairs are as follows
    * `object_subtype` (string) - Object Subtypes are names of subsets of data, like Post Types, Taxonomies, or Comment Types. See [Object Types and Subtypes](https://github.com/sc0ttkclark/wordpress-fields-api/blob/master/docs/object-types-and-subtypes.md) for more details.
    * `capability` (string) - WordPress capability required to interact with component. See [Roles and Capabilities](https://codex.wordpress.org/Roles_and_Capabilities).
    * `priority` (integer) - Allows you to sort the component. Higher priority components are shown first.
    * `type` (string) - Allows you to use a registered component type instead of the default. See register type API methods below.
    * `label` (string) - Label to use for section
    * `display_label` (boolean) - True to display label
    * `description` (string) - Description text to use for section
    * `description_callback` (callable) - Lets you override description display function
    * `controls` (array) - Allows you to register controls for the section
  * Returns `$section` WP_Fields_API_Section

* `$form->get_section( $id )`
  * `$id` (string|WP_Fields_API_Section) - Section ID or object to get.
  * Returns `$section` WP_Fields_API_Section

* `$form->remove_section( $id )`
  * `$id` (string|WP_Fields_API_Section) - Section ID or object to remove.

## Section methods

* `$section->add_control( $id, $args = array() )`
  * `$id` (string) - Unique control ID
  * `$args` (array) - Lets you optionally pass arbitrary key value pairs to the component. Supported key/value pairs are as follows
    * `object_subtype` (string) - Object Subtypes are names of subsets of data, like Post Types, Taxonomies, or Comment Types. See [Object Types and Subtypes](https://github.com/sc0ttkclark/wordpress-fields-api/blob/master/docs/object-types-and-subtypes.md) for more details.
    * `capability` (string) - WordPress capability required to interact with component. See [Roles and Capabilities](https://codex.wordpress.org/Roles_and_Capabilities).
    * `priority` (integer) - Allows you to sort the component. Higher priority components are shown first.
    * `type` (string) - Allows you to use a registered component type instead of the default. See register type API methods below.
    * `label` (string) - Label to use for section
    * `display_label` (boolean) - True to display label
    * `description` (string) - Description text to use for section
    * `description_callback` (callable) - Lets you override description display function
    * `section` (string|WP_Fields_API_Section|array) - Section ID or object to use as component parent. If array, array will be passed as `$args` to add section which will be used as the component parent.
    * `field` (string|WP_Fields_API_Field|array) - Field ID or object to use as component parent. If array, array will be passed as `$args` to add field which will be used as the component parent.
  * Returns `$control` WP_Fields_API_Control

* `$section->get_control( $id )`
  * `$id` (string|WP_Fields_API_Control) - Control ID or object to get.
  * Returns `$control` WP_Fields_API_Control

* `$section->remove_control( $id )`
  * `$id` (string|WP_Fields_API_Control) - Control ID or object to remove.

## Control methods

* `$control->add_field( $id, $args = array() )`
  * `$id` (string) - Unique field ID
  * `$args` (array) - Lets you optionally pass arbitrary key value pairs to the component. Supported key/value pairs are as follows  
    * `object_subtype` (string) - Object Subtypes are names of subsets of data, like Post Types, Taxonomies, or Comment Types. See [Object Types and Subtypes](https://github.com/sc0ttkclark/wordpress-fields-api/blob/master/docs/object-types-and-subtypes.md) for more details.
    * `capability` (string) - WordPress capability required to interact with component. See [Roles and Capabilities](https://codex.wordpress.org/Roles_and_Capabilities).
    * `priority` (integer) - Allows you to sort the component. Higher priority components are shown first.
    * `type` (string) - Allows you to use a registered component type instead of the default. See register type API methods below.
  * Returns `$field` WP_Fields_API_Field

* `$control->add_datasource( $type, $args = array() )`
  * `$type` (string) - Datasource type
  * `$args` (array) - Lets you optionally pass arbitrary key value pairs to the component. Supported key/value pairs are as follows  
    * `get_args` (string) - Object Subtypes are names of subsets of data, like Post Types, Taxonomies, or Comment Types. See [Object Types and Subtypes](https://github.com/sc0ttkclark/wordpress-fields-api/blob/master/docs/object-types-and-subtypes.md) for more details.
    * `hierarchical` (string) - WordPress capability required to interact with component. See [Roles and Capabilities](https://codex.wordpress.org/Roles_and_Capabilities).
    * `hierarchical_fields` (array) - Allows you to sort the component. Higher priority components are shown first.
  * Returns `$datasource` WP_Fields_API_Datasource

* `$control->get_field()`
  * Returns `$field` WP_Fields_API_Field

* `$control->get_datasource()`
  * Returns `$datasource` WP_Fields_API_Datasource

* `$control->remove_field()`

* `$control->remove_datasource()`