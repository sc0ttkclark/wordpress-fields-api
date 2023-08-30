# Add User 

## Action: `user_new_form`

The add user form (`user-new.php`) contains one action that can be used for register new usermeta fields.

### Example Registration

This action fires after the main `<table>` of fields, immediately before the submit button. 

So an entire table with header is needed to register new fields.

```php
add_action( 'user_new_form', 'setup_user_fields' );

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

_Tip:_ It may be beneficial to reuse `setup_user_fields()` in the context of the user edit form. See [User Profile / Edit User](user-edit.md) for relevant actions.


### Multisite

Multisite installs use this same action. 

When adding a new user in the context of an individual site this action fires in the "add existing user" and "add new uesr" sections.


## Saving 

See [User Meta](users.md)