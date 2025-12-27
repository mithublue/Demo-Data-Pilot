<?php
/**
 * Admin main page view
 *
 * @package DemoDataPilot
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

use DemoDataPilot\Generator_Registry;
use DemoDataPilot\Logger;
use DemoDataPilot\Tracker;

$generators = Generator_Registry::get_all_generators();
$logger     = new Logger();
$recent_logs = $logger->get_logs( 10 );
?>

<div class="wrap ddp-admin-page">
	<h1>
		<?php echo esc_html( get_admin_page_title() ); ?>
		<span class="ddp-version">v<?php echo esc_html( DEMO_DATA_PILOT_VERSION ); ?></span>
	</h1>

	<p class="ddp-description">
		<?php esc_html_e( 'Generate realistic demo data for testing your WordPress plugins. Select a generator below to get started.', 'demo-data-pilot' ); ?>
	</p>

	<?php if ( empty( $generators ) ) : ?>
		<div class="notice notice-warning">
			<p><?php esc_html_e( 'No generators found. Please make sure the generators directory exists and contains valid generator files.', 'demo-data-pilot' ); ?></p>
		</div>
	<?php else : ?>

		<div class="ddp-generators-grid">
			<?php foreach ( $generators as $slug => $generator ) : ?>
				<?php
				$is_active = $generator->is_plugin_active();
				$tracker   = new Tracker();
				$stats     = $tracker->get_stats( $slug );
				?>

				<div class="ddp-generator-card <?php echo $is_active ? 'ddp-active' : 'ddp-inactive'; ?>" data-generator="<?php echo esc_attr( $slug ); ?>">
					<div class="ddp-generator-header">
						<?php if ( $generator->get_icon() ) : ?>
							<img src="<?php echo esc_url( $generator->get_icon() ); ?>" alt="<?php echo esc_attr( $generator->get_plugin_name() ); ?>" class="ddp-generator-icon">
						<?php else : ?>
							<div class="ddp-generator-icon-placeholder">
								<?php echo esc_html( substr( $generator->get_plugin_name(), 0, 1 ) ); ?>
							</div>
						<?php endif; ?>

						<div class="ddp-generator-title">
							<h2><?php echo esc_html( $generator->get_plugin_name() ); ?></h2>
							<span class="ddp-status-badge <?php echo $is_active ? 'ddp-badge-active' : 'ddp-badge-inactive'; ?>">
								<?php echo $is_active ? esc_html__( 'Active', 'demo-data-pilot' ) : esc_html__( 'Inactive', 'demo-data-pilot' ); ?>
							</span>
						</div>
					</div>

					<p class="ddp-generator-description"><?php echo esc_html( $generator->get_description() ); ?></p>

					<?php if ( $is_active ) : ?>
						<div class="ddp-data-types">
							<?php foreach ( $generator->get_data_types() as $type_slug => $type_label ) : ?>
								<div class="ddp-data-type" data-type="<?php echo esc_attr( $type_slug ); ?>">
									<div class="ddp-data-type-header">
										<div class="ddp-data-type-info">
											<span class="dashicons dashicons-database"></span>
											<strong><?php echo esc_html( $type_label ); ?></strong>
										</div>
										<div class="ddp-data-type-stats">
											<?php
											$type_count = 0;
											if ( ! empty( $stats['by_type'] ) ) {
												foreach ( $stats['by_type'] as $stat ) {
													if ( $stat->data_type === $type_slug ) {
														$type_count = $stat->count;
														break;
													}
												}
											}
											?>
											<span class="ddp-count-badge"><?php echo esc_html( $type_count ); ?> generated</span>
										</div>
									</div>

									<div class="ddp-data-type-controls">
										<div class="ddp-input-group">
											<label for="ddp-count-<?php echo esc_attr( $slug . '-' . $type_slug ); ?>">
												<?php esc_html_e( 'Count:', 'demo-data-pilot' ); ?>
											</label>
											<input 
												type="number" 
												id="ddp-count-<?php echo esc_attr( $slug . '-' . $type_slug ); ?>" 
												class="ddp-count-input" 
												value="10" 
												min="1" 
												max="100"
											>
										</div>

										<div class="ddp-button-group">
											<button 
												type="button" 
												class="button button-primary ddp-generate-btn" 
												data-generator="<?php echo esc_attr( $slug ); ?>" 
												data-type="<?php echo esc_attr( $type_slug ); ?>"
											>
												<span class="dashicons dashicons-plus-alt"></span>
												<?php esc_html_e( 'Generate', 'demo-data-pilot' ); ?>
											</button>

											<?php if ( $type_count > 0 ) : ?>
												<button 
													type="button" 
													class="button ddp-cleanup-btn" 
													data-generator="<?php echo esc_attr( $slug ); ?>" 
													data-type="<?php echo esc_attr( $type_slug ); ?>"
												>
													<span class="dashicons dashicons-trash"></span>
													<?php esc_html_e( 'Cleanup', 'demo-data-pilot' ); ?>
												</button>
											<?php endif; ?>
										</div>
									</div>

									<div class="ddp-progress-bar" style="display: none;">
										<div class="ddp-progress-fill" style="width: 0%;"></div>
										<span class="ddp-progress-text">0%</span>
									</div>
								</div>
							<?php endforeach; ?>
						</div>
					<?php else : ?>
						<div class="notice notice-warning inline">
							<p>
								<?php
								echo wp_kses_post(
									sprintf(
										/* translators: %s: plugin name */
										__( '<strong>%s</strong> plugin is not active. Please activate it to generate data.', 'demo-data-pilot' ),
										$generator->get_plugin_name()
									)
								);
								?>
							</p>
						</div>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>

		<div class="ddp-logs-section">
			<h2><?php esc_html_e( 'Recent Activity', 'demo-data-pilot' ); ?></h2>
			
			<?php if ( empty( $recent_logs ) ) : ?>
				<p class="ddp-no-logs"><?php esc_html_e( 'No activity yet. Start generating data to see logs here.', 'demo-data-pilot' ); ?></p>
			<?php else : ?>
				<div class="ddp-logs-list">
					<?php foreach ( $recent_logs as $log ) : ?>
						<div class="ddp-log-entry ddp-log-<?php echo esc_attr( $log['level'] ); ?>">
							<span class="ddp-log-timestamp"><?php echo esc_html( $log['timestamp'] ); ?></span>
							<span class="ddp-log-level ddp-level-<?php echo esc_attr( $log['level'] ); ?>">
								<?php echo esc_html( ucfirst( $log['level'] ) ); ?>
							</span>
							<?php if ( ! empty( $log['generator'] ) ) : ?>
								<span class="ddp-log-generator">[<?php echo esc_html( $log['generator'] ); ?>]</span>
							<?php endif; ?>
							<span class="ddp-log-message"><?php echo esc_html( $log['message'] ); ?></span>
						</div>
					<?php endforeach; ?>
				</div>

				<button type="button" class="button ddp-clear-logs-btn" id="ddp-clear-logs">
					<span class="dashicons dashicons-dismiss"></span>
					<?php esc_html_e( 'Clear Logs', 'demo-data-pilot' ); ?>
				</button>
			<?php endif; ?>
		</div>

	<?php endif; ?>
</div>
