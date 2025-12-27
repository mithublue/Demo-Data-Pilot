<?php
/**
 * Generator Registry
 *
 * @package DemoDataPilot
 */

namespace DemoDataPilot;

/**
 * Generator Registry class.
 *
 * Manages registration and retrieval of generator instances.
 */
class Generator_Registry {

	/**
	 * Array of registered generators.
	 *
	 * @var array
	 */
	private static $generators = array();

	/**
	 * Register a generator.
	 *
	 * @param \DemoDataPilot\Abstracts\Abstract_Generator $generator Generator instance.
	 * @return bool True on success, false if already registered.
	 */
	public static function register( $generator ) {
		$slug = $generator->get_plugin_slug();

		if ( isset( self::$generators[ $slug ] ) ) {
			return false;
		}

		self::$generators[ $slug ] = $generator;
		return true;
	}

	/**
	 * Get a generator by slug.
	 *
	 * @param string $slug Generator slug.
	 * @return \DemoDataPilot\Abstracts\Abstract_Generator|null Generator instance or null if not found.
	 */
	public static function get_generator( $slug ) {
		return isset( self::$generators[ $slug ] ) ? self::$generators[ $slug ] : null;
	}

	/**
	 * Get all registered generators.
	 *
	 * @param bool $active_only Whether to return only active generators (target plugin is active).
	 * @return array Array of generator instances.
	 */
	public static function get_all_generators( $active_only = false ) {
		if ( ! $active_only ) {
			return self::$generators;
		}

		return array_filter(
			self::$generators,
			function( $generator ) {
				return $generator->is_plugin_active();
			}
		);
	}

	/**
	 * Auto-discover and register generators from the generators directory.
	 *
	 * @return int Number of generators discovered and registered.
	 */
	public static function discover_generators() {
		$generators_dir = DEMO_DATA_PILOT_PLUGIN_DIR . 'generators/';
		$count          = 0;

		if ( ! is_dir( $generators_dir ) ) {
			return $count;
		}

		// Ensure Abstract_Generator is loaded first.
		require_once DEMO_DATA_PILOT_PLUGIN_DIR . 'includes/abstracts/abstract-generator.php';

		// Get all PHP files in generators directory.
		$generator_files = glob( $generators_dir . 'class-*-generator.php' );

		if ( empty( $generator_files ) ) {
			return $count;
		}

		foreach ( $generator_files as $file ) {
			// Extract class name from filename.
			$filename   = basename( $file, '.php' );
			$class_name = str_replace( 'class-', '', $filename );
			$class_name = str_replace( '-', '_', $class_name );
			$class_name = 'DemoDataPilot\\Generators\\' . ucwords( $class_name, '_' );

			// Convert underscores to proper case.
			$class_parts = explode( '_', str_replace( 'DemoDataPilot\\Generators\\', '', $class_name ) );
			$class_parts = array_map( 'ucfirst', $class_parts );
			$class_name  = 'DemoDataPilot\\Generators\\' . implode( '_', $class_parts );

			// Load file.
			require_once $file;

			// Instantiate and register if class exists.
			if ( class_exists( $class_name ) ) {
				$generator = new $class_name();
				if ( self::register( $generator ) ) {
					$count++;
				}
			}
		}

		return $count;
	}

	/**
	 * Check if a generator is registered.
	 *
	 * @param string $slug Generator slug.
	 * @return bool True if registered, false otherwise.
	 */
	public static function is_registered( $slug ) {
		return isset( self::$generators[ $slug ] );
	}

	/**
	 * Get count of registered generators.
	 *
	 * @return int Number of registered generators.
	 */
	public static function count() {
		return count( self::$generators );
	}
}
