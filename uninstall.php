<?php
/**
 * Fired when the plugin is uninstalled
 *
 * @package DemoDataPilot
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

/**
 * Delete plugin data on uninstall.
 */
function demo_data_pilot_uninstall() {
	global $wpdb;

	// Delete options.
	$options = array(
		'demo_data_pilot_batch_size',
		'demo_data_pilot_enable_logging',
		'demo_data_pilot_auto_cleanup',
		'demo_data_pilot_cleanup_days',
		'demo_data_pilot_enabled_generators',
		'demo_data_pilot_db_version',
	);

	foreach ( $options as $option ) {
		delete_option( $option );
	}

	// Drop custom table.
	$table_name = $wpdb->prefix . 'ddp_generated_records';
	$wpdb->query( "DROP TABLE IF EXISTS $table_name" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

	// Clear any cached data.
	wp_cache_flush();
}

demo_data_pilot_uninstall();
