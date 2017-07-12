<?php

/**
 * Class WP_Test_Fields_Plugin_Instantiation
 *
 * @uses PHPUnit_Framework_TestCase
 */
class WP_Test_Fields_Plugin_Instantiation extends WP_UnitTestCase {

	// test that WP recognizes this as a plugin
	public function test_is_wp_plugin() {
		// TODO: if registered as a plugin, ensure class loads.
		// This is the default behavior of `bootstrap.php`
	}

	// test that I can include it myself
	public function test_is_composer_plugin() {
		// TODO: if registered using composer, ensure class loads
		// TODO: disable `boostrap.php` behavior for this test
		// TODO: `require` the class file manually
	}

	// test that multiple versions (no matter who starts them) defer to newest
	public function test_defers_to_latest_version() {
		// Try including the plugin with differing versions
		// TODO: detect if included manually or by traditional plugin activation
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