<?php
/**
 * Abstract base class for all generators
 *
 * @package DemoDataPilot
 */

namespace DemoDataPilot\Abstracts;

use Faker\Factory as FakerFactory;

/**
 * Abstract Generator class.
 *
 * All generator classes must extend this abstract class and implement
 * the required methods.
 */
abstract class Abstract_Generator {

	/**
	 * Plugin slug identifier (e.g., 'woocommerce').
	 *
	 * @var string
	 */
	protected $plugin_slug;

	/**
	 * Human-readable plugin name (e.g., 'WooCommerce').
	 *
	 * @var string
	 */
	protected $plugin_name;

	/**
	 * Array of supported data types.
	 * Format: array( 'slug' => 'Label' )
	 *
	 * @var array
	 */
	protected $data_types = array();

	/**
	 * Faker instance for generating fake data.
	 *
	 * @var \Faker\Generator
	 */
	protected $faker;

	/**
	 * Logger instance.
	 *
	 * @var \DemoDataPilot\Logger
	 */
	protected $logger;

	/**
	 * Tracker instance.
	 *
	 * @var \DemoDataPilot\Tracker
	 */
	protected $tracker;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->faker = FakerFactory::create();
		
		// Load logger and tracker.
		$this->logger  = new \DemoDataPilot\Logger();
		$this->tracker = new \DemoDataPilot\Tracker();
	}

	/**
	 * Check if the target plugin is active.
	 *
	 * @return bool True if plugin is active, false otherwise.
	 */
	abstract public function is_plugin_active();

	/**
	 * Get supported data types.
	 *
	 * @return array Array of data types with slug => label format.
	 */
	public function get_data_types() {
		return $this->data_types;
	}

	/**
	 * Generate data for the specified type.
	 *
	 * @param string $type  Data type to generate.
	 * @param int    $count Number of records to generate.
	 * @param array  $args  Additional generation arguments.
	 * @return array Array of generated record IDs.
	 */
	abstract public function generate( $type, $count, $args = array() );

	/**
	 * Clean up generated data.
	 *
	 * @param string $type Data type to cleanup.
	 * @param array  $ids  Optional. Specific IDs to cleanup. If empty, cleanup all tracked records.
	 * @return bool True on success, false on failure.
	 */
	abstract public function cleanup( $type, $ids = array() );

	/**
	 * Get form fields for admin UI.
	 *
	 * @param string $type Data type.
	 * @return array Array of form field definitions.
	 */
	abstract public function get_form_fields( $type );

	/**
	 * Get plugin slug.
	 *
	 * @return string Plugin slug.
	 */
	public function get_plugin_slug() {
		return $this->plugin_slug;
	}

	/**
	 * Get plugin name.
	 *
	 * @return string Plugin name.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * Validate dependencies before generation.
	 *
	 * @return bool|string True if valid, error message string if invalid.
	 */
	public function validate_dependencies() {
		if ( ! $this->is_plugin_active() ) {
			return sprintf(
				/* translators: %s: plugin name */
				__( '%s plugin is not active. Please activate it before generating data.', 'demo-data-pilot' ),
				$this->plugin_name
			);
		}

		return true;
	}

	/**
	 * Track a generated record.
	 *
	 * @param string $type      Data type.
	 * @param int    $record_id Record ID.
	 * @param array  $metadata  Optional metadata.
	 */
	protected function track_generated( $type, $record_id, $metadata = array() ) {
		$this->tracker->track( $this->plugin_slug, $type, $record_id, $metadata );
	}

	/**
	 * Log a message.
	 *
	 * @param string $message Log message.
	 * @param string $level   Log level (info, warning, error).
	 */
	protected function log( $message, $level = 'info' ) {
		$this->logger->log( $message, $level, $this->plugin_slug );
	}

	/**
	 * Get generator icon/image.
	 *
	 * @return string URL to icon or empty string.
	 */
	public function get_icon() {
		return '';
	}

	/**
	 * Get generator description.
	 *
	 * @return string Description text.
	 */
	public function get_description() {
		return sprintf(
			/* translators: %s: plugin name */
			__( 'Generate demo data for %s', 'demo-data-pilot' ),
			$this->plugin_name
		);
	}

	/**
	 * Get default batch size for this generator.
	 *
	 * @return int Default batch size.
	 */
	public function get_default_batch_size() {
		return 50;
	}

	/**
	 * Validate generation arguments.
	 *
	 * @param string $type Data type.
	 * @param int    $count Number of records.
	 * @param array  $args Additional arguments.
	 * @return bool|string True if valid, error message if invalid.
	 */
	protected function validate_generation_args( $type, $count, $args ) {
		// Check if type is supported.
		if ( ! array_key_exists( $type, $this->data_types ) ) {
			return sprintf(
				/* translators: %s: data type */
				__( 'Invalid data type: %s', 'demo-data-pilot' ),
				$type
			);
		}

		// Check count.
		if ( $count < 1 ) {
			return __( 'Count must be at least 1', 'demo-data-pilot' );
		}

		// Free version limit.
		if ( ! $this->is_pro_active() && $count > 100 ) {
			return __( 'Free version is limited to 100 records per generation. Upgrade to Pro for unlimited generation.', 'demo-data-pilot' );
		}

		return true;
	}

	/**
	 * Check if Pro version is active.
	 *
	 * @return bool True if Pro is active.
	 */
	protected function is_pro_active() {
		// This will be implemented when Pro version is developed.
		return apply_filters( 'demo_data_pilot_is_pro_active', false );
	}
}
