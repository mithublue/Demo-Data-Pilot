<?php
/**
 * Admin class
 *
 * @package DemoDataPilot
 */

namespace DemoDataPilot\Admin;

use DemoDataPilot\Generator_Registry;
use DemoDataPilot\Generation_Manager;
use DemoDataPilot\Logger;
use DemoDataPilot\Tracker;

/**
 * Admin class.
 *
 * Handles admin area functionality including menu, pages, and AJAX handlers.
 */
class Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @var string
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @var string
	 */
	private $version;

	/**
	 * Initialize the class.
	 *
	 * @param string $plugin_name The name of this plugin.
	 * @param string $version     The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Register the admin menu.
	 */
	public function add_admin_menu() {
		add_management_page(
			__( 'Demo Data Pilot', 'demo-data-pilot' ),
			__( 'Demo Data Pilot', 'demo-data-pilot' ),
			'manage_options',
			'demo-data-pilot',
			array( $this, 'display_admin_page' )
		);
	}

	/**
	 * Display the admin page.
	 */
	public function display_admin_page() {
		require_once DEMO_DATA_PILOT_PLUGIN_DIR . 'admin/views/main-page.php';
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @param string $hook_suffix The current admin page.
	 */
	public function enqueue_styles( $hook_suffix ) {
		if ( 'tools_page_demo-data-pilot' !== $hook_suffix ) {
			return;
		}

		wp_enqueue_style(
			$this->plugin_name,
			DEMO_DATA_PILOT_PLUGIN_URL . 'admin/assets/css/admin.css',
			array(),
			$this->version,
			'all'
		);
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @param string $hook_suffix The current admin page.
	 */
	public function enqueue_scripts( $hook_suffix ) {
		if ( 'tools_page_demo-data-pilot' !== $hook_suffix ) {
			return;
		}

		wp_enqueue_script(
			$this->plugin_name,
			DEMO_DATA_PILOT_PLUGIN_URL . 'admin/assets/js/admin.js',
			array( 'jquery' ),
			$this->version,
			true
		);

		// Localize script with AJAX data.
		wp_localize_script(
			$this->plugin_name,
			'demoDataPilot',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'demo_data_pilot_nonce' ),
				'i18n'     => array(
					'generating'       => __( 'Generating...', 'demo-data-pilot' ),
					'success'          => __( 'Generation completed successfully!', 'demo-data-pilot' ),
					'error'            => __( 'An error occurred. Please try again.', 'demo-data-pilot' ),
					'cleanup_confirm'  => __( 'Are you sure you want to delete all generated data? This action cannot be undone.', 'demo-data-pilot' ),
					'cleanup_label'    => __( 'Cleanup', 'demo-data-pilot' ),
					'cleaning_up'      => __( 'Cleaning up...', 'demo-data-pilot' ),
					'cleanup_success'  => __( 'Cleanup completed successfully!', 'demo-data-pilot' ),
				),
			)
		);
	}

	/**
	 * AJAX handler to get all generators.
	 */
	public function ajax_get_generators() {
		check_ajax_referer( 'demo_data_pilot_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'demo-data-pilot' ) ) );
		}

		$generators = Generator_Registry::get_all_generators();
		$data       = array();

		foreach ( $generators as $slug => $generator ) {
			$tracker = new Tracker();
			$stats   = $tracker->get_stats( $slug );

			$data[] = array(
				'slug'        => $slug,
				'name'        => $generator->get_plugin_name(),
				'description' => $generator->get_description(),
				'is_active'   => $generator->is_plugin_active(),
				'data_types'  => $generator->get_data_types(),
				'icon'        => $generator->get_icon(),
				'stats'       => $stats,
			);
		}

		wp_send_json_success( $data );
	}

	/**
	 * AJAX handler to generate data.
	 */
	public function ajax_generate_data() {
		check_ajax_referer( 'demo_data_pilot_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'demo-data-pilot' ) ) );
		}

		$generator_slug = isset( $_POST['generator'] ) ? sanitize_text_field( wp_unslash( $_POST['generator'] ) ) : '';
		$type           = isset( $_POST['type'] ) ? sanitize_text_field( wp_unslash( $_POST['type'] ) ) : '';
		$count          = isset( $_POST['count'] ) ? absint( $_POST['count'] ) : 10;
		$args           = isset( $_POST['args'] ) ? json_decode( wp_unslash( $_POST['args'] ), true ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		if ( empty( $generator_slug ) || empty( $type ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid parameters', 'demo-data-pilot' ) ) );
		}

		$manager = new Generation_Manager();
		$result  = $manager->generate( $generator_slug, $type, $count, $args );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}

		wp_send_json_success( $result );
	}

	/**
	 * AJAX handler to cleanup data.
	 */
	public function ajax_cleanup_data() {
		check_ajax_referer( 'demo_data_pilot_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'demo-data-pilot' ) ) );
		}

		$generator_slug = isset( $_POST['generator'] ) ? sanitize_text_field( wp_unslash( $_POST['generator'] ) ) : '';
		$type           = isset( $_POST['type'] ) ? sanitize_text_field( wp_unslash( $_POST['type'] ) ) : '';

		if ( empty( $generator_slug ) || empty( $type ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid parameters', 'demo-data-pilot' ) ) );
		}

		$manager = new Generation_Manager();
		$result  = $manager->cleanup( $generator_slug, $type );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) ) ;
		}

		wp_send_json_success( $result );
	}

	/**
	 * AJAX handler to get generation progress.
	 */
	public function ajax_get_progress() {
		check_ajax_referer( 'demo_data_pilot_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'demo-data-pilot' ) ) );
		}

		$generator = isset( $_POST['generator'] ) ? sanitize_text_field( wp_unslash( $_POST['generator'] ) ) : '';
		$type      = isset( $_POST['type'] ) ? sanitize_text_field( wp_unslash( $_POST['type'] ) ) : '';

		if ( empty( $generator ) || empty( $type ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid parameters', 'demo-data-pilot' ) ) );
		}

		$manager  = new Generation_Manager();
		$progress = $manager->get_progress( $generator, $type );

		if ( $progress ) {
			wp_send_json_success( $progress );
		} else {
			wp_send_json_error( array( 'message' => __( 'No progress data found', 'demo-data-pilot' ) ) );
		}
	}

	/**
	 * AJAX handler to clear logs.
	 */
	public function ajax_clear_logs() {
		check_ajax_referer( 'demo_data_pilot_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'demo-data-pilot' ) ) );
		}

		$logger = new Logger();
		$result = $logger->clear_logs();

		if ( $result ) {
			wp_send_json_success( array( 'message' => __( 'Logs cleared successfully', 'demo-data-pilot' ) ) );
		} else {
			wp_send_json_error( array( 'message' => __( 'Failed to clear logs', 'demo-data-pilot' ) ) );
		}
	}
}
