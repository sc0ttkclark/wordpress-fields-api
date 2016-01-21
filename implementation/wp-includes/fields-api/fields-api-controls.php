<?php
/**
 * Fields API Textarea Control class.
 *
 * @see WP_Fields_API_Control
 */
class WP_Fields_API_Textarea_Control extends WP_Fields_API_Control {

	/**
	 * {@inheritdoc}
	 */
	public function render_content() {

		?>
		<label>
			<?php if ( ! empty( $this->label ) ) : ?>
				<span class="fields-control-title"><?php echo esc_html( $this->label ); ?></span>
			<?php endif;
			if ( ! empty( $this->description ) ) : ?>
				<span class="description fields-control-description"><?php echo $this->description; ?></span>
			<?php endif; ?>
			<textarea <?php $this->input_attrs(); ?> <?php $this->link(); ?>><?php echo esc_textarea( $this->value( $this->item_id ) ); ?></textarea>
		</label>
		<?php

	}

	/**
	 * Fetch a checkbox value and allows overriding for separation from field value and 'default' value.
	 *
	 * @return string The requested checkbox value.
	 */
	final public function checkbox_value() {

		$value = $this->value();

		if ( isset( $this->checkbox_value ) ) {
			$value = $this->checkbox_value;
		}

		return $value;

	}

}
/**
 * Fields API Checkbox Control class.
 *
 * @see WP_Fields_API_Control
 */
class WP_Fields_API_Checkbox_Control extends WP_Fields_API_Control {

	/**
	 * Checkbox Value override.
	 *
	 * @access public
	 *
	 * @see WP_Fields_API_Checkbox_Control::checkbox_value()
	 *
	 * @var string Checkbox value, allowing for separation of value, 'default' value, and checkbox value.
	 */
	public $checkbox_value;

	/**
	 * {@inheritdoc}
	 */
	public function render_content() {

			$checkbox_value = $this->checkbox_value();
			$value = $this->value();

			$checked = false;

			if ( '' !== $value && $checkbox_value == $value ) {
				$checked = true;
			}
		?>
		<label>
			<input type="checkbox" value="<?php echo esc_attr( $checkbox_value ); ?>" <?php $this->link(); checked( $checked ); ?> />
			<?php echo esc_html( $this->label ); ?>
			<?php if ( ! empty( $this->description ) ) : ?>
				<span class="description fields-control-description"><?php echo $this->description; ?></span>
			<?php endif; ?>
		</label>
		<?php

	}

	/**
	 * Fetch a checkbox value and allows overriding for separation from field value and 'default' value.
	 *
	 * @return string The requested checkbox value.
	 */
	final public function checkbox_value() {

		$value = $this->value();

		if ( isset( $this->checkbox_value ) ) {
			$value = $this->checkbox_value;
		}

		return $value;

	}

}

/**
 * Fields API Multiple Checkbox Control class.
 *
 * @see WP_Fields_API_Control
 */
class WP_Fields_API_Multi_Checkbox_Control extends WP_Fields_API_Control {

	/**
	 * {@inheritdoc}
	 */
	public function render_content() {

		if ( empty( $this->choices ) ) {
			return;
		}

		$name = '_fields-checkbox-' . $this->id;

		if ( ! empty( $this->label ) ) : ?>
			<span class="fields-control-title"><?php echo esc_html( $this->label ); ?></span>
		<?php endif;
		if ( ! empty( $this->description ) ) : ?>
			<span class="description fields-control-description"><?php echo $this->description ; ?></span>
		<?php endif;

		foreach ( $this->choices as $value => $label ) :
			?>
			<label>
				<input type="checkbox" value="<?php echo esc_attr( $value ); ?>" name="<?php echo esc_attr( $name ); ?>" <?php $this->link(); checked( $this->value(), $value ); ?> />
				<?php echo esc_html( $label ); ?><br/>
			</label>
			<?php
		endforeach;

	}

}

/**
 * Fields API Radio Control class.
 *
 * @see WP_Fields_API_Control
 */
class WP_Fields_API_Radio_Control extends WP_Fields_API_Control {

