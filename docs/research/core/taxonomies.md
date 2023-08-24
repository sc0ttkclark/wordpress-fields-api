# Taxonomies

The Taxonomy admin screens use two different forms for adding and editing terms. Those forms don't separate their fields into sections and there is no API to register new sections or fields. Instead, developers can use hooks to echo HTML code for additional sections and fields at the bottom of the standard form. 

## Add Term form

The Add Term form uses two actions to allow developers to insert new fields:

| action                | description                                                                          |
|-----------------------|--------------------------------------------------------------------------------------|    
| `add_tag_form_fields` | Fires after the Add Tag form fields. Triggered for non-hierarchical taxonomies only. |
| `"{$taxonomy}_add_form_fields"`                      | Fires after the Add Term form fields. The dynamic portion of the hook name, `$taxonomy`, refers to the taxonomy slug.                                               |

### Registering a section and a field for any non-hierarchical taxonomy

```php
add_action( 'add_tag_form_fields', 'taxonomy_urls_add_url_field_to_add_term_form' );

function taxonomy_urls_add_url_field_to_add_term_form() {
    ?>
    <h3><?php esc_html_e( 'Custom attributes' ); ?></h3>

    <div class="form-field">
        <label for="tag-url"><?php esc_html_e( 'URL' ); ?></label>
        <input name="url" id="tag-url" type="url" value="" size="40 aria-describedby="url-description" />
        <p id="url-description"><?php esc_html_e( 'An external URL for this term.' ); ?></p>
    </div>
    <?php
}
```

### Registering a section and a field for post categories

```php
add_action( 'category_add_form_fields', 'taxonomy_urls_add_url_field_to_add_term_form' );

function taxonomy_urls_add_url_field_to_add_term_form() {
    ?>
    <h3><?php esc_html_e( 'Custom attributes' ); ?></h3>

    <div class="form-field">
        <label for="tag-url"><?php esc_html_e( 'URL' ); ?></label>
        <input name="url" id="tag-url" type="url" value="" size="40 aria-describedby="url-description" />
        <p id="url-description"><?php esc_html_e( 'An external URL for this term.' ); ?></p>
    </div>
    <?php
}
```

## Edit Term form

The standard Edit Term form includes the same fields that the standard Add Term form, but they use separate implementations. As a result, new form sections and fields also need separate implementations to show up in both forms.

The Edit Term form uses one action that allows developers to define new fields:

| action                | description                                                                                                                          |
|-----------------------|--------------------------------------------------------------------------------------------------------------------------------------|    
| `"{$taxonomy}_edit_form_fields"`                     | Fires after the Edit Term form fields are displayed. The dynamic portion of the hook name, `$taxonomy`, refers to the taxonomy slug. |

Developers must register a handler for each term that needs the new field because the slug of the term is part of the hook name.

### Registering a section and a field for post categories

```php
add_action( 'category_edit_form_fields', 'taxonomy_urls_add_url_field_to_edit_term_form' );

function taxonomy_urls_add_url_field_to_edit_term_form( $tag ) {
    $url = get_term_meta( $tag->term_id, 'url', true );

    ?>
    <tr>
        <th scope="row" colspan="2"><?php _e( 'Custom attributes' ); ?></th>
    </tr>

    <tr class="form-field">
        <th scope="row"><label for="url"><?php _e( 'URL' ); ?></label></th>
        <td>
            <input name="url" id="url" type="url" size="40" value="<?php echo esc_attr( $url ); ?>" aria-describedby="url-description">
            <p class="description" id="url-description"><?php esc_html_e('An external URL for this term.'); ?></p>
        </td>
    </tr>
    <?php
}
```

## Saving data

The Taxonomy admin screens use the `wp_insert_term()` and `wp_update_term()` functions to save submitted data. Both functions trigger the `saved_term` action.

## Saving data for a field

```php
add_action( 'saved_term', 'taxonomy_urls_save_url_field', 10, 5 );

function taxonomy_urls_save_url_field( $term_id, $tt_id, $taxonomy, $update, $args ) {
    // return early if wp_insert_term() or wp_update_term() are used outside of the admin screens
    if ( ! isset( $args['url'] ) ) {
        return;
    }

    // allow valid URLs or empty strings only
    $url = $args['url'] === '' ? '' : filter_var( $args['url'], FILTER_VALIDATE_URL );
    
    if ( ! is_string( $url ) ) {
        return;
    }

    if ( $update ) {
        update_term_meta( $term_id, 'url', (string) $url );
    } elseif ( $url ) {
        add_term_meta( $term_id, 'url', (string) $url, true );
    }
}
```

# Lessons to be learned from the Taxonomies admin screens approach

* There are no hooks to insert fields between the standard fields or to modify those fields.
* Sections and fields use different markup for the Add Term and Edit Term forms so each fields needs to be "registered" twice
* The logic that save the value of a field needs to detect between form submissions that include values for the custom fields and function calls triggered in other scenarios

| Registration Type  | Supports JSON Files | Supports Multiple Content Types | Form / HTML markup required |
|--------------------|---------------------|---------------------------------|-----------------------------|
| PHP function calls | No                  | No, taxonomies only             | Yes                         |