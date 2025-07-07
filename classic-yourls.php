<?php
/**
 * Plugin Name: Classic YOURLS
 * Plugin URI: https://github.com/graham55/classic-yourls
 * Description: Integrate your blog with YOURLS custom URL generator.
 * Version: 2.4.2
 * Author: Graham McKoen & Chris Wiegman
 * Text Domain: classic-yourls
 * Domain Path: /languages
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define plugin constants
define( 'CYOURLS_VERSION', '2.4.2' );
define( 'CYOURLS_URL', plugin_dir_url( __FILE__ ) );
define( 'CYOURLS_PATH', plugin_dir_path( __FILE__ ) );

// Include required files
require_once( CYOURLS_PATH . 'includes/class-classic-yourls-setup.php' );
require_once( CYOURLS_PATH . 'includes/class-classic-yourls-actions.php' );
require_once( CYOURLS_PATH . 'includes/class-classic-yourls-admin.php' );

// Register hooks
register_activation_hook( __FILE__, array( 'Classic_YOURLS_Setup', 'on_activate' ) );
register_deactivation_hook( __FILE__, array( 'Classic_YOURLS_Setup', 'on_deactivate' ) );

// Initialize the plugin
if ( is_admin() ) {
    new Classic_YOURLS_Admin();
}

new Classic_YOURLS_Actions();

// Load shortcode functionality after WordPress is fully loaded
add_action( 'init', 'classic_yourls_load_shortcode' );

function classic_yourls_load_shortcode() {
    $settings = get_option( 'classic_yourls' );
    if ( ! empty( $settings['shortcode_enabled'] ) ) {
        require_once( CYOURLS_PATH . 'includes/shortcodes.php' );
        
        // Enable YOURLS shortcodes in excerpts if both shortcodes and excerpt processing are enabled
        if ( ! empty( $settings['excerpt_shortcodes_enabled'] ) ) {
            add_filter( 'get_the_excerpt', 'classic_yourls_process_shortcodes_in_excerpt', 10 );
            add_filter( 'the_excerpt', 'classic_yourls_process_shortcodes_in_excerpt', 10 );
        }
    }
}

/**
 * Process YOURLS shortcodes in excerpts with safety checks
 * Only processes excerpts that contain YOURLS shortcodes
 *
 * @param string $excerpt The post excerpt
 * @return string The processed excerpt
 */
function classic_yourls_process_shortcodes_in_excerpt( $excerpt ) {
    // Safety check: ensure excerpt is not empty
    if ( empty( $excerpt ) ) {
        return $excerpt;
    }
    
    // Check for YOURLS shortcodes before processing - CORRECTED SHORTCODE NAME
    if ( has_shortcode( $excerpt, 'classicyourls_shortlink' ) ) {
        // Process only YOURLS shortcodes to avoid conflicts
        $processed_excerpt = do_shortcode( $excerpt );
        
        // Fallback: return original if processing fails
        return ! empty( $processed_excerpt ) ? $processed_excerpt : $excerpt;
    }
    
    return $excerpt;
}
