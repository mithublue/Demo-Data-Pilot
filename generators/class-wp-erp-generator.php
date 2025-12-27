<?php
/**
 * WP ERP Generator
 *
 * @package DemoDataPilot
 */

namespace DemoDataPilot\Generators;

use DemoDataPilot\Abstracts\Abstract_Generator;

/**
 * WP ERP Generator class.
 *
 * Generates demo data for WP ERP including employees.
 */
class Wp_Erp_Generator extends Abstract_Generator {

	/**
	 * Plugin slug.
	 *
	 * @var string
	 */
	protected $plugin_slug = 'wp-erp';

	/**
	 * Plugin name.
	 *
	 * @var string
	 */
	protected $plugin_name = 'WP ERP';

	/**
	 * Supported data types.
	 *
	 * @var array
	 */
	protected $data_types = array(
		'employees' => 'Employees',
	);

	/**
	 * Check if WP ERP is active.
	 *
	 * @return bool
	 */
	public function is_plugin_active() {
		return function_exists( 'erp_hr_get_employees' );
	}

	/**
	 * Generate data.
	 *
	 * @param string $type  Data type.
	 * @param int    $count Number of records.
	 * @param array  $args  Additional arguments.
	 * @return array|WP_Error Generated IDs or error.
	 */
	public function generate( $type, $count, $args = array() ) {
		// Validate arguments.
		$validation = $this->validate_generation_args( $type, $count, $args );
		if ( true !== $validation ) {
			return new \WP_Error( 'validation_failed', $validation );
		}

		switch ( $type ) {
			case 'employees':
				return $this->generate_employees( $count, $args );
			default:
				return new \WP_Error( 'invalid_type', __( 'Invalid data type', 'demo-data-pilot' ) );
		}
	}

	/**
	 * Generate employees.
	 *
	 * @param int   $count Number of employees.
	 * @param array $args  Additional arguments.
	 * @return array Generated employee IDs.
	 */
	private function generate_employees( $count, $args ) {
		$generated = array();

		// Check if erp_hr_employee_create function exists.
		if ( ! function_exists( 'erp_hr_employee_create' ) ) {
			$this->log( __( 'WP ERP HR functions not available.', 'demo-data-pilot' ), 'error' );
			return $generated;
		}

		for ( $i = 0; $i < $count; $i++ ) {
			try {
				$first_name = $this->faker->firstName();
				$last_name  = $this->faker->lastName();

				$employee_data = array(
					'user_email'     => strtolower( $first_name . '.' . $last_name . wp_rand( 1, 999 ) . '@' . $this->faker->safeEmailDomain() ),
					'first_name'     => $first_name,
					'last_name'      => $last_name,
					'designation'    => $this->faker->randomElement( array(
						'Manager',
						'Developer',
						'Designer',
						'Marketing Specialist',
						'Sales Representative',
						'HR Manager',
						'Accountant',
						'Customer Support',
						'Project Manager',
						'Team Lead',
					) ),
					'department'     => $this->faker->randomElement( array(
						'Engineering',
						'Marketing',
						'Sales',
						'Human Resources',
						'Finance',
						'Customer Service',
						'Operations',
						'IT',
					) ),
					'location'       => $this->faker->randomElement( array(
						'New York',
						'Los Angeles',
						'Chicago',
						'Houston',
						'Phoenix',
						'Philadelphia',
						'San Antonio',
						'San Diego',
					) ),
					'hiring_date'    => $this->faker->dateTimeBetween( '-5 years', '-1 month' )->format( 'Y-m-d' ),
					'date_of_birth'  => $this->faker->dateTimeBetween( '-60 years', '-22 years' )->format( 'Y-m-d' ),
					'reporting_to'   => 0, // Can be set to another employee ID.
					'pay_rate'       => $this->faker->numberBetween( 40000, 150000 ),
					'pay_type'       => 'yearly',
					'type'           => $this->faker->randomElement( array( 'permanent', 'contract', 'temporary' ) ),
					'status'         => 'active',
					'other_email'    => '',
					'phone'          => $this->faker->phoneNumber(),
					'work_phone'     => $this->faker->phoneNumber(),
					'mobile'         => $this->faker->phoneNumber(),
					'address'        => array(
						'street_1' => $this->faker->streetAddress(),
						'street_2' => '',
						'city'     => $this->faker->city(),
						'state'    => $this->faker->state(),
						'country'  => 'US',
						'postal_code' => $this->faker->postcode(),
					),
				);

				// Create employee.
				$employee_id = erp_hr_employee_create( $employee_data );

				if ( ! is_wp_error( $employee_id ) && $employee_id ) {
					// Track generated record.
					$this->track_generated( 'employees', $employee_id );
					$generated[] = $employee_id;

					$this->log(
						sprintf(
							/* translators: 1: employee ID, 2: employee name */
							__( 'Generated employee #%1$d: %2$s %3$s', 'demo-data-pilot' ),
							$employee_id,
							$first_name,
							$last_name
						)
					);
				} else {
					if ( is_wp_error( $employee_id ) ) {
						$this->log( $employee_id->get_error_message(), 'error' );
					}
				}

			} catch ( \Exception $e ) {
				$this->log( $e->getMessage(), 'error' );
			}
		}

		return $generated;
	}

	/**
	 * Cleanup generated data.
	 *
	 * @param string $type Data type.
	 * @param array  $ids  IDs to cleanup.
	 * @return bool
	 */
	public function cleanup( $type, $ids = array() ) {
		if ( empty( $ids ) ) {
			return true;
		}

		if ( ! function_exists( 'erp_hr_employee_delete' ) ) {
			return true;
		}

		$deleted = 0;

		foreach ( $ids as $id ) {
			try {
				if ( 'employees' === $type ) {
					$result = erp_hr_employee_delete( $id );
					if ( ! is_wp_error( $result ) ) {
						$deleted++;
					}
				}
			} catch ( \Exception $e ) {
				$this->log( $e->getMessage(), 'error' );
			}
		}

		$this->log(
			sprintf(
				/* translators: 1: deleted count, 2: type */
				__( 'Deleted %1$d %2$s records', 'demo-data-pilot' ),
				$deleted,
				$type
			),
			'success'
		);

		return true;
	}

	/**
	 * Get form fields for admin UI.
	 *
	 * @param string $type Data type.
	 * @return array
	 */
	public function get_form_fields( $type ) {
		return array();
	}

	/**
	 * Get generator description.
	 *
	 * @return string
	 */
	public function get_description() {
		return __( 'Generate realistic employee records for WP ERP Human Resources module.', 'demo-data-pilot' );
	}
}
