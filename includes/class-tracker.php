<?php
/**
 * Tracker class for managing generated records
 *
 * @package DemoDataPilot
 */

namespace DemoDataPilot;

/**
 * Tracker class.
 *
 * Tracks all generated records in the database for cleanup purposes.
 */
class Tracker {

	/**
	 * Table name for tracking records.
	 *
	 * @var string
	 */
	private $table_name;

	/**
	 * Constructor.
	 */
	public function __construct() {
		global $wpdb;
		$this->table_name = $wpdb->prefix . 'ddp_generated_records';
	}

	/**
	 * Track a generated record.
	 *
	 * @param string $generator Generator slug.
	 * @param string $type      Data type.
	 * @param int    $record_id Record ID.
	 * @param array  $metadata  Optional metadata.
	 * @return int|false Insert ID on success, false on failure.
	 */
	public function track( $generator, $type, $record_id, $metadata = array() ) {
		global $wpdb;

		$data = array(
			'generator'  => sanitize_text_field( $generator ),
			'data_type'  => sanitize_text_field( $type ),
			'record_id'  => absint( $record_id ),
			'metadata'   => wp_json_encode( $metadata ),
			'created_at' => current_time( 'mysql' ),
		);

		$result = $wpdb->insert(
			$this->table_name,
			$data,
			array( '%s', '%s', '%d', '%s', '%s' )
		);

		return $result ? $wpdb->insert_id : false;
	}

	/**
	 * Get tracked records.
	 *
	 * @param string $generator Generator slug.
	 * @param string $type      Optional. Data type filter.
	 * @return array Array of tracked records.
	 */
	public function get_tracked( $generator, $type = null ) {
		global $wpdb;

		$where = $wpdb->prepare( 'WHERE generator = %s', $generator );

		if ( $type ) {
			$where .= $wpdb->prepare( ' AND data_type = %s', $type );
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$results = $wpdb->get_results( "SELECT * FROM {$this->table_name} {$where} ORDER BY created_at DESC" );

		return $results;
	}

	/**
	 * Get tracked record IDs only.
	 *
	 * @param string $generator Generator slug.
	 * @param string $type      Optional. Data type filter.
	 * @return array Array of record IDs.
	 */
	public function get_tracked_ids( $generator, $type = null ) {
		global $wpdb;

		$where = $wpdb->prepare( 'WHERE generator = %s', $generator );

		if ( $type ) {
			$where .= $wpdb->prepare( ' AND data_type = %s', $type );
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$results = $wpdb->get_col( "SELECT record_id FROM {$this->table_name} {$where}" );

		return array_map( 'absint', $results );
	}

	/**
	 * Remove tracking records.
	 *
	 * @param array $ids Tracking record IDs to remove.
	 * @return int|false Number of rows deleted, false on failure.
	 */
	public function remove_tracking( $ids ) {
		global $wpdb;

		if ( empty( $ids ) ) {
			return false;
		}

		$ids          = array_map( 'absint', $ids );
		$placeholders = implode( ',', array_fill( 0, count( $ids ), '%d' ) );

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return $wpdb->query( $wpdb->prepare( "DELETE FROM {$this->table_name} WHERE id IN ($placeholders)", $ids ) );
	}

	/**
	 * Remove all tracking records for a generator/type.
	 *
	 * @param string $generator Generator slug.
	 * @param string $type      Optional. Data type filter.
	 * @return int|false Number of rows deleted, false on failure.
	 */
	public function cleanup_all( $generator, $type = null ) {
		global $wpdb;

		$where = $wpdb->prepare( 'WHERE generator = %s', $generator );

		if ( $type ) {
			$where .= $wpdb->prepare( ' AND data_type = %s', $type );
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return $wpdb->query( "DELETE FROM {$this->table_name} {$where}" );
	}

	/**
	 * Get statistics for tracked records.
	 *
	 * @param string $generator Optional. Generator slug filter.
	 * @return array Statistics array.
	 */
	public function get_stats( $generator = null ) {
		global $wpdb;

		$where = $generator ? $wpdb->prepare( 'WHERE generator = %s', $generator ) : '';

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$total = $wpdb->get_var( "SELECT COUNT(*) FROM {$this->table_name} {$where}" );

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$by_type = $wpdb->get_results( "SELECT data_type, COUNT(*) as count FROM {$this->table_name} {$where} GROUP BY data_type" );

		return array(
			'total'   => absint( $total ),
			'by_type' => $by_type,
		);
	}

	/**
	 * Check if a record is tracked.
	 *
	 * @param string $generator Generator slug.
	 * @param string $type      Data type.
	 * @param int    $record_id Record ID.
	 * @return bool True if tracked, false otherwise.
	 */
	public function is_tracked( $generator, $type, $record_id ) {
		global $wpdb;

		$result = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT id FROM {$this->table_name} WHERE generator = %s AND data_type = %s AND record_id = %d",
				$generator,
				$type,
				$record_id
			)
		);

		return ! is_null( $result );
	}
}
