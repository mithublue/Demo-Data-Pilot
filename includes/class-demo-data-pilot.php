<?php
/**
 * The core plugin class
 *
 * @package DemoDataPilot
 */

namespace DemoDataPilot;

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 */
class Demo_Data_Pilot {

	/**
	 * The loader that's responsible for maintaining and registering all hooks.
	 *
	 * @var Loader
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @var string
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @var string
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 */
	public function __construct() {
		$this->version     = DEMO_DATA_PILOT_VERSION;
		$this->plugin_name = 'demo-data-pilot';

		$this->load_dependencies();

		$this->define_admin_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 */
	private function load_dependencies() {
		require_once DEMO_DATA_PILOT_PLUGIN_DIR . 'includes/class-loader.php';
		$this->loader = new Loader();
	}



	/**
	 * Register all of the hooks related to the admin area functionality.
	 */
	private function define_admin_hooks() {
		// Load admin class.
		require_once DEMO_DATA_PILOT_PLUGIN_DIR . 'admin/class-admin.php';
		$plugin_admin = new Admin\Admin( $this->get_plugin_name(), $this->get_version() );

		// Admin menu and assets.
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_admin_menu' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		// AJAX handlers.
		$this->loader->add_action( 'wp_ajax_ddp_get_generators', $plugin_admin, 'ajax_get_generators' );
		$this->loader->add_action( 'wp_ajax_ddp_generate_data', $plugin_admin, 'ajax_generate_data' );
		$this->loader->add_action( 'wp_ajax_ddp_cleanup_data', $plugin_admin, 'ajax_cleanup_data' );
		$this->loader->add_action( 'wp_ajax_ddp_get_progress', $plugin_admin, 'ajax_get_progress' );
		$this->loader->add_action( 'wp_ajax_ddp_clear_logs', $plugin_admin, 'ajax_clear_logs' );

		// Initialize generators on init.
		$this->loader->add_action( 'init', $this, 'register_generators', 5 );
	}



	/**
	 * Register all available generators.
	 */
	public function register_generators() {
		require_once DEMO_DATA_PILOT_PLUGIN_DIR . 'includes/class-generator-registry.php';
		Generator_Registry::discover_generators();
		
		/**
		 * Allow developers to register custom generators.
		 *
		 * @since 1.0.0
		 */
		do_action( 'demo_data_pilot_register_generators' );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it.
	 *
	 * @return string The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @return string The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @return Loader Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}
}
