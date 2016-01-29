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
class WP_Fields_API_Form_Settings extends WP_Fields_API_Table_Form {

	/**
	 * {@inheritdoc}
	 */
	public function render() {

		// Add Settings API hidden form fields and nonce
		settings_fields( $this->id );

		parent::render();

	}

}