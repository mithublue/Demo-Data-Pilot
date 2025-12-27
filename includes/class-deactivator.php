<?php
/**
 * Fired during plugin deactivation
 *
 * @package DemoDataPilot
 */

namespace DemoDataPilot;

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 */
class Deactivator {

	/**
	 * Deactivate the plugin.
	 *
	 * Clean up scheduled events and temporary data.
	 *
	 * @since 1.0.0
	 */
	public static function deactivate() {
		// Clear scheduled cron events if any.
		wp_clear_scheduled_hook( 'demo_data_pilot_cleanup' );
		
		// Flush rewrite rules.
		flush_rewrite_rules();
	}
}
