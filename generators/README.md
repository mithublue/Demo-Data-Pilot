# Adding Custom Generators

This guide explains how to create custom generators for Demo Data Pilot.

## Quick Start

1. Create a new file in the `generators` directory following the naming convention: `class-PLUGIN-NAME-generator.php`

2. Use this template:

```php
<?php
namespace DemoDataPilot\Generators;

use DemoDataPilot\Abstracts\Abstract_Generator;

class Your_Plugin_Generator extends Abstract_Generator {
    
    protected $plugin_slug = 'your-plugin';
    protected $plugin_name = 'Your Plugin';
    protected $data_types = array(
        'items' => 'Items',
        'posts' => 'Posts',
    );
    
    public function is_plugin_active() {
        // Check if your plugin is active
        return class_exists( 'Your_Plugin_Class' );
    }
    
    public function generate( $type, $count, $args = array() ) {
        // Validate first
        $validation = $this->validate_generation_args( $type, $count, $args );
        if ( true !== $validation ) {
            return new \WP_Error( 'validation_failed', $validation );
        }
        
        $generated = array();
        
        for ( $i = 0; $i < $count; $i++ ) {
            // Create your record here
            $id = $this->create_item();
            
            if ( $id ) {
                // Track the generated record
                $this->track_generated( $type, $id );
                $generated[] = $id;
                
                // Log success
                $this->log( "Generated item #$id" );
            }
        }
        
        return $generated;
    }
    
    public function cleanup( $type, $ids = array() ) {
        if ( empty( $ids ) ) {
            return true;
        }
        
        foreach ( $ids as $id ) {
            // Delete your record
            wp_delete_post( $id, true );
        }
        
        return true;
    }
    
    public function get_form_fields( $type ) {
        // Optional: Return custom form fields for admin UI
        return array();
    }
}
```

3. The plugin will automatically discover and register your generator!

## Available Helper Methods

### Faker Instance
Access the Faker library via `$this->faker`:

```php
$this->faker->name();
$this->faker->email();
$this->faker->sentence();
$this->faker->paragraph();
$this->faker->numberBetween( 1, 100 );
$this->faker->dateTimeBetween( '-1 year', 'now' );
```

### Logging
```php
$this->log( 'Message', 'info' );   // info, success, warning, error
```

### Tracking
```php
$this->track_generated( 'type', $id, $metadata );
```

## Best Practices

1. **Always validate** using `$this->validate_generation_args()`
2. **Always track** generated records using `$this->track_generated()`
3. **Always log** important events
4. **Handle exceptions** with try-catch blocks
5. **Check dependencies** in `is_plugin_active()`

## Example: Blog Posts Generator

```php
private function generate_posts( $count, $args ) {
    $generated = array();
    
    for ( $i = 0; $i < $count; $i++ ) {
        $post_data = array(
            'post_title'   => $this->faker->sentence(),
            'post_content' => $this->faker->paragraphs( 5, true ),
            'post_status'  => 'publish',
            'post_type'    => 'post',
            'post_author'  => 1,
        );
        
        $post_id = wp_insert_post( $post_data );
        
        if ( $post_id ) {
            $this->track_generated( 'posts', $post_id );
            $generated[] = $post_id;
        }
    }
    
    return $generated;
}
```

## Need Help?

- Check existing generators in the `generators` directory
- Review the Abstract_Generator class in `includes/abstracts/`
- Visit our documentation at [plugin website]
