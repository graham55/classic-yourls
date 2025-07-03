<?php
/**
Plugin Name: Classic YOURLS
Description: Connect your WordPress or ClassicPress site to your YOURLS instance.
Version: 2.3.0
Original Author: Chris Wiegman ( better-YOURLs )
Original Author URI: https://www.chriswiegman.com/
Contributor: Graham McKoen ( Fork Classic YOURLs )
Author: Chris Weigman - Graham McKoen
License: GPLv2
Original Copyright: 2015 Chris Wiegman  (email: info@chriswiegman.com)
Copyright: 2025 Graham McKoen & Chris Weigman (graham@cambmail.com - info@chriswiegman.com)
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define constants
define( 'BYOURLS_VERSION', '2.3.0' );
define( 'BYOURLS_PATH', plugin_dir_path( __FILE__ ) );
define( 'BYOURLS_URL', plugin_dir_url( __FILE__ ) );

// Load core classes
require_once BYOURLS_PATH . 'includes/class-better-yourls-admin.php';
require_once BYOURLS_PATH . 'includes/class-better-yourls-actions.php';

// Initialize
new Better_YOURLS_Admin();
new Better_YOURLS_Actions();

// Register shortcode
require_once BYOURLS_PATH . 'includes/shortcodes.php';