# User Meta

This section of research covers adding and editing usermeta fields to the various wp-admin forms.


## Registering Custom Usermeta Fields

"Registering" new usermeta fields must be accomplished by echoing HTML via different actions depending on the context.

In some contexts, you must echo a single table row, while in others you will need to echo an entire table, perhaps including a header as well. 

_Note:_ It is important to include the wp-admin css classes so the new fields blend in with the admin. 

**See**

- [Add User](user-new.md)
- [Profile / Edit User](user-edit.md)


## Saving Custom Usermeta Fields

All user forms `include 'user.php'` and use the `edit_form()` function to perform user insert and updates. Yes, _adding_ users also uses the `edit_user()` function.  

### Filter: `insert_custom_user_meta`
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