<?php
/**
 * Uninstall Classic YOURLS
 *
 * @package Classic_YOURLS
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Exit if not uninstalling
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

/**
 * Class Classic_YOURLS_Uninstaller
 */
class Classic_YOURLS_Uninstaller {

    /**
     * Initialize uninstaller
     *
     * Perform some checks to make sure plugin can/should be uninstalled
     *
     * @since 2.0.0
     *
     * @return Classic_YOURLS_Uninstaller
     */
    public function __construct() {
        // Exit if accessed directly.
        if ( ! defined( 'ABSPATH' ) ) {
            $this->exit_uninstaller();
        }

        // Not uninstalling.
        if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
            $this->exit_uninstaller();
        }

        // Not uninstalling.
        if ( ! WP_UNINSTALL_PLUGIN ) {
            $this->exit_uninstaller();
        }

        // Not uninstalling this plugin.
        if ( dirname( WP_UNINSTALL_PLUGIN ) !== dirname( plugin_basename( __FILE__ ) ) ) {
            $this->exit_uninstaller();
        }

        // Uninstall Classic YOURLS.
        self::clean_data();
    }

    /**
     * Cleanup options and data
     *
     * Deletes Classic YOURLS' options, post_meta, and cached data.
     *
     * @since 2.0.0
     *
     * @return void
     */
    protected static function clean_data() {
        global $wpdb;

        // Clean both old and new option names for backward compatibility
        delete_option( 'classic_yourls' );
        delete_option( 'better_yourls' ); // Keep for backward compatibility cleanup

        // Remove all post metadata for short links (more efficient than delete_metadata)
        $wpdb->delete(
            $wpdb->postmeta,
            array(
                'meta_key' => '_classic_yourls_short_link'
            )
        );

        $wpdb->delete(
            $wpdb->postmeta,
            array(
                'meta_key' => '_better_yourls_short_link'
            )
        );

        // Clean up any keyword metadata that might exist
        $wpdb->delete(
            $wpdb->postmeta,
            array(
                'meta_key' => '_classic_yourls_keyword'
            )
        );

        $wpdb->delete(
            $wpdb->postmeta,
            array(
                'meta_key' => '_better_yourls_keyword'
            )
        );

        // Clear any cached data
        wp_cache_flush();

        // Clean up any transients that might be set
        self::clean_transients();

        // Log the uninstall for debugging purposes
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            error_log( 'Classic YOURLS: Plugin data cleaned during uninstall' );
        }
    }

    /**
     * Clean up transients
     *
     * Remove any transients that might be set by the plugin
     *
     * @since 2.4.2
     *
     * @return void
     */
    protected static function clean_transients() {
        global $wpdb;

        // Delete any transients that start with classic_yourls or better_yourls
        $wpdb->query(
            "DELETE FROM {$wpdb->options} 
             WHERE option_name LIKE '_transient_classic_yourls_%' 
             OR option_name LIKE '_transient_timeout_classic_yourls_%'
             OR option_name LIKE '_transient_better_yourls_%' 
             OR option_name LIKE '_transient_timeout_better_yourls_%'"
        );

        // For multisite, also clean site transients
        if ( is_multisite() ) {
            $wpdb->query(
                "DELETE FROM {$wpdb->sitemeta} 
                 WHERE meta_key LIKE '_site_transient_classic_yourls_%' 
                 OR meta_key LIKE '_site_transient_timeout_classic_yourls_%'
                 OR meta_key LIKE '_site_transient_better_yourls_%' 
                 OR meta_key LIKE '_site_transient_timeout_better_yourls_%'"
            );
        }
    }

    /**
     * Exit uninstaller
     *
     * Gracefully exit the uninstaller if we should not be here
     *
     * @since 2.0.0
     *
     * @return void
     */
    protected function exit_uninstaller() {
        status_header( 404 );
        exit;
    }
}

new Classic_YOURLS_Uninstaller();
