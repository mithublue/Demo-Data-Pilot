<?php
/**
 * WooCommerce Generator
 *
 * @package DemoDataPilot
 */

namespace DemoDataPilot\Generators;

use DemoDataPilot\Abstracts\Abstract_Generator;

/**
 * WooCommerce Generator class.
 *
 * Generates demo data for WooCommerce including products, customers, and orders.
 */
class WooCommerce_Generator extends Abstract_Generator {

	/**
	 * Plugin slug.
	 *
	 * @var string
	 */
	protected $plugin_slug = 'woocommerce';

	/**
	 * Plugin name.
	 *
	 * @var string
	 */
	protected $plugin_name = 'WooCommerce';

	/**
	 * Supported data types.
	 *
	 * @var array
	 */
	protected $data_types = array(
		'products'  => 'Products',
		'customers' => 'Customers',
		'orders'    => 'Orders',
	);

	/**
	 * Check if WooCommerce is active.
	 *
	 * @return bool
	 */
	public function is_plugin_active() {
		return class_exists( 'WooCommerce' );
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
			case 'products':
				return $this->generate_products( $count, $args );
			case 'customers':
				return $this->generate_customers( $count, $args );
			case 'orders':
				return $this->generate_orders( $count, $args );
			default:
				return new \WP_Error( 'invalid_type', __( 'Invalid data type', 'demo-data-pilot' ) );
		}
	}

	/**
	 * Generate products.
	 *
	 * @param int   $count Number of products.
	 * @param array $args  Additional arguments.
	 * @return array Generated product IDs.
	 */
	private function generate_products( $count, $args ) {
		$generated = array();

		// Product categories and types for realistic names.
		$product_types = array(
			'Electronics' => array( 'Wireless Headphones', 'Smart Watch', 'Bluetooth Speaker', 'USB Cable', 'Phone Case', 'Power Bank', 'Laptop Stand', 'Webcam', 'Keyboard', 'Mouse' ),
			'Clothing'    => array( 'Cotton T-Shirt', 'Denim Jeans', 'Hoodie', 'Running Shoes', 'Sneakers', 'Baseball Cap', 'Winter Jacket', 'Polo Shirt', 'Dress Shirt', 'Casual Pants' ),
			'Home'        => array( 'Coffee Maker', 'Blender', 'Vacuum Cleaner', 'Table Lamp', 'Throw Pillow', 'Wall Clock', 'Storage Box', 'Picture Frame', 'Candle Set', 'Bath Towel' ),
			'Sports'      => array( 'Yoga Mat', 'Dumbbell Set', 'Water Bottle', 'Resistance Bands', 'Jump Rope', 'Gym Bag', 'Tennis Racket', 'Basketball', 'Running Belt', 'Fitness Tracker' ),
			'Books'       => array( 'Fiction Novel', 'Cookbook', 'Self-Help Book', 'Biography', 'Travel Guide', 'Art Book', 'Science Book', 'History Book', 'Children\'s Book', 'Comic Book' ),
		);

		$adjectives = array( 'Premium', 'Professional', 'Deluxe', 'Classic', 'Modern', 'Vintage', 'Eco-Friendly', 'Portable', 'Compact', 'Ultra', 'Pro', 'Essential', 'Advanced', 'Smart' );
		$colors     = array( 'Black', 'White', 'Blue', 'Red', 'Gray', 'Silver', 'Gold', 'Green', 'Navy', 'Charcoal' );

		for ( $i = 0; $i < $count; $i++ ) {
			try {
				$product = new \WC_Product_Simple();

				// Generate meaningful product name.
				$category     = $this->faker->randomElement( array_keys( $product_types ) );
				$product_type = $this->faker->randomElement( $product_types[ $category ] );
				$adjective    = $this->faker->randomElement( $adjectives );
				$color        = $this->faker->boolean( 40 ) ? $this->faker->randomElement( $colors ) . ' ' : '';
				
				$product_name = $adjective . ' ' . $color . $product_type;
				
				$product->set_name( $product_name );
				$product->set_slug( sanitize_title( $product_name . '-' . $this->faker->unique()->numberBetween( 1000, 9999 ) ) );
				
				// Generate meaningful description.
				$features = array(
					'High-quality materials ensure durability and long-lasting performance.',
					'Designed with user comfort and convenience in mind.',
					'Perfect for everyday use or special occasions.',
					'Easy to use and maintain with simple care instructions.',
					'Comes with a satisfaction guarantee and excellent customer support.',
					'Sleek and modern design that fits any style.',
					'Lightweight and portable for on-the-go convenience.',
					'Energy-efficient and environmentally friendly.',
					'Compatible with most standard accessories and equipment.',
					'Trusted by thousands of satisfied customers worldwide.',
				);

				$description_parts = array(
					'Introducing our ' . strtolower( $product_name ) . '. ',
					$this->faker->randomElement( $features ) . ' ',
					$this->faker->randomElement( $features ) . ' ',
					'Whether you\'re looking for quality, style, or functionality, this product delivers on all fronts.',
				);

				$product->set_description( implode( "\n\n", $description_parts ) );
				$product->set_short_description( $this->faker->randomElement( $features ) );
				
				// Price.
				$regular_price = $this->faker->randomFloat( 2, 10, 500 );
				$product->set_regular_price( $regular_price );
				
				// Sometimes add a sale price.
				if ( $this->faker->boolean( 30 ) ) {
					$sale_price = $regular_price * $this->faker->randomFloat( 2, 0.5, 0.9 );
					$product->set_sale_price( $sale_price );
				}

				// Stock.
				$product->set_manage_stock( true );
				$product->set_stock_quantity( $this->faker->numberBetween( 0, 100 ) );
				$product->set_stock_status( $this->faker->randomElement( array( 'instock', 'outofstock' ) ) );

				// SKU.
				$product->set_sku( 'DDP-' . strtoupper( $this->faker->bothify( '???-####' ) ) );

				// Status.
				$product->set_status( 'publish' );
				$product->set_catalog_visibility( 'visible' );

				// Featured.
				$product->set_featured( $this->faker->boolean( 20 ) );

				// Save product.
				$product_id = $product->save();

				if ( $product_id ) {
					// Track generated record.
					$this->track_generated( 'products', $product_id );
					$generated[] = $product_id;

					$this->log(
						sprintf(
							/* translators: 1: product ID, 2: product name */
							__( 'Generated product #%1$d: %2$s', 'demo-data-pilot' ),
							$product_id,
							$product->get_name()
						)
					);
				}

			} catch ( \Exception $e ) {
				$this->log( $e->getMessage(), 'error' );
			}
		}

		return $generated;
	}

	/**
	 * Generate customers.
	 *
	 * @param int   $count Number of customers.
	 * @param array $args  Additional arguments.
	 * @return array Generated customer IDs.
	 */
	private function generate_customers( $count, $args ) {
		$generated = array();

		for ( $i = 0; $i < $count; $i++ ) {
			try {
				$customer = new \WC_Customer();

				// Personal info.
				$first_name = $this->faker->firstName();
				$last_name  = $this->faker->lastName();
				
				$customer->set_first_name( $first_name );
				$customer->set_last_name( $last_name );
				$customer->set_email( strtolower( $first_name . '.' . $last_name . '@' . $this->faker->safeEmailDomain() ) );
				$customer->set_username( strtolower( $first_name . $last_name . rand( 1, 999 ) ) );

				// Billing address.
				$customer->set_billing_first_name( $first_name );
				$customer->set_billing_last_name( $last_name );
				$customer->set_billing_company( $this->faker->boolean( 40 ) ? $this->faker->company() : '' );
				$customer->set_billing_address_1( $this->faker->streetAddress() );
				$customer->set_billing_address_2( $this->faker->boolean( 30 ) ? $this->faker->secondaryAddress() : '' );
				$customer->set_billing_city( $this->faker->city() );
				$customer->set_billing_state( $this->faker->stateAbbr() );
				$customer->set_billing_postcode( $this->faker->postcode() );
				$customer->set_billing_country( 'US' );
				$customer->set_billing_phone( $this->faker->phoneNumber() );
				$customer->set_billing_email( $customer->get_email() );

				// Shipping address (sometimes same as billing).
				if ( $this->faker->boolean( 70 ) ) {
					$customer->set_shipping_first_name( $first_name );
					$customer->set_shipping_last_name( $last_name );
					$customer->set_shipping_company( $customer->get_billing_company() );
					$customer->set_shipping_address_1( $customer->get_billing_address_1() );
					$customer->set_shipping_address_2( $customer->get_billing_address_2() );
					$customer->set_shipping_city( $customer->get_billing_city() );
					$customer->set_shipping_state( $customer->get_billing_state() );
					$customer->set_shipping_postcode( $customer->get_billing_postcode() );
					$customer->set_shipping_country( $customer->get_billing_country() );
				} else {
					$customer->set_shipping_first_name( $first_name );
					$customer->set_shipping_last_name( $last_name );
					$customer->set_shipping_address_1( $this->faker->streetAddress() );
					$customer->set_shipping_city( $this->faker->city() );
					$customer->set_shipping_state( $this->faker->stateAbbr() );
					$customer->set_shipping_postcode( $this->faker->postcode() );
					$customer->set_shipping_country( 'US' );
				}

				// Save customer.
				$customer_id = $customer->save();

				if ( $customer_id ) {
					// Track generated record.
					$this->track_generated( 'customers', $customer_id );
					$generated[] = $customer_id;

					$this->log(
						sprintf(
							/* translators: 1: customer ID, 2: customer email */
							__( 'Generated customer #%1$d: %2$s', 'demo-data-pilot' ),
							$customer_id,
							$customer->get_email()
						)
					);
				}

			} catch ( \Exception $e ) {
				$this->log( $e->getMessage(), 'error' );
			}
		}

		return $generated;
	}

	/**
	 * Generate orders.
	 *
	 * @param int   $count Number of orders.
	 * @param array $args  Additional arguments.
	 * @return array Generated order IDs.
	 */
	private function generate_orders( $count, $args ) {
		$generated = array();

		// Get available products and customers.
		$products  = wc_get_products( array( 'limit' => -1, 'return' => 'ids' ) );
		$customers = get_users( array( 'role' => 'customer', 'fields' => 'ID' ) );

		if ( empty( $products ) ) {
			$this->log( __( 'No products available. Please generate products first.', 'demo-data-pilot' ), 'warning' );
			return $generated;
		}

		for ( $i = 0; $i < $count; $i++ ) {
			try {
				$order = wc_create_order();

				// Assign customer if available.
				if ( ! empty( $customers ) ) {
					$customer_id = $this->faker->randomElement( $customers );
					$order->set_customer_id( $customer_id );
					
					$customer = new \WC_Customer( $customer_id );
					$order->set_billing_first_name( $customer->get_billing_first_name() );
					$order->set_billing_last_name( $customer->get_billing_last_name() );
					$order->set_billing_email( $customer->get_billing_email() );
					$order->set_billing_address_1( $customer->get_billing_address_1() );
					$order->set_billing_city( $customer->get_billing_city() );
					$order->set_billing_state( $customer->get_billing_state() );
					$order->set_billing_postcode( $customer->get_billing_postcode() );
					$order->set_billing_country( $customer->get_billing_country() );
				}

				// Add random number of products.
				$num_items = $this->faker->numberBetween( 1, 5 );
				$selected_products = $this->faker->randomElements( $products, $num_items );

				foreach ( $selected_products as $product_id ) {
					$product = wc_get_product( $product_id );
					if ( $product ) {
						$order->add_product( $product, $this->faker->numberBetween( 1, 3 ) );
					}
				}

				// Set order status.
				$statuses = array( 'pending', 'processing', 'on-hold', 'completed', 'cancelled', 'refunded', 'failed' );
				$order->set_status( $this->faker->randomElement( $statuses ) );

				// Set payment method.
				$payment_methods = array( 'bacs', 'cheque', 'cod', 'paypal', 'stripe' );
				$order->set_payment_method( $this->faker->randomElement( $payment_methods ) );

				// Calculate totals.
				$order->calculate_totals();

				// Set random date in the past 6 months.
				$date = $this->faker->dateTimeBetween( '-6 months', 'now' );
				$order->set_date_created( $date->format( 'Y-m-d H:i:s' ) );

				// Save order.
				$order_id = $order->save();

				if ( $order_id ) {
					// Track generated record.
					$this->track_generated( 'orders', $order_id );
					$generated[] = $order_id;

					$this->log(
						sprintf(
							/* translators: 1: order ID, 2: order total */
							__( 'Generated order #%1$d with total %2$s', 'demo-data-pilot' ),
							$order_id,
							wc_price( $order->get_total() )
						)
					);
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

		$deleted = 0;

		foreach ( $ids as $id ) {
			try {
				switch ( $type ) {
					case 'products':
						$product = wc_get_product( $id );
						if ( $product ) {
							$product->delete( true );
							$deleted++;
						}
						break;

					case 'customers':
						if ( wp_delete_user( $id ) ) {
							$deleted++;
						}
						break;

					case 'orders':
						$order = wc_get_order( $id );
						if ( $order ) {
							$order->delete( true );
							$deleted++;
						}
						break;
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
		// Could return custom fields here for advanced configuration.
		return array();
	}

	/**
	 * Get generator description.
	 *
	 * @return string
	 */
	public function get_description() {
		return __( 'Generate realistic products, customers, and orders for WooCommerce testing and development.', 'demo-data-pilot' );
	}
}
