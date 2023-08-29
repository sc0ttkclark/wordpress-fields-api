<?php
/*
 * Plugin Name:       WordPress Fields API
 * Description:       Demo of a PoC for the WordPress fields API
 * Version:           0.1.0
 * Requires at least: 6.3
 * Requires PHP:      7.0
 * Author:            Alex Standiford
 * Author URI:        https://alexstandiford.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 */

$path = plugin_dir_path( __FILE__ );
require_once( $path . 'abstracts/class-field-control.php' );
require_once( $path . 'abstracts/class-field-data-store.php' );
require_once( $path . 'abstracts/class-registrable-field-data-store.php' );
require_once( $path . 'datastores/class-option.php' );
require_once( $path . 'controls/class-settings-page-text-input-control.php' );
require_once( $path . 'class-fields-registry.php' );

Fields_Registry::get_instance()->register_datastore_type( 'option', Option::class );
Fields_Registry::get_instance()->register_control_type( 'settings_page_text_input', Settings_Page_Text_Input_Control::class );
Fields_Registry::get_instance()->register_json( plugin_dir_path( __FILE__ ) . 'fields.json' );