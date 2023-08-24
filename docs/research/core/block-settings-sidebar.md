# Custom Block

Here we demonstrate 2 different use-cases for custom fields in custom blocks:

1. Standard custom block with custom attributes
2. Advanced custom block that interacts with post meta

## Custom Block with Attributes

Creating a custom block requires:

* Set up a custom plugin
* Register a block
* Scaffold the block
* Define block attributes
* Create the block editor script and custom input fields
* Render the block on the front-end

In this example, our custom fields are configured via the block's `attributes`, which are saved in the block's markup when the post is saved. Attribute values are, for the most part, accessible only by the block that defines them.

### Set up a custom plugin

For the sake of brevity, we have our [local environment set up for block development](https://developer.wordpress.org/block-editor/getting-started/devenv/), and we are setting up the plugin with `@wordpress/create-block`:

```bash
npx @wordpress/create-block fields-test-custom-block
```

### Register a Block

By default, the `create-block` script does block registration in the main plugin file, `fields-test-custom-block.php`:

```php
function create_block_fields_test_custom_block_block_init() {
	register_block_type( __DIR__ . '/build' );
}
add_action( 'init', 'create_block_fields_test_custom_block_block_init' );
```

### Scaffold the Block

The `create-block` script also creates a `src` directory with the block scaffolded for you.

```
src
├── block.json
├── edit.js
├── editor.scss
├── index.js
├── save.js
└── style.scss
```

The `index.js` file is the entry point for the block editor script. This is where we register the block in JavaScript.

**index.js**:
```js
import { registerBlockType } from '@wordpress/blocks';

import './style.scss';

import edit from './edit';
import save from './save';
import metadata from './block.json';

registerBlockType( metadata.name, {
    /**
     * @see ./edit.js
     */
    edit,
    /**
     * @see ./save.js
     */
    save,
} );
```

### Define Block Attributes

The `block.json` file is used to describe the block and its [attributes](https://developer.wordpress.org/block-editor/getting-started/create-block/attributes/), which are the custom fields that will be used in the block editor. For this example, the attribute value will be handled manually.

**block.json**:
```json
{
	"$schema": "https://schemas.wp.org/trunk/block.json",
	"apiVersion": 3,
	"name": "fields-test/custom-block",
	"version": "0.1.0",
	"title": "Fields Test Custom Block",
	"category": "widgets",
	"icon": "smiley",
	"attributes": {
		"content": {
			"type": "string",
			"default": "Default Subtitle"
		}
	},
	"editorScript": "file:./index.js",
	"editorStyle": "file:./index.css",
	"style": "file:./style-index.css"
}
```

### Create the Block Editor Script and Custom Input Fields

#### Example 1: Using the RichText Component to Render an Editable Field in the Block

The `edit.js` file is where we define the block editor script and import components from WordPress packages or build them ourselves. In this example, we are using the `RichText` component from `@wordpress/block-editor` to render an editable text field directly inside the block.

**edit.js**:
```js
import { useBlockProps, RichText } from '@wordpress/block-editor';

export default function Edit( { attributes, setAttributes } ) {
	return (
		<RichText
			{ ...useBlockProps() }
			tagName="p" // The tag here is the element output
			value={ attributes.content } // Our custom field content
			allowedFormats={ [ 'core/bold', 'core/italic' ] } // Allow the content to be made bold or italic, but do not allow other formatting options
			onChange={ ( content ) => setAttributes( { content } ) } // Store updated content in our custom block attribute
			placeholder={ __( 'Subtitle...' ) } // Display this text before any content has been added by the user
		/>
	);
}
```

#### Example 2: Using the TextControl Component to Render an Input Field in the Block Settings Sidebar

If instead we want to provide an input field in the block settings sidebar, we can use the `InspectorControls` component from `@wordpress/block-editor` to render a custom field in the block sidebar. In this example, we are using the `TextControl` component from `@wordpress/components` to render the input field, and `PanelBody` to group our custom fields together.

In this example, the field is only editable in the block settings sidebar, and there are no rich text formatting options.

**edit.js**:
```js
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { TextControl, PanelBody } from '@wordpress/components';

export default function Edit( { attributes, setAttributes } ) {
	return (
		<>
			<InspectorControls key="settings">
				<PanelBody title="Fields Test">
					<TextControl
						value={ attributes.content }
						onChange={ ( content ) => setAttributes( { content } ) }
						placeholder={ __( 'Subtitle...' ) }
					/>
				</PanelBody>
			</InspectorControls>
			<p { ...useBlockProps() }>
				{ attributes.content }
			</p>
		</>
	);
}
```

### Render the Block on the Front-End

To render a block on the front-end with custom block attributes, we are defining a "static block" that is saved as HTML in the post content on save. We need to render the block component with the attributes output as it should be rendered on the front-end.

#### Example 1: Outputting the RichText Value

In this example, we output the `RichText` field content:

**save.js**:
```js
import { useBlockProps, RichText } from '@wordpress/block-editor';

export default function Save( { attributes } ) {
	return (
		<RichText.Content
			{ ...useBlockProps.save() }
			tagName="p"
			value={ attributes.content }
		/>
	);
}
```

#### Example 2: Outputting the TextControl Value

To render the second example using the `TextControl` component, we need to output the `attributes.content` value directly:

**save.js**:
```js
import { useBlockProps } from '@wordpress/block-editor';

export default function Save( { attributes } ) {
	return (
		<p { ...useBlockProps.save() }>
			{ attributes.content }
		</p>
	);
}
```

## Custom Block that Interacts with Post Meta

Creating a custom block that renders & updates a post meta field is a lot of work. It requires everything from the previous example, plus:

* Registering a post meta field
* Format the block as a dynamic block
* Handle reading and writing the post meta field in the editor
* Render the block on the front-end with PHP

### Registering Post Meta

When registering post meta, you need to specify `'show_in_rest' => true` to interact with the post meta field in the block editor.

```php
function fields_test_custom_block_register_meta() {
	register_post_meta(
		'post',
		'subtitle',
		array(
			'show_in_rest' => true,
			'single'       => true,
			'type'         => 'string',
		)
	);
}
add_action('init', 'fields_test_custom_block_register_meta');
```

### Format the Block as a Dynamic Block

When displaying post meta, the blook needs to be a "dynamic block" that is rendered with PHP. We need to render the block and attributes output as HTML.

To alter our examples above to work as a dynamic block, we need to change 3 things:
1. Remove the `save` function from `index.js` and remove the `save.js` file.
2. Add the `render` and `usesContext` properties to the block registration in `block.json`.
3. Add the render.php file.

Our `index.js` file now looks like this, with `save` removed:

**index.js**:
```js
import { registerBlockType } from '@wordpress/blocks';

import './style.scss';

import edit from './edit';
import metadata from './block.json';

registerBlockType( metadata.name, {
	/**
	 * @see ./edit.js
	 */
	edit,
} );
```

Our `block.json` file now looks like this, with `attributes` removed and `render` + `usesContext` added:

**block.json**:
```json
{
	"$schema": "https://schemas.wp.org/trunk/block.json",
	"apiVersion": 3,
	"name": "fields-test/custom-block",
	"version": "0.1.0",
	"title": "Fields Test Custom Block",
	"category": "widgets",
	"icon": "smiley",
	"usesContext": [ "postId", "postType" ],
	"editorScript": "file:./index.js",
	"editorStyle": "file:./index.css",
	"style": "file:./style-index.css",
	"render": "file:./render.php"
}
```

We need to consume the `postId` and `postType` context to interact with the post meta value in the editor as well as when rendering on the front end.

Lastly, we add the `render.php` file, which is detailed below.

### Handle Reading and Writing the Post Meta Field in the Editor

Instead of interacting with block attributes, we need to consume and update post meta values in the `edit` function. We can use the `useContext` hook to consume the `postId` context, and the `useEntityProp` hook to interact with the post meta value. Here we're just using the `TextControl` component to render the input field in the `InspectorControls`, but it could also be added to the block with the `RichText` component.

Note that we don't read and write directly to a single post meta value, but rather an object containing all post meta values for the post type when using `useEntityProp`. When updating the value, we need to pass the entire object back to the `setMeta` function.

**edit.js**:
```js
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { TextControl, PanelBody } from '@wordpress/components';
import { useContext } from '@wordpress/element';

export default function Edit( { attributes, setAttributes, context } ) {
	const { postId, postType } = useContext( 'postId' );
	const [ meta, setMeta ] = useEntityProp( 'postType', postType, 'meta' );

	const { subtitle } = meta;

	return (
		<>
			<InspectorControls key="settings">
				<PanelBody title="Fields Test">
					<TextControl
						value={ subtitle }
						onChange={ ( content ) => setMeta( { ...meta, subtitle: content } ) } // Pass the entire object back to setMeta with object destructuring and the new value added
						placeholder={ __( 'Subtitle...' ) }
					/>
				</PanelBody>
			</InspectorControls>
			<p { ...useBlockProps() }>
				{ subtitle }
			</p>
		</>
	);
}
```

### Render the Block on the Front-End with PHP

Now we can create the `render.php` file to render the block that outputs post meta on the front-end:

**render.php**:
```php
<?php
/**
 * Block: fields-test/custom-block, render.
 *
 * Global vars: $attributes, $content, $block.
 *
 * @package fields-test
 */

$subtitle = get_post_meta( $block->context['postId'], 'subtitle', true );
?>
<p <?php echo get_block_wrapper_attributes(); ?>>
	<?php echo esc_html( $subtitle ); ?>
</p>
```

## Lessons to be learned from the Custom Block approach

The core approach to custom blocks with attributes or post meta provides little in the way of field content validation other than defining the format of the field value and output escaping in PHP. The `RichText` component allows for rich text formatting, but there is no standard way to customize the validation of the content of the field. The `TextControl` component is a simple text entry field, and validation would be left up to the developer. Various other field components are available in the `@wordpress/components` package.

It does provide an example of JSON usage for configuration that could be an example for or even extended by the Fields API to provide a standard way to define fields and field groups. Potentially, defining these fields could handle input field creation, validation, and output escaping automatically.

| Registration Type             | Supports JSON Files                | Supports Multiple Content Types | Form / HTML markup required |
|-------------------------------|------------------------------------|---------------------------------|-----------------------------|
| PHP function calls & JS/React | Yes (for block & attribute config) | Yes (with custom code)          | Yes (JS/React)              |