	/**
	 * {@inheritdoc}
	 */
	public function render_content() {

		if ( empty( $this->choices ) ) {
			return;
		}

		$name = '_fields-radio-' . $this->id;

		if ( ! empty( $this->label ) ) : ?>
			<span class="fields-control-title"><?php echo esc_html( $this->label ); ?></span>
		<?php endif;
		if ( ! empty( $this->description ) ) : ?>
			<span class="description fields-control-description"><?php echo $this->description ; ?></span>
		<?php endif;

		foreach ( $this->choices as $value => $label ) :
			?>
			<label>
				<input type="radio" value="<?php echo esc_attr( $value ); ?>" name="<?php echo esc_attr( $name ); ?>" <?php $this->link(); checked( $this->value(), $value ); ?> />
				<?php echo esc_html( $label ); ?><br/>
			</label>
			<?php
		endforeach;

	}

}

/**
 * Fields API Select Control class.
 *
 * @see WP_Fields_API_Control
 */
class WP_Fields_API_Select_Control extends WP_Fields_API_Control {

	/**
	 * {@inheritdoc}
	 */
	public function render_content() {

		if ( empty( $this->choices ) ) {
			return;
		}
		?>
		<label>
			<?php if ( ! empty( $this->label ) ) : ?>
				<span class="fields-control-title"><?php echo esc_html( $this->label ); ?></span>
			<?php endif;
			if ( ! empty( $this->description ) ) : ?>
				<span class="description fields-control-description"><?php echo $this->description; ?></span>
			<?php endif; ?>

			<select <?php $this->link(); ?>>
				<?php
				foreach ( $this->choices as $value => $label )
					echo '<option value="' . esc_attr( $value ) . '"' . selected( $this->value(), $value, false ) . '>' . $label . '</option>';
				?>
			</select>
		</label>
		<?php

	}

}

/**
 * Fields API Dropdown Pages Control class.
 *
 * @see WP_Fields_API_Control
 */
class WP_Fields_API_Dropdown_Pages_Control extends WP_Fields_API_Select_Control {

	/**
	 * Setup page choices for use by control
	 *
	 * @return array
	 */
	public function choices() {

		$choices = array(
			'0' => __( '&mdash; Select &mdash;' ),
		);

		$pages = get_pages();

		$choices = $this->get_page_choices_recurse( $choices, $pages );

		return $choices;

	}

	/**
	 * Recursively build choices array the full depth
	 *
	 * @param array     $choices List of choices.
	 * @param WP_Post[] $pages   List of pages.
	 * @param int       $depth   Current depth.
	 * @param int       $parent  Current parent page ID.
	 *
	 * @return array
	 */
	public function get_page_choices_recurse( $choices, $pages, $depth = 0, $parent = 0 ) {

		$pad = str_repeat( '&nbsp;', $depth * 3 );

		foreach ( $pages as $page ) {
			if ( $parent == $page->post_parent ) {
				$title = $page->post_title;

				if ( '' === $title ) {
					/* translators: %d: ID of a post */
					$title = sprintf( __( '#%d (no title)' ), $page->ID );
				}

				/**
				 * Filter the page title when creating an HTML drop-down list of pages.
				 *
				 * @since 3.1.0
				 *
				 * @param string $title Page title.
				 * @param object $page  Page data object.
				 */
				$title = apply_filters( 'list_pages', $title, $page );

				$choices[ $page->ID ] = $pad . $title;

				$choices = $this->get_page_choices_recurse( $choices, $pages, $depth + 1, $page->ID );
			}
		}

		return $choices;

	}

}

/**
 * Fields API Color Control class.
 *
 * @see WP_Fields_API_Control
 */
class WP_Fields_API_Color_Control extends WP_Fields_API_Control {

	/**
	 * @access public
	 * @var string
	 */
	public $type = 'color';

	/**
	 * @access public
	 * @var array
	 */
	public $statuses = array();

	/**
	 * Constructor.
	 *
	 * @since 3.4.0
	 * @uses WP_Fields_API_Control::__construct()
	 *
	 * @param WP_Customize_Manager $manager Customizer bootstrap instance.
	 * @param string               $id      Control ID.
	 * @param array                $args    Optional. Arguments to override class property defaults.
	 */
	public function __construct( $manager, $id, $args = array() ) {

		$this->statuses = array(
			'' => __( 'Default' ),
		);

		parent::__construct( $manager, $id, $args );

	}

