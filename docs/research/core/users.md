# User Meta

This research covers user meta in the context of the user management feature in wp-admin.

## Registering New Usermeta

### Adding usermeta to the form

The `*_user_profile` actions fire near the bottom of the user edit form (see [wp-admin/user-edit.php](https://github.com/WordPress/WordPress/blob/30ffb247b7667516a388d5dd968c2cbd1766cddb/wp-admin/user-edit.php#L834-L854)).

```php
add_action( 'show_user_profile', 'setup_user_fields' );
add_action( 'edit_user_profile', 'setup_user_fields' );

public function setup_user_fields( $user ) {
	?>
	  <table class="form-table">
		  <tr>
			  <th><label for="<?= $meta_name ?>">New Meta Name</label></th>
			  <td>
				  <input type="text" name="new_meta_key" id="new_meta_key"
						 value="<?php echo esc_attr(get_user_meta($user->ID, 'new_meta_key', true)); ?>"
						 class="regular-text"/><br/>
			  </td>
		  </tr>
	  </table>
	<?php
}
```

**Notes:**
- `show_` is fired when a user is editing their own profile.
- `edit_` is fired when a user is editing another user's profile. 

### Attaching the meta

The `insert_custom_user_meta` filter appends an arbitrary array immediately prior to running `update_user_meta()` with each element in the array inside `wp_insert_user()` (see [wp-includes/user.php](https://github.com/WordPress/WordPress/blob/30ffb247b7667516a388d5dd968c2cbd1766cddb/wp-includes/user.php#L2430)).

To attach a new meta we inform `wp_insert_user()` about the previously unassigned `$_POST` value. 

```php
add_filter( 'insert_custom_user_meta', 'attach_new_meta_field' );

public function attach_new_meta_field() {
	if ( ! isset( $POST['new_meta_key'] ) ) {
		return [];
	}
	
	return array( 'new_meta_key' => sanitize_text_fields( $_POST['new_meta_key'] ) ); 
}
```