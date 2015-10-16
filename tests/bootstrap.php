<?php
/**
 * Bootstrap the plugin unit testing environment.
 *
 * @package WordPress
 * @subpackage Fields API
*/
define( 'WP_TESTS_FORCE_KNOWN_BUGS', true );

// Support for:
// 1. `WP_DEVELOP_DIR` environment variable
// 2. Plugin installed inside of WordPress.org developer checkout
// 3. Tests checked out to /tmp
// 4. VVV tests
if ( false !== getenv( 'WP_DEVELOP_DIR' ) ) {
	$test_root = getenv( 'WP_DEVELOP_DIR' ) . '/tests/phpunit';
} else if ( file_exists( '../../../../tests/phpunit/includes/bootstrap.php' ) ) {
	$test_root = '../../../../tests/phpunit';
} else if ( file_exists( '/tmp/wordpress-tests-lib/includes/bootstrap.php' ) ) {
	$test_root = '/tmp/wordpress-tests-lib';
} else if ( file_exists( '/srv/www/wordpress-develop/tests/phpunit/includes/bootstrap.php' ) ) {
	$test_root = '/srv/www/wordpress-develop/tests/phpunit';
}

require $test_root . '/includes/functions.php';

function _manually_load_plugin() {
	require dirname( __FILE__ ) . '/../wordpress-fields-api.php';
}
tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

require $test_root . '/includes/bootstrap.php';
