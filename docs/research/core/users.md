# User Meta

This research covers user meta in the context of the user management feature in wp-admin.

## Registering New Usermeta

### Adding "Personal Options"

Use the `personal_options` action to add usermeta to the "personal option" section of at the top of the user edit form (see  [wp-admin/user-edit.php](https://github.com/WordPress/WordPress/blob/099ec7eec8fb89d48a0a1239c8ead5aa58a81295/wp-admin/user-edit.php#L394))

```php
add_action( 'personal_options', 'setup_personal_options' );

public function setup_personal_options() {
    ?>
    <th>
        <label for="new_meta_key">New Meta Name</label>
    </th>
    <td>
		<input type="text" name="new_meta_key" id="new_meta_key"
			value="<?php echo esc_attr( get_user_meta( $user->ID, 'new_meta_key', true ) ); ?>"
			class="regular-text"/>
    </td>
    <?php
}
```

### Adding Usermeta Sections

There are 3 actions that can be used to add new _sections_ of usermeta: 
- `profile_personal_options` fires on `profile.php` immediately below the "Personal Option" section.
- `show_user_profile` fires on `profile.php` near the end of the edit form (see [wp-admin/user-edit.php](https://github.com/WordPress/WordPress/blob/30ffb247b7667516a388d5dd968c2cbd1766cddb/wp-admin/user-edit.php#L835-L844)).
- `edit_user_profile` fires on `user-edit.php` in the same location as `show_user_profile` (see [wp-admin/user-edit.php](https://github.com/WordPress/WordPress/blob/30ffb247b7667516a388d5dd968c2cbd1766cddb/wp-admin/user-edit.php#L846-L853)).


```php
// ONLY profile.php below "Personal Options" 
add_action( 'profile_personal_options', 'setup_user_fields' );
// profile.php near the end of the form
add_action( 'show_user_profile', 'setup_user_fields' );
// user-edit.php near the end of the form
add_action( 'edit_user_profile', 'setup_user_fields' );

public function setup_user_fields( $user ) {
	?>
      <h2>New Section</h2>	
	  <table class="form-table">
		  <tr>
			  <th><label for="new_meta_key">New Meta Name</label></th>
			  <td>
				<input type="text" name="new_meta_key" id="new_meta_key"
						value="<?php echo esc_attr( get_user_meta( $user->ID, 'new_meta_key', true ) ); ?>"
						class="regular-text"/>
			  </td>
		  </tr>
	  </table>
	<?php
}
```

## Saving

The `insert_custom_user_meta` filter appends an arbitrary array immediately prior to running `update_user_meta()` with each element in the array inside `wp_insert_user()` (see [wp-includes/user.php](https://github.com/WordPress/WordPress/blob/30ffb247b7667516a388d5dd968c2cbd1766cddb/wp-includes/user.php#L2430)).

To attach a new meta we inform `wp_insert_user()` about the previously unassigned `$_POST` value. 

```php
add_filter( 'insert_custom_user_meta', 'attach_new_meta_field' );

public function attach_new_meta_field() {
	if ( ! isset( $POST['new_meta_key'] ) ) {
		return [];
	}
	
	return array( 'new_meta_key' => sanitize_text_field( $_POST['new_meta_key'] ) ); 
}
```