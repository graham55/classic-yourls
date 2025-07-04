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
     * Cleanup options
     *
     * Deletes Classic YOURLS' options and post_meta.
     *
     * @since 2.0.0
     *
     * @return void
     */
    protected static function clean_data() {
        // Clean both old and new option names for backward compatibility
        delete_option( 'classic_yourls' );
        delete_option( 'better_yourls' ); // Keep for backward compatibility
        delete_metadata( 'post', null, '_classic_yourls_short_link', null, true );
        delete_metadata( 'post', null, '_better_yourls_short_link', null, true ); // Keep for backward compatibility
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
