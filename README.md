# Demo Data Pilot

A modular, extensible WordPress plugin for generating realistic dummy/demo data for various WordPress plugins.

## Features

- **Modular Architecture**: Easy-to-extend generator system
- **Multiple Plugin Support**: WooCommerce, WP ERP, and more
- **Batch Processing**: Generate large datasets without timeouts
- **Progress Tracking**: Real-time AJAX progress updates
- **Smart Cleanup**: Remove all generated data with one click
- **Realistic Data**: Uses Faker library for authentic-looking data

## Installation

1. Upload the `demo-data-pilot` folder to `/wp-content/plugins/`
2. Run `composer install` in the plugin directory to install dependencies
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Navigate to **Tools → Demo Data Pilot** to start generating data

## Supported Plugins

### Free Version
- **WooCommerce**: Products, Customers, Orders
- **WP ERP**: Employees

### Pro Version
- Easy Digital Downloads
- LearnDash
- MemberPress
- And many more...

## Adding Custom Generators

1. Create a new file in the `generators` directory:
   ```php
   // generators/class-my-plugin-generator.php
   namespace DemoDataPilot\Generators;
   
   class My_Plugin_Generator extends \DemoDataPilot\Abstracts\Abstract_Generator {
       protected $plugin_slug = 'my-plugin';
       protected $plugin_name = 'My Plugin';
       protected $data_types = array(
           'items' => 'Items'
       );
       
       public function is_plugin_active() {
           return class_exists( 'My_Plugin' );
       }
       
       public function generate( $type, $count, $args = array() ) {
           // Your generation logic here
       }
       
       public function cleanup( $type, $ids = array() ) {
           // Your cleanup logic here
       }
       
       public function get_form_fields( $type ) {
           // Return form field configuration
       }
   }
   ```

2. The plugin will automatically discover and register your generator!

## Requirements

- PHP 7.4 or higher
- WordPress 5.8 or higher
- Composer (for dependency management)

## License

GPL v2 or later
