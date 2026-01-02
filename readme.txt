=== Demo Data Pilot ===
Contributors: mithublue, cybercraftit
Tags: demo data, dummy data, test data, woocommerce, development
Requires at least: 5.8
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 1.0.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Generate realistic demo data for testing WordPress plugins. Supports WooCommerce, WP ERP, and easily extensible for other plugins.

== Description ==

Demo Data Pilot is a modular WordPress plugin designed to generate realistic dummy data for testing and development purposes. Perfect for developers, designers, and agencies who need to populate test sites with authentic-looking data.

= Key Features =

* **Modular Architecture** - Easy-to-extend generator system
* **WooCommerce Support** - Generate products, customers, and orders
* **WP ERP Support** - Generate employee records
* **Realistic Data** - Uses Faker library for authentic-looking content
* **Batch Processing** - Generate large datasets without timeouts
* **Progress Tracking** - Real-time AJAX progress updates
* **Smart Cleanup** - Remove all generated data with one click
* **Developer Friendly** - Simple API for adding custom generators

= Supported Plugins =

**Free Version:**
* WooCommerce (Products, Customers, Orders)
* WP ERP (Employees)

= For Developers =

Adding support for additional plugins is straightforward. Simply create a generator class that extends the abstract base class, and the plugin will automatically discover and register it.

See the `/generators/README.md` file for detailed instructions on creating custom generators.

= Privacy & Data =

This plugin generates fictional data using the Faker library. No real personal information is used or collected. All generated data can be easily removed using the built-in cleanup functionality.

== Installation ==

1. Upload the `demo-data-pilot` folder to `/wp-content/plugins/`
2. Run `composer install` in the plugin directory to install dependencies
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Navigate to **Tools → Demo Data Pilot** to start generating data

= Manual Installation =

1. Download the plugin ZIP file
2. Extract to `/wp-content/plugins/demo-data-pilot/`
3. SSH into your server and navigate to the plugin directory
4. Run `composer install` to install the Faker library
5. Activate the plugin in WordPress admin

== Frequently Asked Questions ==

= What plugins are supported? =

Currently, the free version supports WooCommerce and WP ERP. The modular architecture makes it easy to add support for additional plugins.

= How do I add support for another plugin? =

Create a generator class in the `/generators/` directory that extends `Abstract_Generator`. See the included `README.md` in the generators folder for detailed instructions.

= Is the generated data realistic? =

Yes! The plugin uses the Faker library to generate realistic names, addresses, emails, product descriptions, and more.

= Can I remove the generated data? =

Absolutely. Each data type has a "Cleanup" button that removes all generated records. The plugin tracks everything it creates for easy cleanup.

= Will this work on large sites? =

Yes. The plugin uses batch processing to handle large datasets without timing out. Progress is tracked in real-time via AJAX.

= Does this plugin collect any data? =

No. The plugin only generates fictional data locally on your WordPress installation. No data is sent to external servers.

= Can I use this on a production site? =

While technically possible, this plugin is designed for development and testing environments. We recommend using it only on staging or local development sites.

== Screenshots ==

1. Main admin interface showing available generators
2. WooCommerce generator with products, customers, and orders
3. Real-time progress tracking during generation
4. Activity log showing generation history
5. Generated products in WooCommerce

== Changelog ==

= 1.0.1 =
* Fixed: Removed automatic page reload after generation/cleanup
* Fixed: Clear logs AJAX handler
* Improved: More meaningful product names and descriptions
* Fixed: Cleanup button label issue

= 1.0.0 =
* Initial release
* WooCommerce generator (Products, Customers, Orders)
* WP ERP generator (Employees)
* Modular architecture for easy extensibility
* Batch processing with progress tracking
* Cleanup functionality
* Activity logging

== Upgrade Notice ==

= 1.0.1 =
Minor bug fixes and improved user experience. Update recommended.

= 1.0.0 =
Initial release of Demo Data Pilot.

== Developer Notes ==

= Adding Custom Generators =

Create a new file in `/generators/` directory:

`
<?php
namespace DemoDataPilot\Generators;

class My_Plugin_Generator extends \DemoDataPilot\Abstracts\Abstract_Generator {
    protected $plugin_slug = 'my-plugin';
    protected $plugin_name = 'My Plugin';
    protected $data_types = array( 'items' => 'Items' );
    
    public function is_plugin_active() {
        return class_exists( 'My_Plugin' );
    }
    
    public function generate( $type, $count, $args = array() ) {
        // Your generation logic
    }
    
    public function cleanup( $type, $ids = array() ) {
        // Your cleanup logic
    }
    
    public function get_form_fields( $type ) {
        return array();
    }
}
`

The plugin will automatically discover and register your generator.

= Hooks =

**Actions:**
* `demo_data_pilot_before_generate` - Fires before data generation
* `demo_data_pilot_after_generate` - Fires after data generation
* `demo_data_pilot_before_cleanup` - Fires before cleanup
* `demo_data_pilot_after_cleanup` - Fires after cleanup
* `demo_data_pilot_register_generators` - Register custom generators

**Filters:**
* `demo_data_pilot_generation_args` - Modify generation arguments
* `demo_data_pilot_batch_size` - Modify batch size
* `demo_data_pilot_is_pro_active` - Check if Pro version is active

== Third-Party Libraries ==

This plugin uses the following third-party library:

* **FakerPHP/Faker** (MIT License) - For generating realistic fake data
  https://github.com/FakerPHP/Faker

== Support ==

For support, feature requests, or bug reports, please visit:
https://github.com/your-username/demo-data-pilot

== Contributing ==

Contributions are welcome! Please submit pull requests or open issues on GitHub.