	/**
	 * Enqueue scripts/styles for the color picker.
	 *
	 * @since 3.4.0
	 */
	public function enqueue() {
		wp_enqueue_script( 'wp-color-picker' );
		wp_enqueue_style( 'wp-color-picker' );
	}

	/**
	 * Refresh the parameters passed to the JavaScript via JSON.
	 *
	 * @uses WP_Fields_API_Control::json()
	 */
	public function json() {

		$json = parent::json();

		$json['statuses']     = $this->statuses;
		$json['defaultValue'] = $this->setting->default;

		return $json;

	}

	/**
	 * Don't render the control content from PHP, as it's rendered via JS on load.
	 */
	public function render_content() {

		// @todo Figure out what to do for render_content vs content_template for purposes of Customizer vs other Fields implementations

	}

	/**
	 * Render a JS template for the content of the color picker control.
	 */
	public function content_template() {

		// @todo Figure out what to do for render_content vs content_template for purposes of Customizer vs other Fields implementations
		?>
		<# var defaultValue = '';
		if ( data.defaultValue ) {
			if ( '#' !== data.defaultValue.substring( 0, 1 ) ) {
				defaultValue = '#' + data.defaultValue;
			} else {
				defaultValue = data.defaultValue;
			}
			defaultValue = ' data-default-color=' + defaultValue; // Quotes added automatically.
		} #>
		<label>
			<# if ( data.label ) { #>
				<span class="customize-control-title">{{{ data.label }}}</span>
			<# } #>
			<# if ( data.description ) { #>
				<span class="description customize-control-description">{{{ data.description }}}</span>
			<# } #>
			<div class="customize-control-content">
				<input class="color-picker-hex" type="text" maxlength="7" placeholder="<?php esc_attr_e( 'Hex Value' ); ?>" {{ defaultValue }} />
			</div>
		</label>
		<?php
	}
}

/**
 * Customize Media Control class.
 *
 * @since 4.2.0
 *
 * @see WP_Fields_API_Control
 */
class WP_Fields_API_Media_Control extends WP_Fields_API_Control {
	/**
	 * Control type.
	 *
	 * @since 4.2.0
	 * @access public
	 * @var string
	 */
	public $type = 'media';

	/**
	 * Media control mime type.
	 *
	 * @since 4.2.0
	 * @access public
	 * @var string
	 */
	public $mime_type = '';

	/**
	 * Button labels.
	 *
	 * @since 4.2.0
	 * @access public
	 * @var array
	 */
	public $button_labels = array();

	/**
	 * Constructor.
	 *
	 * @since 4.1.0
	 * @since 4.2.0 Moved from WP_Fields_API_Upload_Control.
	 *
	 * @param WP_Customize_Manager $manager Customizer bootstrap instance.
	 * @param string               $id      Control ID.
	 * @param array                $args    Optional. Arguments to override class property defaults.
	 */
	public function __construct( $manager, $id, $args = array() ) {
		parent::__construct( $manager, $id, $args );

		$this->button_labels = array(
			'select'       => __( 'Select File' ),
			'change'       => __( 'Change File' ),
			'default'      => __( 'Default' ),
			'remove'       => __( 'Remove' ),
			'placeholder'  => __( 'No file selected' ),
			'frame_title'  => __( 'Select File' ),
			'frame_button' => __( 'Choose File' ),
		);
	}

	/**
	 * Enqueue control related scripts/styles.
	 *
	 * @since 3.4.0
	 * @since 4.2.0 Moved from WP_Fields_API_Upload_Control.
	 */
	public function enqueue() {
		wp_enqueue_media();
	}

