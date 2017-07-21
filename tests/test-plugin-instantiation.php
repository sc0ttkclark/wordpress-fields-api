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
		$VERSION = '0.1.0';
		$this->assertEquals( WP_FIELDS_API_PLUGIN_VERSION, $VERSION );
	}

	// test that multiple versions (no matter who starts them) defer to newest
	public function test_defers_to_latest_version() {
		// Try including the plugin with differing versions
		$LOW_VERSION = '1.0.0';
		$HIGH_VERSION = '2.0.0';
		WP_Fields_API_v_0_1_0::_debug_force_initialize( $LOW_VERSION );
		WP_Fields_API_v_0_1_0::_debug_force_initialize( $HIGH_VERSION );
		do_action( 'plugins_loaded' );

		// Verify that the newest version loads
		$this->assertEquals( WP_FIELDS_API_PLUGIN_VERSION, $HIGH_VERSION );
	}

	// test that warnings appear when multiple versions are installed
	public function test_warns_about_dependency_hell() {
		// As before, include multiple versions
		$LOW_VERSION = '1.0.0';
		$MIDDLE_VERSION = '1.5.0';
		$HIGH_VERSION = '2.0.0';
		// _wp_fields_api_include( $LOW_VERSION );
		// _wp_fields_api_include( $HIGH_VERSION );
		// _wp_fields_api_include( $MIDDLE_VERSION );
		WP_Fields_API_v_0_1_0::_debug_force_initialize( $LOW_VERSION, 9999 );
		WP_Fields_API_v_0_1_0::_debug_force_initialize( $HIGH_VERSION, 9997 );
		WP_Fields_API_v_0_1_0::_debug_force_initialize( $MIDDLE_VERSION, 9998 );

		// Verify that some sort of warning is emitted.
		ob_start();
		do_action( 'plugins_loaded' );
		do_action( 'admin_notices' );
		$output = ob_get_contents();
		$this->assertRegExp("/A plugin is trying to include an older version\s+\($LOW_VERSION <= $HIGH_VERSION\)\s+of the <strong>WP Fields API<\/strong>./m", $output);
		$this->assertRegExp("/A plugin is trying to include an older version\s+\($MIDDLE_VERSION <= $HIGH_VERSION\)\s+of the <strong>WP Fields API<\/strong>./m", $output);
		ob_end_clean();
	}

	// test disabling builtin form override
	public function test_can_disable_builtin_form_override() {}
}
