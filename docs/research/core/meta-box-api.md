# Meta Box API

## Registering Forms

Forms in this case are handled by the screens that have support for rendering the meta boxes including: post types,
media, and comments.

**Exceptions for meta box handling:**

* Media Modals do not use meta boxes (Media edit has meta boxes)
* Comment add new form on the frontend does not use meta boxes (Comment edit has meta boxes)
* Taxonomies and Users are the only content objects in WordPress that do not leverage meta boxes anywhere

## Registering Sections

Sections in the Meta Box API are the meta boxes themselves.

The registration of meta boxes should be done during the `add_meta_boxes` action.

### Register meta box for any type

```php
add_action( 'add_meta_boxes', 'my_meta_box' );

function my_meta_box( $post_type ) {
	add_meta_box( 'my-meta-box', __( 'My meta box' ), 'my_meta_box_output' );
}
```

### Register meta box for a specific post type

```php
// Format: add_meta_boxes_{$post_type}
add_action( 'add_meta_boxes_post', 'my_meta_box_for_post' );

function my_meta_box_for_post() {
	add_meta_box( 'my-meta-box-for-post', __( 'My meta box for post' ), 'my_meta_box_output_for_post' );
}
```

### Register meta box for comments

```php
add_action( 'add_meta_boxes_comment', 'my_meta_box_for_comment' );

function my_meta_box_for_comment() {
	add_meta_box( 'my-meta-box-for-comment', __( 'My meta box for comment' ), 'my_meta_box_output_for_comment' );
}
```

### Output

Output from the meta box is tied to the callback argument for the `add_meta_box()` call.

You must output all fields manually in this function.

#### Output meta box for any type

```php
function my_meta_box_output() {
?>
	<label>
		My meta box field
		<input type="text" name="my-meta-box-field" value="" />
	</label>
<?php
}
```

#### Output meta box for a specific post type

```php
function my_meta_box_output_for_post() {
?>
	<label>
		My meta box field for post
		<input type="text" name="my-meta-box-field-for-post" value="" />
	</label>
<?php
}
```

#### Output meta box for comments

```php
function my_meta_box_output_for_comment() {
?>
	<label>
		My meta box field for comment
		<input type="text" name="my-meta-box-field-for-comment" value="" />
	</label>
<?php
}
```

## Registering Fields

Registering fields for meta boxes requires no special calls but the saving of them must be handled on your own through the `save_post` hook.

#### Save meta box field for any type

```php
add_action( 'save_post', 'my_meta_box_save' );

function my_meta_box_save( $post_id ) {
    if ( isset( $_POST['my-meta-box-field'] ) ) {
        $value_to_save = sanitize_text_field( $_POST['my-meta-box-field'] );

        update_post_meta( $post_id, 'my-meta-box-field', $value_to_save );
    }
}
```

#### Save meta box field for a specific post type

```php
add_action( 'save_post', 'my_meta_box_save_for_post' );

function my_meta_box_save_for_post( $post_id ) {
    if ( isset( $_POST['my-meta-box-field-for-post'] ) ) {
        $value_to_save = sanitize_text_field( $_POST['my-meta-box-field-for-post'] );

        update_post_meta( $post_id, 'my-meta-box-field-for-post', $value_to_save );
    }
}
```

#### Save meta box field for comments

```php
add_action( 'edit_comment', 'my_meta_box_save_for_comment' );

function my_meta_box_save_for_comment( $comment_id ) {
    if ( isset( $_POST['my-meta-box-field-for-comment'] ) ) {
        $value_to_save = sanitize_text_field( $_POST['my-meta-box-field-for-comment'] );

        update_comment_meta( $comment_id, 'my-meta-box-field-for-comment', $value_to_save );
    }
}
```

# Lessons to be learned from the Meta Box API approach

The Meta Box API has been around since March 2008 and is a core API within WordPress itself.

The Meta Box API itself is mostly similar to hooks and still requires a lot of HTML markup to be written by the
developer to piece together the implementation of a custom meta box. The only part that is "done" for you is the markup
and functionality of a meta box itself that can be moved, hidden, and expanded/collapsed.

Function calls are used everywhere in the Settings API for registering settings and outputting them.

| Registration Type  | Supports JSON Files | Supports Multiple Content Types      | Form / HTML markup required |
|--------------------|---------------------|--------------------------------------|-----------------------------|
| PHP function calls | No                  | Yes, post types, media, and comments | Yes                         |
