# Profile / Edit User

Users typically updated via `user-edit.php` unless a user is editing their own profile, these edits occur in `profile.php`.

## Registering New Usermeta

The user edit form contains actions which fire in two distinct locations within the form: 

### Action: `personal_options`

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

_Tip:_ It may be beneficial to reuse `setup_user_fields()` in the context of the user edit form. See [Add User](user-new.md) for relevant action.

## Saving

See [User Meta](users.md)