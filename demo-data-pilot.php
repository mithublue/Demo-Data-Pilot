<?php
/**
 * Plugin Name: Demo Data Pilot
 * Plugin URI: https://yourwebsite.com/demo-data-pilot
 * Description: A modular, extensible WordPress plugin for generating realistic dummy/demo data for various WordPress plugins like WooCommerce, WP ERP, and more.
 * Version: 1.0.1
 * Author: Your Name
 * Author URI: https://yourwebsite.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: demo-data-pilot
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 *
 * @package DemoDataPilot
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Current plugin version.
 */
define( 'DEMO_DATA_PILOT_VERSION', '1.0.1' );
define( 'DEMO_DATA_PILOT_PLUGIN_FILE', __FILE__ );
define( 'DEMO_DATA_PILOT_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'DEMO_DATA_PILOT_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'DEMO_DATA_PILOT_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Autoloader for plugin classes.
 *
 * @param string $class_name The class name.
 */
function demo_data_pilot_autoloader( $class_name ) {
	// Only autoload classes in our namespace.
	if ( strpos( $class_name, 'DemoDataPilot' ) !== 0 ) {
		return;
	}

	// Convert namespace to file path.
	$class_name = str_replace( 'DemoDataPilot\\', '', $class_name );
	$class_name = str_replace( '\\', DIRECTORY_SEPARATOR, $class_name );
	
	// Convert class name format.
	$class_parts = explode( DIRECTORY_SEPARATOR, $class_name );
	$class_file  = 'class-' . strtolower( str_replace( '_', '-', array_pop( $class_parts ) ) ) . '.php';
	
	// Determine base directory.
	$base_dir = DEMO_DATA_PILOT_PLUGIN_DIR;
	
	// Check if it's a generator class.
	if ( ! empty( $class_parts ) && strtolower( $class_parts[0] ) === 'generators' ) {
		// Remove 'Generators' from path and use generators/ directory.
		array_shift( $class_parts );
		$base_dir .= 'generators' . DIRECTORY_SEPARATOR;
	} elseif ( ! empty( $class_parts ) && strtolower( $class_parts[0] ) === 'admin' ) {
		// Remove 'Admin' from path and use admin/ directory.
		array_shift( $class_parts );
		$base_dir .= 'admin' . DIRECTORY_SEPARATOR;
	} else {
		// Default to includes/ directory.
		$base_dir .= 'includes' . DIRECTORY_SEPARATOR;
	}
	
	// Add remaining subdirectories.
	if ( ! empty( $class_parts ) ) {
		$base_dir .= strtolower( implode( DIRECTORY_SEPARATOR, $class_parts ) ) . DIRECTORY_SEPARATOR;
	}
	
	$file_path = $base_dir . $class_file;

	// Load the file if it exists.
	if ( file_exists( $file_path ) ) {
		require_once $file_path;
	}
}
spl_autoload_register( 'demo_data_pilot_autoloader' );

/**
 * Load Composer dependencies.
 */
if ( file_exists( DEMO_DATA_PILOT_PLUGIN_DIR . 'vendor/autoload.php' ) ) {
	require_once DEMO_DATA_PILOT_PLUGIN_DIR . 'vendor/autoload.php';
}

/**
 * The code that runs during plugin activation.
 */
function activate_demo_data_pilot() {
	require_once DEMO_DATA_PILOT_PLUGIN_DIR . 'includes/class-activator.php';
	DemoDataPilot\Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_demo_data_pilot() {
	require_once DEMO_DATA_PILOT_PLUGIN_DIR . 'includes/class-deactivator.php';
	DemoDataPilot\Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_demo_data_pilot' );
register_deactivation_hook( __FILE__, 'deactivate_demo_data_pilot' );

/**
 * Begin execution of the plugin.
 *
 * @since 1.0.0
 */
function run_demo_data_pilot() {
	require_once DEMO_DATA_PILOT_PLUGIN_DIR . 'includes/class-demo-data-pilot.php';
	$plugin = new DemoDataPilot\Demo_Data_Pilot();
	$plugin->run();
}

// Run the plugin.
run_demo_data_pilot();
