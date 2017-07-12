<?php

/**
 * Class WP_Test_Fields_Plugin_Instantiation
 *
 * @uses PHPUnit_Framework_TestCase
 */
class WP_Test_Fields_Plugin_Instantiation extends WP_UnitTestCase {

	// test that WP recognizes this as a plugin
	public function test_is_wp_plugin() {}

	// test that I can include it myself
	public function test_is_composer_plugin() {}

	// test that multiple versions (no matter who starts them) defer to newest
	public function test_defers_to_latest_version() {}

	// test that warnings appear when multiple versions are installed
	public function test_warns_about_dependency_hell() {}
}