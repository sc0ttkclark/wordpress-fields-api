<?php
/**
 * @package    WordPress
 * @subpackage Fields_API
 */

/**
 * Fields API Media File Control Class.
 *
 * @see WP_Fields_API_Media_Control
 */
class WP_Fields_API_Media_File_Control extends WP_Fields_API_Media_Control {

	/**
	 * {@inheritdoc}
	 */
	public $type = 'media-file';

	/**
	 * {@inheritdoc}
	 */
	public function json() {

		$json = parent::json();

		$value = $this->value();

		if ( $value ) {
			// Get the attachment model for the existing file.
			$attachment_id = attachment_url_to_postid( $value );

			if ( $attachment_id ) {
				$json['attachment'] = wp_prepare_attachment_for_js( $attachment_id );
			}
		}

		return $json;

	}

}