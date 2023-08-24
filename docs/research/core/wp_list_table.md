# List Table API

[Handbook](https://developer.wordpress.org/reference/classes/wp_list_table/) This class is used to generate the List Tables that populate WordPress’ various admin screens. It has an advantage over previous implementations in that it can be dynamically altered with AJAX and may be hooked in future WordPress releases.

## Using WP_List_Table
To use the `WP_List_Table`, you first create a new class that extends the original. Your new class must be instantiated, and the prepare_items() and display() methods called explicitly on the instance. See the method descriptions below for more details.