	/**
	 * Refresh the parameters passed to the JavaScript via JSON.
	 *
	 * @since 3.4.0
	 * @since 4.2.0 Moved from WP_Fields_API_Upload_Control.
	 *
	 * @see WP_Fields_API_Control::to_json()
	 */
	public function to_json() {
		parent::to_json();
		$this->json['label'] = html_entity_decode( $this->label, ENT_QUOTES, get_bloginfo( 'charset' ) );
		$this->json['mime_type'] = $this->mime_type;
		$this->json['button_labels'] = $this->button_labels;
		$this->json['canUpload'] = current_user_can( 'upload_files' );

		$value = $this->value();

		if ( is_object( $this->setting ) ) {
			if ( $this->setting->default ) {
				// Fake an attachment model - needs all fields used by template.
				// Note that the default value must be a URL, NOT an attachment ID.
				$type = in_array( substr( $this->setting->default, -3 ), array( 'jpg', 'png', 'gif', 'bmp' ) ) ? 'image' : 'document';
				$default_attachment = array(
					'id' => 1,
					'url' => $this->setting->default,
					'type' => $type,
					'icon' => wp_mime_type_icon( $type ),
					'title' => basename( $this->setting->default ),
				);

				if ( 'image' === $type ) {
					$default_attachment['sizes'] = array(
						'full' => array( 'url' => $this->setting->default ),
					);
				}

				$this->json['defaultAttachment'] = $default_attachment;
			}

			if ( $value && $this->setting->default && $value === $this->setting->default ) {
				// Set the default as the attachment.
				$this->json['attachment'] = $this->json['defaultAttachment'];
			} elseif ( $value ) {
				$this->json['attachment'] = wp_prepare_attachment_for_js( $value );
			}
		}
	}

	/**
	 * Don't render any content for this control from PHP.
	 *
	 * @since 3.4.0
	 * @since 4.2.0 Moved from WP_Fields_API_Upload_Control.
	 *
	 * @see WP_Fields_API_Media_Control::content_template()
	 */
	public function render_content() {}

	/**
	 * Render a JS template for the content of the media control.
	 *
	 * @since 4.1.0
	 * @since 4.2.0 Moved from WP_Fields_API_Upload_Control.
	 */
	public function content_template() {
		?>
		<label for="{{ data.settings['default'] }}-button">
			<# if ( data.label ) { #>
				<span class="customize-control-title">{{ data.label }}</span>
			<# } #>
			<# if ( data.description ) { #>
				<span class="description customize-control-description">{{{ data.description }}}</span>
			<# } #>
		</label>

		<# if ( data.attachment && data.attachment.id ) { #>
			<div class="current">
				<div class="container">
					<div class="attachment-media-view attachment-media-view-{{ data.attachment.type }} {{ data.attachment.orientation }}">
						<div class="thumbnail thumbnail-{{ data.attachment.type }}">
							<# if ( 'image' === data.attachment.type && data.attachment.sizes && data.attachment.sizes.medium ) { #>
								<img class="attachment-thumb" src="{{ data.attachment.sizes.medium.url }}" draggable="false" />
							<# } else if ( 'image' === data.attachment.type && data.attachment.sizes && data.attachment.sizes.full ) { #>
								<img class="attachment-thumb" src="{{ data.attachment.sizes.full.url }}" draggable="false" />
							<# } else if ( 'audio' === data.attachment.type ) { #>
								<# if ( data.attachment.image && data.attachment.image.src && data.attachment.image.src !== data.attachment.icon ) { #>
									<img src="{{ data.attachment.image.src }}" class="thumbnail" draggable="false" />
								<# } else { #>
									<img src="{{ data.attachment.icon }}" class="attachment-thumb type-icon" draggable="false" />
								<# } #>
								<p class="attachment-meta attachment-meta-title">&#8220;{{ data.attachment.title }}&#8221;</p>
								<# if ( data.attachment.album || data.attachment.meta.album ) { #>
									<p class="attachment-meta"><em>{{ data.attachment.album || data.attachment.meta.album }}</em></p>
								<# } #>
								<# if ( data.attachment.artist || data.attachment.meta.artist ) { #>
									<p class="attachment-meta">{{ data.attachment.artist || data.attachment.meta.artist }}</p>
								<# } #>
								<audio style="visibility: hidden" controls class="wp-audio-shortcode" width="100%" preload="none">
									<source type="{{ data.attachment.mime }}" src="{{ data.attachment.url }}"/>
								</audio>
							<# } else if ( 'video' === data.attachment.type ) { #>
								<div class="wp-media-wrapper wp-video">
									<video controls="controls" class="wp-video-shortcode" preload="metadata"
										<# if ( data.attachment.image && data.attachment.image.src !== data.attachment.icon ) { #>poster="{{ data.attachment.image.src }}"<# } #>>
										<source type="{{ data.attachment.mime }}" src="{{ data.attachment.url }}"/>
									</video>
								</div>
							<# } else { #>
								<img class="attachment-thumb type-icon icon" src="{{ data.attachment.icon }}" draggable="false" />
								<p class="attachment-title">{{ data.attachment.title }}</p>
							<# } #>
						</div>
					</div>
				</div>
			</div>
			<div class="actions">
				<# if ( data.canUpload ) { #>
					<button type="button" class="button remove-button"><?php echo $this->button_labels['remove']; ?></button>
					<button type="button" class="button upload-button" id="{{ data.settings['default'] }}-button"><?php echo $this->button_labels['change']; ?></button>
					<div style="clear:both"></div>
				<# } #>
			</div>
		<# } else { #>
			<div class="current">
				<div class="container">
					<div class="placeholder">
						<div class="inner">
							<span>
								<?php echo $this->button_labels['placeholder']; ?>
							</span>
						</div>
					</div>
				</div>
			</div>
			<div class="actions">
				<# if ( data.defaultAttachment ) { #>
					<button type="button" class="button default-button"><?php echo $this->button_labels['default']; ?></button>
				<# } #>
				<# if ( data.canUpload ) { #>
					<button type="button" class="button upload-button" id="{{ data.settings['default'] }}-button"><?php echo $this->button_labels['select']; ?></button>
				<# } #>
				<div style="clear:both"></div>
			</div>
		<# } #>
		<?php
	}
}

