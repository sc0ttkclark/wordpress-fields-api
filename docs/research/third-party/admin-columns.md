# Admin Columns

[Admin Columns](https://wordpress.org/plugins/admin-columns/) integrates an editable experience into [`WP_List_Table`](https://developer.wordpress.org/reference/classes/wp_list_table/) for Post Types (including Custom), Users, and Media. The ability to leverage a uniform API for List Tables and Quick Edit is paramount for improving adoption and unified experience.

Reference: [Hooks & Filters](https://docs.admincolumns.com/article/15-hooks-and-filters)

The plugin uses _abstract_ class `ListScreen` to register columns by `screen_id`.

## Column
> Filter: [ac/column/value](https://github.com/codepress/admin-columns-hooks/blob/master/ac-column-value.php)
> This uses _abstract_ class `ManageValue` to sanatize the value for cell level display on a particular column. Ultimately this uses [`get_post_field`](https://developer.wordpress.org/reference/functions/get_post_field/) to retrieve the field value.

> Filter: [ac/headings/label](https://github.com/codepress/admin-columns-hooks/blob/master/ac-headings-label.php)
> This uses class `Column` to sanatize the value for a custom column heading display.

## Custom Field
> Filter: [ac/column/custom_field/use_text_input](https://github.com/codepress/admin-columns-hooks/blob/master/ac-column-custom-field-use_text_input.php)

> Filter: [acp/custom_field/stored_date_format](https://github.com/codepress/admin-columns-hooks/blob/master/ac-column-custom-field-stored_date_format.php)
