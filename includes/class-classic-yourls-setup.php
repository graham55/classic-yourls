<?php
/**
 * Classic YOURLS Setup
 *
 * @package Classic_YOURLS
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * YOURLS setup.
 *
 * @since 0.0.1
 */
class Classic_YOURLS_Setup {

    /**
     * Classic YOURLS constructor.
     *
     * @since 0.0.1
     *
     * @param string $case The case to execute
     *
     * @return Classic_YOURLS_Setup
     */
    function __construct( $case = false ) {

        if ( ! $case ) {
            wp_die( esc_html__( 'Invalid setup case provided.', 'classic-yourls' ) );
        }

        switch ( $case ) {
            case 'activate': // activate plugin
                $this->activate_execute();
                break;

            case 'deactivate': // deactivate plugin
                $this->deactivate_execute();
                break;

            case 'uninstall': // uninstall plugin
                $this->uninstall_execute();
                break;

            default:
                wp_die( esc_html__( 'Unknown setup case.', 'classic-yourls' ) );
                break;
        }
    }

    /**
     * Entry point for activation
     *
     * @since 0.0.1
     *
     * @return void
     */
    public static function on_activate() {
        new Classic_YOURLS_Setup( 'activate' );
    }

    /**
     * Entry point for deactivation
     *
     * @since 0.0.1
     *
     * @return void
     */
    public static function on_deactivate() {
        if ( defined( 'CLASSIC_YOURLS_DEVELOPMENT' ) && CLASSIC_YOURLS_DEVELOPMENT == true ) {
            $case = 'uninstall';
        } else {
            $case = 'deactivate';
        }

        new Classic_YOURLS_Setup( $case );
    }

    /**
     * Entry point for uninstall
     *
     * @since 0.0.1
     *
     * @return void
     */
    public static function on_uninstall() {
        if ( __FILE__ != WP_UNINSTALL_PLUGIN ) { // verify they actually clicked uninstall
            return;
        }

        new Classic_YOURLS_Setup( 'uninstall' );
    }

    /**
     * Execute Activation functions
     *
     * @since 0.0.1
     *
     * @return void
     */
    function activate_execute() {
        // Migrate old settings to new option name for backward compatibility
        $old_settings = get_option( 'better_yourls' );
        if ( $old_settings && ! get_option( 'classic_yourls' ) ) {
            update_option( 'classic_yourls', $old_settings );
        }

        // Set default settings for new installations
        $current_settings = get_option( 'classic_yourls' );
        if ( ! $current_settings ) {
            $default_settings = array(
                'domain' => '',
                'key' => '',
                'https' => false,
                'https_ignore' => false,
                'private_post_types' => false,
                'shortcode_enabled' => true,
                'excerpt_shortcodes_enabled' => false,
                'replace_excerpt_readmore' => false,
                'excerpt_replacement_text' => 'Read More',
                'post_types' => array()
            );
            update_option( 'classic_yourls', $default_settings );
        }

        // Flush rewrite rules to ensure proper URL handling
        flush_rewrite_rules();
    }

    /**
     * Execute Update functions
     *
     * @since 0.0.1
     *
     * @return void
     */
    function update_execute() {
        // Reserved for future version updates
        // This method can be used for database schema updates or settings migrations
    }

    /**
     * Execute Deactivation functions
     *
     * @since 0.0.1
     *
     * @return void
     */
    function deactivate_execute() {
        // Flush rewrite rules to clean up any custom rules
        flush_rewrite_rules();
        
        // Clear any cached data
        wp_cache_flush();
    }

    /**
     * Execute Uninstall functions
     *
     * @since 0.0.1
     *
     * @return void
     */
    function uninstall_execute() {
        // Clean both old and new option names for complete cleanup
        delete_option( 'classic_yourls' );
        delete_option( 'better_yourls' ); // Keep for backward compatibility cleanup
        
        // Remove all post metadata for short links
        delete_metadata( 'post', null, '_classic_yourls_short_link', null, true );
        delete_metadata( 'post', null, '_better_yourls_short_link', null, true ); // Keep for backward compatibility cleanup
        
        // Clear any cached data
        wp_cache_flush();
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
}
