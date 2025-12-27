<?php
/**
 * Generation Manager
 *
 * @package DemoDataPilot
 */

namespace DemoDataPilot;

/**
 * Generation Manager class.
 *
 * Handles the data generation process with batch processing and progress tracking.
 */
class Generation_Manager {

	/**
	 * Logger instance.
	 *
	 * @var Logger
	 */
	private $logger;

	/**
	 * Tracker instance.
	 *
	 * @var Tracker
	 */
	private $tracker;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->logger  = new Logger();
		$this->tracker = new Tracker();
	}

	/**
	 * Generate data.
	 *
	 * @param string $generator_slug Generator slug.
	 * @param string $type          Data type to generate.
	 * @param int    $count         Number of records to generate.
	 * @param array  $args          Additional generation arguments.
	 * @return array|WP_Error Generation result or WP_Error on failure.
	 */
	public function generate( $generator_slug, $type, $count, $args = array() ) {
		// Get generator.
		$generator = Generator_Registry::get_generator( $generator_slug );

		if ( ! $generator ) {
			return new \WP_Error(
				'invalid_generator',
				sprintf(
					/* translators: %s: generator slug */
					__( 'Generator not found: %s', 'demo-data-pilot' ),
					$generator_slug
				)
			);
		}

		// Validate dependencies.
		$validation = $generator->validate_dependencies();
		if ( true !== $validation ) {
			return new \WP_Error( 'validation_failed', $validation );
		}

		// Log start.
		$this->logger->log(
			sprintf(
				/* translators: 1: count, 2: type, 3: generator name */
				__( 'Starting generation of %1$d %2$s records for %3$s', 'demo-data-pilot' ),
				$count,
				$type,
				$generator->get_plugin_name()
			),
			Logger::LEVEL_INFO,
			$generator_slug
		);

		// Get batch size.
		$batch_size = isset( $args['batch_size'] ) ? absint( $args['batch_size'] ) : $generator->get_default_batch_size();
		$batch_size = apply_filters( 'demo_data_pilot_batch_size', $batch_size, $generator_slug, $type );

		// Process in batches.
		try {
			$generated_ids = $this->batch_process( $generator, $type, $count, $args, $batch_size );

			// Log success.
			$this->logger->log(
				sprintf(
					/* translators: 1: count, 2: type */
					__( 'Successfully generated %1$d %2$s records', 'demo-data-pilot' ),
					count( $generated_ids ),
					$type
				),
				Logger::LEVEL_SUCCESS,
				$generator_slug
			);

			return array(
				'success'       => true,
				'generated_ids' => $generated_ids,
				'count'         => count( $generated_ids ),
				'type'          => $type,
				'generator'     => $generator_slug,
			);

		} catch ( \Exception $e ) {
			// Log error.
			$this->logger->log(
				sprintf(
					/* translators: %s: error message */
					__( 'Generation failed: %s', 'demo-data-pilot' ),
					$e->getMessage()
				),
				Logger::LEVEL_ERROR,
				$generator_slug
			);

			return new \WP_Error( 'generation_failed', $e->getMessage() );
		}
	}

	/**
	 * Process generation in batches.
	 *
	 * @param \DemoDataPilot\Abstracts\Abstract_Generator $generator  Generator instance.
	 * @param string                                       $type       Data type.
	 * @param int                                          $count      Total count to generate.
	 * @param array                                        $args       Additional arguments.
	 * @param int                                          $batch_size Batch size.
	 * @return array Array of generated IDs.
	 * @throws \Exception If generation fails.
	 */
	private function batch_process( $generator, $type, $count, $args, $batch_size ) {
		$generated_ids  = array();
		$remaining      = $count;
		$current_batch  = 0;
		$total_batches  = ceil( $count / $batch_size );

		// Apply filter to allow modification of generation args.
		$args = apply_filters( 'demo_data_pilot_generation_args', $args, $generator->get_plugin_slug(), $type );

		// Fire action before generation.
		do_action( 'demo_data_pilot_before_generate', $generator->get_plugin_slug(), $type, $count );

		while ( $remaining > 0 ) {
			$current_batch++;
			$batch_count = min( $batch_size, $remaining );

			// Update progress.
			$this->update_progress(
				$generator->get_plugin_slug(),
				$type,
				$current_batch,
				$total_batches,
				count( $generated_ids )
			);

			// Generate batch.
			$batch_ids = $generator->generate( $type, $batch_count, $args );

			if ( is_wp_error( $batch_ids ) ) {
				throw new \Exception( esc_html( $batch_ids->get_error_message() ) );
			}

			$generated_ids = array_merge( $generated_ids, $batch_ids );
			$remaining    -= $batch_count;

			// Small delay to prevent overwhelming the server.
			if ( $remaining > 0 ) {
				usleep( 100000 ); // 100ms.
			}
		}

		// Fire action after generation.
		do_action( 'demo_data_pilot_after_generate', $generator->get_plugin_slug(), $type, $generated_ids );

		return $generated_ids;
	}

	/**
	 * Update generation progress.
	 *
	 * @param string $generator     Generator slug.
	 * @param string $type          Data type.
	 * @param int    $current_batch Current batch number.
	 * @param int    $total_batches Total batches.
	 * @param int    $generated     Number of records generated so far.
	 */
	private function update_progress( $generator, $type, $current_batch, $total_batches, $generated ) {
		$progress = array(
			'generator'      => $generator,
			'type'           => $type,
			'current_batch'  => $current_batch,
			'total_batches'  => $total_batches,
			'generated'      => $generated,
			'percentage'     => ( $current_batch / $total_batches ) * 100,
			'timestamp'      => time(),
		);

		set_transient( 'ddp_progress_' . $generator . '_' . $type, $progress, 300 );
	}

	/**
	 * Get generation progress.
	 *
	 * @param string $generator Generator slug.
	 * @param string $type      Data type.
	 * @return array|false Progress data or false if not found.
	 */
	public function get_progress( $generator, $type ) {
		return get_transient( 'ddp_progress_' . $generator . '_' . $type );
	}

	/**
	 * Cleanup generated data.
	 *
	 * @param string $generator_slug Generator slug.
	 * @param string $type          Data type to cleanup.
	 * @param array  $ids           Optional. Specific IDs to cleanup.
	 * @return array|WP_Error Cleanup result or WP_Error on failure.
	 */
	public function cleanup( $generator_slug, $type, $ids = array() ) {
		// Get generator.
		$generator = Generator_Registry::get_generator( $generator_slug );

		if ( ! $generator ) {
			return new \WP_Error( 'invalid_generator', __( 'Generator not found', 'demo-data-pilot' ) );
		}

		// Log start.
		$this->logger->log(
			sprintf(
				/* translators: 1: type, 2: generator name */
				__( 'Starting cleanup of %1$s records for %2$s', 'demo-data-pilot' ),
				$type,
				$generator->get_plugin_name()
			),
			Logger::LEVEL_INFO,
			$generator_slug
		);

		// Fire action before cleanup.
		do_action( 'demo_data_pilot_before_cleanup', $generator_slug, $type, $ids );

		try {
			// If no specific IDs provided, get all tracked IDs.
			if ( empty( $ids ) ) {
				$ids = $this->tracker->get_tracked_ids( $generator_slug, $type );
			}

			// Cleanup via generator.
			$result = $generator->cleanup( $type, $ids );

			if ( is_wp_error( $result ) ) {
				throw new \Exception( $result->get_error_message() );
			}

			// Remove from tracking.
			$this->tracker->cleanup_all( $generator_slug, $type );

			// Log success.
			$this->logger->log(
				sprintf(
					/* translators: 1: count, 2: type */
					__( 'Successfully cleaned up %1$d %2$s records', 'demo-data-pilot' ),
					count( $ids ),
					$type
				),
				Logger::LEVEL_SUCCESS,
				$generator_slug
			);

			// Fire action after cleanup.
			do_action( 'demo_data_pilot_after_cleanup', $generator_slug, $type );

			return array(
				'success' => true,
				'count'   => count( $ids ),
				'type'    => $type,
			);

		} catch ( \Exception $e ) {
			// Log error.
			$this->logger->log(
				sprintf(
					/* translators: %s: error message */
					__( 'Cleanup failed: %s', 'demo-data-pilot' ),
					$e->getMessage()
				),
				Logger::LEVEL_ERROR,
				$generator_slug
			);

			return new \WP_Error( 'cleanup_failed', $e->getMessage() );
		}
	}
}
