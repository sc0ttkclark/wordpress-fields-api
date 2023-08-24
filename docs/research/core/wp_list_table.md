# List Table API

A List Table is the output of the generic WP_List_Table class object on various admin screens that list various WordPress data types in table form such as posts, pages, media, etc.

* [Custom List Table Columns](#custom-list-table-columns)
  * [Table Cell Content](#table-cell-content)
* [Using WP_List_Table](#using-wp_list_table)
  * [Uses in Core](#uses-in-core)
  * [Unit Tests](#unit-tests)
  * [Third Party List Table Integration](#third-party-list-table-integration)

## Custom List Table Columns
[Handbook](https://make.wordpress.org/docs/plugin-developer-handbook/10-plugin-components/custom-list-table-columns/): To add a custom column to the List Table, a developer must first add its name to the array of column header names. This is done by hooking into the 'manage_{$screen->id}_columns' filter.
```php
function my_custom_posts_column( $columns ) {
    $columns["metabox"] = "Metabox";
    return $columns;
}
add_filter('manage_posts_columns', 'my_custom_posts_column');
```
After defining the column, adding sortable filter `manage_{$screen->id}_sortable_columns` hook to the same custom function will add the same custom column(s) to the sortable array.

### Table Cell Content

[Handbook](https://make.wordpress.org/docs/plugin-developer-handbook/10-plugin-components/custom-list-table-columns/): To manage the dynamic custom cell value, the action `manage_{$data_type}_custom_column` hook is available for most data uses.
```php
function page_custom_column_views( $column_name, $post_id ) {
	if ( $column_name === 'metabox' ) {
		echo get_post_meta( $post_id , 'custom_field_key' , true );
	}
}
add_action( 'manage_posts_custom_column', 'my_custom_cell_value', 5, 2 );
```

## Using WP_List_Table
[Handbook](https://developer.wordpress.org/reference/classes/wp_list_table/):  This class is used to generate the List Tables that populate WordPress’ various admin screens. It has an advantage over previous implementations in that it can be dynamically altered with AJAX and may be hooked in future WordPress releases.

> The WordPress core loads and returns its classes dynamically by using the _get_list_table() function, which automatically loads the appropriate extended class and instantiates it. This is a private function, however, and should not be used by developers.

### Uses in Core
* `src/wp-admin/edit.php` : line 52
```
52:    $wp_list_table = _get_list_table( 'WP_Posts_List_Table' );
```
* `src/wp-admin/includes/ajax-actions.php`
```
1127:  $wp_list_table = _get_list_table( 'WP_Terms_List_Table', array( 'screen' => $_POST['screen'] ) );
...
1420:  $wp_list_table = _get_list_table( 'WP_Comments_List_Table', array( 'screen' => 'edit-comments' ) );
```
* `src/wp-admin/includes/meta-boxes.php`
```
909:   $wp_list_table = _get_list_table( 'WP_Post_Comments_List_Table' );
```
* `src/wp-admin/edit-comments.php`
```
19:    $wp_list_table = _get_list_table( 'WP_Comments_List_Table' );
```
* `src/wp-admin/includes/dashboard.php`
```
1115:   _get_list_table( 'WP_Comments_List_Table' )->views();
```
* `src/wp-admin/edit-tags.php`
```
41:    $wp_list_table = _get_list_table( 'WP_Terms_List_Table' );
```
* `src/wp-admin/user-edit.php`
```
813:   $application_passwords_list_table = _get_list_table( 'WP_Application_Passwords_List_Table', array( 'screen' => 'application-passwords-user' ) );
814:   $application_passwords_list_table->prepare_items();
815:   $application_passwords_list_table->display();
```
* `src/wp-admin/network/sites.php`
```
17:    $wp_list_table = _get_list_table( 'WP_MS_Sites_List_Table' );
```
* `src/wp-admin/network/site-users.php`
```
17:    $wp_list_table = _get_list_table( 'WP_Users_List_Table' );
```
* `src/wp-admin/upload.php`
```
242:   $wp_list_table = _get_list_table( 'WP_Media_List_Table' );
```
* `src/wp-admin/erase-personal-data.php`
```
90:    $requests_table = _get_list_table( 'WP_Privacy_Data_Removal_Requests_List_Table', $_list_table_args );
```
* `src/wp-admin/users.php`
```
21:    $wp_list_table = _get_list_table( 'WP_Users_List_Table' );
```
* `src/wp-admin/includes/template.php`
```
447:   $wp_list_table = _get_list_table( 'WP_Post_Comments_List_Table' );
...
449:   $wp_list_table = _get_list_table( 'WP_Post_Comments_List_Table' );
```
* `src/wp-admin/network/users.php`
```
210:   $wp_list_table = _get_list_table( 'WP_MS_Users_List_Table' );
```

#### Unit Tests
* tests/phpunit/tests/admin/wpUsersListTable.php
* tests/phpunit/tests/admin/wpThemeInstallListTable.php
* tests/phpunit/tests/admin/wpPostCommentsListTable.php
* tests/phpunit/tests/admin/wpPluginInstallListTable.php
* tests/phpunit/tests/multisite/wpMsUsersListTable.php
* tests/phpunit/tests/multisite/wpMsThemesListTable.php
* tests/phpunit/tests/admin/wpListTable.php
* tests/phpunit/tests/admin/wpPostsListTable.php
* tests/phpunit/tests/admin/wpCommentsListTable.php
* tests/phpunit/tests/multisite/wpMsSitesListTable.php
* tests/phpunit/tests/admin/wpPluginsListTable.php

### Third Party List Table Integration
> Third Party developers cannot use the _get_list_table() function directly, as it is a private function, instead to use the `WP_List_Table`, a new class that extends the original must be created. the class should be extended and instantiated manually, and the prepare_items() and display() methods called explicitly on the instance.
