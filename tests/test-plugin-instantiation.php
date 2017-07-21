<?php

/**
 * Class WP_Test_Fields_Plugin_Instantiation
 *
 * @uses PHPUnit_Framework_TestCase
 */
class WP_Test_Fields_Plugin_Instantiation extends WP_UnitTestCase {

	// TODO deal with https://make.wordpress.org/plugins/2016/03/01/please-do-not-submit-frameworks/

	// print_r(get_class_methods($this));
	// die;

	// Make sure that when the plugin loads (via `plugins_loaded`) it starts 
	// with the correct version number.
	public function test_begins_with_currect_version() {
		$VERSION = "0.1.0";
		$this->assertEquals(WP_FIELDS_API_PLUGIN_VERSION, $VERSION);
	}

	// test that multiple versions (no matter who starts them) defer to newest
	public function test_defers_to_latest_version() {
		// Try including the plugin with differing versions
		$LOW_VERSION = "1.0.0";
		$HIGH_VERSION = "2.0.0";
		_wp_fields_api_include( $LOW_VERSION );
		_wp_fields_api_include( $HIGH_VERSION );

		$this->assertEquals(WP_FIELDS_API_PLUGIN_VERSION, $HIGH_VERSION);


		// TODO: don't execute hooks if some sort of testing override is defined

		// Verify that the newest version loads
		// TODO: execute hook twice, ensure registered version is highest :D
	}

	// test that warnings appear when multiple versions are installed
	public function test_warns_about_dependency_hell() {
		// As before, include multiple versions

		// Verify that some sort of warning is emitted.
	}
}