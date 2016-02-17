<?php
/**
 * This is an implementation for Fields API for the Settings forms in the WordPress Dashboard
 *
 * @package    WordPress
 * @subpackage Fields_API
 */

/**
 * Class WP_Fields_API_Form_Settings
 */
class WP_Fields_API_Form_Settings extends WP_Fields_API_Form {

	/**
	 * {@inheritdoc}
	 */
	public function render() {

		// Get Settings Page ID
		$setting_page_id = $this->id;

		// Remove our namespace
		$setting_page_id = str_replace( 'settings-', '', $setting_page_id );

		// Add Settings API hidden form fields and nonce
		settings_fields( $setting_page_id );

		// Render Settings API fields
		do_settings_sections( $setting_page_id );

		// Enqueue footer scripts
		$this->enqueue_footer_scripts();

	}

}