# Comments

Adding custom fields to comment forms requires:

1. Adding field markup "config" to front-end comment form.
2. Validating submitted field value.
3. Saving field value to comment meta.
4. Adding a metabox to the comment edit screen (and also handle saving the field value).

## Adding a Field to the Front-End Comment Form

Custom field markup is added to the comment form by hooking into the `comment_form_default_fields` or `comment_form_fields` filter hooks. Either one works for our purposes of adding a field. The field markup is added to the `$fields` array, which contains the fields output in the form.

```php
function fields_test_add_comment_field( $fields ) {
	$fields['title'] = '<p class="comment-form-fields-test">
		<label for="title">' . __( 'My Field' ) . '</label>
		<input id="title" name="title" type="text" placeholder="' . __( 'Title' ) . '" required="" />
	</p>';

	return $fields;
}
add_filter( 'comment_form_fields', 'fields_test_add_comment_field' );
```

## Validate Submitted Field Value

The submitted field value is validated by hooking into the `preprocess_comment` action hook. This hook fires before the comment is sanitized and saved to the database.

```php
function fields_test_validate_comment_field( $commentdata ) {

	// If the field is required, check that it has a value.
	if ( ! isset( $_POST['title'] ) || empty( $_POST['title'] ) ) {
		wp_die( __( 'Error: please fill the required field (My Field).' ) );
	}

	return $commentdata;
}
add_filter( 'preprocess_comment', 'fields_test_validate_comment_field' );
```

## Save Field Value to Comment Meta

We must handle saving the field value ourselves. This is done by hooking into the `comment_post` action hook. This hook fires after the comment is saved to the database.

```php
function fields_test_save_comment_field( $comment_id ) {
	if ( isset( $_POST['title'] ) ) {
		$value_to_save = sanitize_text_field( $_POST['title'] );

		update_comment_meta( $comment_id, 'title', $value_to_save );
	}
}
add_action( 'comment_post', 'fields_test_save_comment_field' );
```

## Add a Meta Box to the Comment Edit Screen

Oh, did you think you were done? There's one additional place to add your field: on the edit comment admin screen. Unfortunately, the only practical way to add anything on this screen is to add a metabox, which also means you must basically do all of the above again in a different context.

### Register the Meta Box

Register the meta box to display on the edit comments screen via the `add_meta_boxes_comment` action hook.

```php
function fields_test_add_comment_meta_box() {
	add_meta_box(
		'fields_test_comment_meta_box',
		__( 'Fields Test' ),
		'fields_test_render_comment_meta_box',
		'comment',
		'normal',
		'default'
	);
}
add_action( 'add_meta_boxes_comment', 'fields_test_add_comment_meta_box' );
```

### Render the Meta Box

The callback function for the metabox is where we'll output the field markup, including the field value if it exists.

```php
function fields_test_render_comment_meta_box( $comment ) {
	// Fetch existing value.
	$value = get_comment_meta( $comment->comment_ID, 'title', true );
	?>
	<p>
		<label for="title"><?php _e( 'My Field' ); ?></label>
		<input id="title" name="title" type="text" value="<?php echo esc_attr( $value ); ?>" />
	</p>
	<?php
}
```

### Save the Meta Box Field Value

The field value is saved by hooking into the `edit_comment` action hook. Ideally, you might abstract this into a single function that handles saving the field value for both the front-end comment form and the comment edit screen.

```php
function fields_test_save_comment_meta_box( $comment_id ) {
	if ( isset( $_POST['title'] ) ) {
		$value_to_save = sanitize_text_field( $_POST['title'] );

		update_comment_meta( $comment_id, 'title', $value_to_save );
	}
}
add_action( 'edit_comment', 'fields_test_save_comment_meta_box' );
```

# Lessons to be learned from the Comment API approach

Like the Meta Box API (which we had to use anyways for the admin screen), adding fields to the comments form is mostly hooks and function calls and still requires a lot of HTML markup to be written by the developer to display the field(s), as well as boilerplate code to validate and save the field value.

| Registration Type  | Supports JSON Files | Supports Multiple Content Types | Form / HTML markup required |
|--------------------|---------------------|---------------------------------|-----------------------------|
| PHP function calls | No                  | Yes (post types only)           | Yes                         |
