# Widget

While the [Widgets API](https://developer.wordpress.org/themes/functionality/widgets/) could be considered deprecated at this point, you can still register and use them to output custom field values.

## Registering Widget Forms

Extend the `WP_Widget` class and register your widget with `register_widget()`.

```php
<?php
/**
 * Widget: Fields Test
 *
 * @package fields-test
 */

class FieldsTestWidget extends WP_Widget {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			'fields_test_widget', // Base ID.
			__( 'Fields Test Widget', 'fields-test' ), // Widget name.
			[
				'description' => __( 'A Fields Test Widget', 'fields-test' ),
			]
		);
	}

	/**
	 * Widget front-end display.
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
		extract( $args ); // $before_widget, $after_widget, $before_title, $after_title.

		// Handle standard widget_title filter.
		$title = apply_filters( 'widget_title', $instance['title'] );

		// Display the widget with required before/after values.
		echo $before_widget;
		if ( ! empty( $title ) ) {
			echo $before_title . $title . $after_title;
		}
		echo $after_widget;
	}

	/**
	 * Options form in the admin.
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		// Get widget form field values.
		if ( isset( $instance['title'] ) ) {
			$title = $instance['title'];
		} else {
			$title = __( 'New title', 'fields-test' );
		}

		// Display the admin form.
		?>
		<p>
			<label for="<?php echo $this->get_field_name( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<?php
	}

	/**
	 * Process widget options to be saved.
	 *
	 * @param array $new_instance New values from the form.
	 * @param array $old_instance Previously saved values from database.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();

		// Handle field validation/sanitization.
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';

		return $instance;
	}
}

function fields_test_register_widget() {
	register_widget( 'FieldsTestWidget' );
}
add_action( 'widgets_init', 'fields_test_register_widget' );
```

## Including the Widget Manually

While generally it's up to the user to add your widget to a sidebar, you can also include it manually in a template:

```php
<?php
/**
 * Template: Widget
 *
 * @package fields-test
 */

get_header();

the_widget( 'FieldsTestWidget' );

get_footer();
```

This will render the first instance of the widget found in any registered sidebar.

## Lessons to be learned from the Widget API approach

Using the Widgets API is an excercise in boilerplate. You have to handle saving, rendering and field markup yourself, as well as including standard hooks for things like `widget_title` and other required output from passed-in arguments. The field markup in particular can be a pain point if you care about whether the form looks like other forms in the admin (in which case you need to find the undocumented classnames other fields are using), as well as a potential accessibility concern if not done properly. There is no field configuration for specifying field value type or any built-in field validation.

You are also responsible for handling any custom form styling or JavaScript for validation yourself.

| Registration Type  | Supports JSON Files | Supports Multiple Content Types | Form / HTML markup required |
|--------------------|---------------------|---------------------------------|-----------------------------|
| PHP function calls | No                  | No                              | Yes                         |
