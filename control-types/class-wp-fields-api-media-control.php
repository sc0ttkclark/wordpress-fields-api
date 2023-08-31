<?php
/**
 * @package    WordPress
 * @subpackage Fields_API
 */

/**
 * Fields API Media Control class.
 *
 * @see WP_Fields_API_Control
 */
class WP_Fields_API_Media_Control extends WP_Fields_API_Control {

	/**
	 * {@inheritdoc}
	 */
	public $type = 'media';

	/**
	 * Media control mime type.
	 *
	 * @access public
	 * @var string
	 */
	public $mime_type = '';

	/**
	 * Button labels.
	 *
	 * @access public
	 * @var array
	 */
	public $button_labels = array();

	/**
	 * {@inheritdoc}
	 */
	public function init( $object_type, $id, $args = array() ) {

		parent::init( $object_type, $id, $args );

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
	 * {@inheritdoc}
	 */
	public function enqueue() {

		wp_enqueue_media();

	}

	/**
	 * Refresh the parameters passed to the JavaScript via JSON.
	 *
	 * @see WP_Fields_API_Control::to_json()
	 */
	public function json() {

		$json = parent::json();

		$json['mime_type']     = $this->mime_type;
		$json['button_labels'] = $this->button_labels;
		$json['canUpload']     = current_user_can( 'upload_files' );

		$value = $this->value();

		$field = $this->field;

		if ( $field ) {
			if ( $field->default ) {
				// Fake an attachment model - needs all fields used by template.
				// Note that the default value must be a URL, NOT an attachment ID.
				$type = 'document';

				if ( in_array( substr( $field->default, - 3 ), array( 'jpg', 'png', 'gif', 'bmp' ) ) ) {
					$type = 'image';
				}

				$default_attachment = array(
					'id'    => 1,
					'url'   => $field->default,
					'type'  => $type,
					'icon'  => wp_mime_type_icon( $type ),
					'title' => basename( $field->default ),
				);

				if ( 'image' === $type ) {
					$default_attachment['sizes'] = array(
						'full' => array(
							'url' => $field->default,
						),
					);
				}

				$json['defaultAttachment'] = $default_attachment;
			}

			if ( $value && $field->default && $value === $field->default ) {
				// Set the default as the attachment.
				$json['attachment'] = $json['defaultAttachment'];
			} elseif ( $value ) {
				$json['attachment'] = wp_prepare_attachment_for_js( $value );
			}
		}

		return $json;

	}

	/**
	 * Don't render any content for this control from PHP.
	 *
	 * @see WP_Fields_API_Media_Control::content_template()
	 */
	protected function render_content() {

		// @todo Figure out render for non-JS forms

	}

	/**
	 * Render a JS template for the content of the media control.
	 */
	public function content_template() {
		?>
        <label for="{{ data.settings['default'] }}-button">
            <# if ( data.label ) { #>
            <span class="fields-control-title">{{ data.label }}</span>
            <# } #>
            <# if ( data.description ) { #>
            <span class="description fields-control-description">{{{ data.description }}}</span>
            <# } #>
        </label>

        <# if ( data.attachment && data.attachment.id ) { #>
        <div class="current">
            <div class="container">
                <div class="attachment-media-view attachment-media-view-{{ data.attachment.type }} {{ data.attachment.orientation }}">
                    <div class="thumbnail thumbnail-{{ data.attachment.type }}">
                        <# if ( 'image' === data.attachment.type && data.attachment.sizes &&
                        data.attachment.sizes.medium ) { #>
                        <img class="attachment-thumb" src="{{ data.attachment.sizes.medium.url }}" draggable="false"/>
                        <# } else if ( 'image' === data.attachment.type && data.attachment.sizes &&
                        data.attachment.sizes.full ) { #>
                        <img class="attachment-thumb" src="{{ data.attachment.sizes.full.url }}" draggable="false"/>
                        <# } else if ( 'audio' === data.attachment.type ) { #>
                        <# if ( data.attachment.image && data.attachment.image.src && data.attachment.image.src !==
                        data.attachment.icon ) { #>
                        <img src="{{ data.attachment.image.src }}" class="thumbnail" draggable="false"/>
                        <# } else { #>
                        <img src="{{ data.attachment.icon }}" class="attachment-thumb type-icon" draggable="false"/>
                        <# } #>
                        <p class="attachment-meta attachment-meta-title">&#8220;{{ data.attachment.title }}&#8221;</p>
                        <# if ( data.attachment.album || data.attachment.meta.album ) { #>
                        <p class="attachment-meta"><em>{{ data.attachment.album || data.attachment.meta.album }}</em>
                        </p>
                        <# } #>
                        <# if ( data.attachment.artist || data.attachment.meta.artist ) { #>
                        <p class="attachment-meta">{{ data.attachment.artist || data.attachment.meta.artist }}</p>
                        <# } #>
                        <audio style="visibility: hidden" controls class="wp-audio-shortcode" width="100%"
                               preload="none">
                            <source type="{{ data.attachment.mime }}" src="{{ data.attachment.url }}"/>
                        </audio>
                        <# } else if ( 'video' === data.attachment.type ) { #>
                        <div class="wp-media-wrapper wp-video">
                            <video controls="controls" class="wp-video-shortcode" preload="metadata"
                            <# if ( data.attachment.image && data.attachment.image.src !== data.attachment.icon ) {
                            #>poster="{{ data.attachment.image.src }}"<# } #>>
                            <source type="{{ data.attachment.mime }}" src="{{ data.attachment.url }}"/>
                            </video>
                        </div>
                        <# } else { #>
                        <img class="attachment-thumb type-icon icon" src="{{ data.attachment.icon }}"
                             draggable="false"/>
                        <p class="attachment-title">{{ data.attachment.title }}</p>
                        <# } #>
                    </div>
                </div>
            </div>
        </div>
        <div class="actions">
            <# if ( data.canUpload ) { #>
            <button type="button" class="button remove-button"><?php echo $this->button_labels['remove']; ?></button>
            <button type="button" class="button upload-button"
                    id="{{ data.settings['default'] }}-button"><?php echo $this->button_labels['change']; ?></button>
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
            <button type="button" class="button upload-button"
                    id="{{ data.settings['default'] }}-button"><?php echo $this->button_labels['select']; ?></button>
            <# } #>
            <div style="clear:both"></div>
        </div>
        <# } #>
		<?php
	}
}