/**
 * Customize Upload Control Class.
 *
 * @since 3.4.0
 *
 * @see WP_Fields_API_Media_Control
 */
class WP_Fields_API_Upload_Control extends WP_Fields_API_Media_Control {
	public $type          = 'upload';
	public $mime_type     = '';
	public $button_labels = array();
	public $removed = ''; // unused
	public $context; // unused
	public $extensions = array(); // unused

	/**
	 * Refresh the parameters passed to the JavaScript via JSON.
	 *
	 * @since 3.4.0
	 *
	 * @uses WP_Fields_API_Media_Control::to_json()
	 */
	public function to_json() {
		parent::to_json();

		$value = $this->value();
		if ( $value ) {
			// Get the attachment model for the existing file.
			$attachment_id = attachment_url_to_postid( $value );
			if ( $attachment_id ) {
				$this->json['attachment'] = wp_prepare_attachment_for_js( $attachment_id );
			}
		}
	}
}

/**
 * Customize Image Control class.
 *
 * @since 3.4.0
 *
 * @see WP_Fields_API_Upload_Control
 */
class WP_Fields_API_Image_Control extends WP_Fields_API_Upload_Control {
	public $type = 'image';
	public $mime_type = 'image';

	/**
	 * Constructor.
	 *
	 * @since 3.4.0
	 * @uses WP_Fields_API_Upload_Control::__construct()
	 *
	 * @param WP_Customize_Manager $manager Customizer bootstrap instance.
	 * @param string               $id      Control ID.
	 * @param array                $args    Optional. Arguments to override class property defaults.
	 */
	public function __construct( $manager, $id, $args = array() ) {
		parent::__construct( $manager, $id, $args );

		$this->button_labels = array(
			'select'       => __( 'Select Image' ),
			'change'       => __( 'Change Image' ),
			'remove'       => __( 'Remove' ),
			'default'      => __( 'Default' ),
			'placeholder'  => __( 'No image selected' ),
			'frame_title'  => __( 'Select Image' ),
			'frame_button' => __( 'Choose Image' ),
		);
	}

	/**
	 * @since 3.4.2
	 * @deprecated 4.1.0
	 */
	public function prepare_control() {}

	/**
	 * @since 3.4.0
	 * @deprecated 4.1.0
	 *
	 * @param string $id
	 * @param string $label
	 * @param mixed $callback
	 */
	public function add_tab( $id, $label, $callback ) {}

	/**
	 * @since 3.4.0
	 * @deprecated 4.1.0
	 *
	 * @param string $id
	 */
	public function remove_tab( $id ) {}

	/**
	 * @since 3.4.0
	 * @deprecated 4.1.0
	 *
	 * @param string $url
	 * @param string $thumbnail_url
	 */
	public function print_tab_image( $url, $thumbnail_url = null ) {}
}