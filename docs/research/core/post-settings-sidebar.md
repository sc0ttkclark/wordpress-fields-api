# Post Settings Sidebar

Meta fields can be registered in the block editor sidebar's "Post" section. These fields apply to the entire post
object, and are generally stored using post meta.

## Setup

To set up a custom field in the settings sidebar, you need to do at least 3 things:

1. Create a script that makes the component to handle the input field in the block editor.
2. Enqueue that script using `wp_enqueue_script`
3. Register the meta field.

### Create the Script

This assumes you're using
the [@wordpress/scripts package](https://developer.wordpress.org/block-editor/reference-guides/packages/packages-scripts/),
and have Webpack configured correctly.

```js
import { PluginDocumentSettingPanel } from '@wordpress/edit-post';
import { registerPlugin } from '@wordpress/plugins';
import { TextControl } from '@wordpress/components';
import { useState } from '@wordpress/element';
import { select, dispatch } from '@wordpress/data';

registerPlugin( 'sample-field', {
	render: () => {
		const [sampleField, setSampleField] = useState( select( 'core/editor' )?.getEditedPostAttribute( 'meta' )?.sampleField ?? '' );

		return (
			<PluginDocumentSettingPanel
				name="sample-field-section"
				title="Sample Field Panel"
			>
				<TextControl
					label="Sample Text Field"
					value={sampleField}
					onChange={( sampleField ) => {
						dispatch( 'core/editor' ).editPost( {
							meta: {
								sampleField
							}
						} )
						setSampleField( sampleField )
					}}
				/>
			</PluginDocumentSettingPanel>
		);
	}
} );
```

### Enqueue The Script File

Once the script has been made, it must be enqueued. The example below assumes that you're getting dependencies and the
version from the compiled PHP file that is created when running the compiler
in [@wordpress/scripts](https://developer.wordpress.org/block-editor/reference-guides/packages/packages-scripts/)

```php
<?php
add_action('admin_enqueue_scripts', function () {
	$asset = require(plugin_dir_path(__FILE__).'build/admin.asset.php');

	// If checks pass, enqueue the script.
	if (isset($asset['dependencies']) && isset($asset['version']) && 'post' === get_post_type()) {
		wp_enqueue_script(
			'sample-inspector-field',
			plugin_dir_url(__FILE__).'build/admin.js',
			$asset['dependencies'],
			$asset['version']
		);
	}
});
```

### Register The Post Meta Field

Finally, it's necessary to register the post meta field. The most important detail in this part is the `show_in_rest`
argument, which ensures that the meta field can be displayed in the block editor.

```php
<?php
add_action('init', function () {
	register_post_meta('post', 'sampleField', [
		'type'         => 'string',
		'description'  => 'A sample meta field registered in the post inspector',
		'single'       => true,
		'default'      => '',
		'show_in_rest' => true,
	]);
});
```