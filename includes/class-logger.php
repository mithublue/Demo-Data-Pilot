<?php
/**
 * Logger class for tracking generation activity
 *
 * @package DemoDataPilot
 */

namespace DemoDataPilot;

/**
 * Logger class.
 *
 * Handles logging of generation activities and errors.
 */
class Logger {

	/**
	 * Log levels.
	 */
	const LEVEL_INFO    = 'info';
	const LEVEL_WARNING = 'warning';
	const LEVEL_ERROR   = 'error';
	const LEVEL_SUCCESS = 'success';

	/**
	 * Whether logging is enabled.
	 *
	 * @var bool
	 */
	private $enabled;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->enabled = get_option( 'demo_data_pilot_enable_logging', true );
	}

	/**
	 * Log a message.
	 *
	 * @param string $message   Log message.
	 * @param string $level     Log level (info, warning, error, success).
	 * @param string $generator Optional. Generator slug.
	 */
	public function log( $message, $level = self::LEVEL_INFO, $generator = '' ) {
		if ( ! $this->enabled ) {
			return;
		}

		$log_entry = array(
			'timestamp' => current_time( 'mysql' ),
			'level'     => $level,
			'message'   => $message,
			'generator' => $generator,
		);

		// Get existing logs.
		$logs = $this->get_logs();

		// Add new entry.
		array_unshift( $logs, $log_entry );

		// Keep only last 100 entries.
		$logs = array_slice( $logs, 0, 100 );

		// Save logs.
		update_option( 'demo_data_pilot_logs', $logs );

		// Also log to WordPress debug log if enabled.
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
			$log_message = sprintf(
				'[Demo Data Pilot] [%s] %s %s',
				strtoupper( $level ),
				$generator ? "[$generator]" : '',
				$message
			);
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log( $log_message );
		}
	}

	/**
	 * Get all logs.
	 *
	 * @param int $limit Optional. Number of logs to retrieve.
	 * @return array Array of log entries.
	 */
	public function get_logs( $limit = 100 ) {
		$logs = get_option( 'demo_data_pilot_logs', array() );

		if ( $limit > 0 ) {
			$logs = array_slice( $logs, 0, $limit );
		}

		return $logs;
	}

	/**
	 * Clear all logs.
	 *
	 * @return bool True on success, false on failure.
	 */
	public function clear_logs() {
		return delete_option( 'demo_data_pilot_logs' );
	}

	/**
	 * Get logs by level.
	 *
	 * @param string $level Log level to filter by.
	 * @return array Filtered log entries.
	 */
	public function get_logs_by_level( $level ) {
		$logs = $this->get_logs();

		return array_filter(
			$logs,
			function( $log ) use ( $level ) {
				return $log['level'] === $level;
			}
		);
	}

	/**
	 * Get logs by generator.
	 *
	 * @param string $generator Generator slug.
	 * @return array Filtered log entries.
	 */
	public function get_logs_by_generator( $generator ) {
		$logs = $this->get_logs();

		return array_filter(
			$logs,
			function( $log ) use ( $generator ) {
				return $log['generator'] === $generator;
			}
		);
	}

	/**
	 * Enable logging.
	 */
	public function enable() {
		$this->enabled = true;
		update_option( 'demo_data_pilot_enable_logging', true );
	}

	/**
	 * Disable logging.
	 */
	public function disable() {
		$this->enabled = false;
		update_option( 'demo_data_pilot_enable_logging', false );
	}

	/**
	 * Check if logging is enabled.
	 *
	 * @return bool True if enabled, false otherwise.
	 */
	public function is_enabled() {
		return $this->enabled;
	}
}
