The Fields API allows you to manage various types of components: forms, sections, controls, and fields. Each component is created from it's own class. Sometimes you need to create custom component classes.

## Custom Form Components

Creating a custom form component allows you to create custom save and rendering methods among other things. Your component class must extend `WP_Fields_API_Form` like so:

```php
class WP_Fields_API_Form_Post extends WP_Fields_API_Form {

    /**
     * {@inheritdoc}
     */
    public function setup() {
        //This method will be called after the form is created. You can add sections, controls, and fields here if you want.
    }

    /**
     * {@inheritdoc}
     */
    public function save_fields( $item_id = null, $object_subtype = null ) {
        // Custom saving logic here
    }

}
```