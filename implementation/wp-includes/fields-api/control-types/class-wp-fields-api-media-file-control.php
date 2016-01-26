<?php
/**
 * Fields API Media File Control Class.
 *
 * @see WP_Fields_API_Media_Control
 */
class WP_Fields_API_Media_File_Control extends WP_Fields_API_Media_Control {
	public $type          = 'media_file';
	public $mime_type     = '';
	public $button_labels = array();

	/**
	 * Refresh the parameters passed to the JavaScript via JSON.
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