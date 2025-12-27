<?php
/**
 * Fired during plugin activation
 *
 * @package DemoDataPilot
 */

namespace DemoDataPilot;

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 */
class Activator {

	/**
	 * Activate the plugin.
	 *
	 * Creates database tables and sets default options.
	 *
	 * @since 1.0.0
	 */
	public static function activate() {
		self::create_tables();
		self::set_default_options();
		
		// Flush rewrite rules.
		flush_rewrite_rules();
	}

	/**
	 * Create plugin database tables.
	 *
	 * @since 1.0.0
	 */
	private static function create_tables() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();
		$table_name      = $wpdb->prefix . 'ddp_generated_records';

		$sql = "CREATE TABLE IF NOT EXISTS $table_name (
			id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
			generator VARCHAR(100) NOT NULL,
			data_type VARCHAR(100) NOT NULL,
			record_id BIGINT NOT NULL,
			metadata TEXT,
			created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
			INDEX idx_generator_type (generator, data_type),
			INDEX idx_record (record_id),
			INDEX idx_created_at (created_at)
		) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );

		// Store database version.
		update_option( 'demo_data_pilot_db_version', '1.0.0' );
	}

	/**
	 * Set default plugin options.
	 *
	 * @since 1.0.0
	 */
	private static function set_default_options() {
		$defaults = array(
			'batch_size'          => 50,
			'enable_logging'      => true,
			'auto_cleanup'        => false,
			'cleanup_days'        => 30,
			'enabled_generators'  => array(),
		);

		foreach ( $defaults as $key => $value ) {
			$option_name = 'demo_data_pilot_' . $key;
			if ( false === get_option( $option_name ) ) {
				add_option( $option_name, $value );
			}
		}
	}
}
