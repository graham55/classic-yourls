<?php
/**
 * Plugin Name: Classic YOURLS
 * Plugin URI: https://github.com/graham55/classic-yourls
 * Description: Integrate your blog with YOURLS custom URL generator.
 * Version: 2.4.0
 * Author: Graham McKoen & Chris Wiegman
 * Text Domain: classic-yourls
 * Domain Path: /languages
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define plugin constants
define( 'CYOURLS_VERSION', '2.4.0' );
define( 'CYOURLS_URL', plugin_dir_url( __FILE__ ) );
define( 'CYOURLS_PATH', plugin_dir_path( __FILE__ ) );

// Include required files
require_once( CYOURLS_PATH . 'includes/class-classic-yourls-setup.php' );
require_once( CYOURLS_PATH . 'includes/class-classic-yourls-actions.php' );
require_once( CYOURLS_PATH . 'includes/class-classic-yourls-admin.php' );

// Include shortcode functionality if enabled
$settings = get_option( 'classic_yourls' );
if ( ! empty( $settings['shortcode_enabled'] ) ) {
    require_once( CYOURLS_PATH . 'includes/shortcodes.php' );
}

// Register hooks
register_activation_hook( __FILE__, array( 'Classic_YOURLS_Setup', 'on_activate' ) );
register_deactivation_hook( __FILE__, array( 'Classic_YOURLS_Setup', 'on_deactivate' ) );

// Initialize the plugin
if ( is_admin() ) {
    new Classic_YOURLS_Admin();
}

new Classic_YOURLS_Actions();
