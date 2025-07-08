<?php
/**
 * Classic YOURLS Actions
 *
 * @package Classic_YOURLS
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class Classic_YOURLS_Actions
 */
class Classic_YOURLS_Actions {

    /**
     * The saved Classic YOURLS settings
     *
     * @since 0.0.1
     *
     * @var array|bool
     */
    protected $settings;

    /**
     * Classic YOURLS constructor.
     *
     * Register actions and setup local items for the plugin.
     *
     * @since 0.0.1
     *
     * @return Classic_YOURLS_Actions
     */
    public function __construct() {
        // Set default options
        $this->settings = get_option( 'classic_yourls' );
        
        // Backward compatibility - migrate from old option name
        if ( ! $this->settings ) {
            $old_settings = get_option( 'better_yourls' );
            if ( $old_settings ) {
                update_option( 'classic_yourls', $old_settings );
                $this->settings = $old_settings;
            }
        }

        // Add filters and actions if we've set API info
        if ( isset( $this->settings['domain'], $this->settings['key'] ) && 
             '' !== $this->settings['domain'] && 
             '' !== $this->settings['key'] ) {
            
            // NOTE: Meta box functionality removed - handled by Classic_YOURLS_Meta_Box class
            add_action( 'admin_bar_menu', array( $this, 'action_admin_bar_menu' ), 100 );
            add_action( 'save_post', array( $this, 'action_save_post' ), 11 );
            add_action( 'wp_enqueue_scripts', array( $this, 'action_wp_enqueue_scripts' ) );
            add_action( 'transition_post_status', array( $this, 'action_transition_post_status' ), 9, 3 );
            add_filter( 'get_shortlink', array( $this, 'filter_get_shortlink' ), 10, 3 );
            add_filter( 'pre_get_shortlink', array( $this, 'filter_pre_get_shortlink' ), 11, 2 );
            add_filter( 'sharing_permalink', array( $this, 'filter_sharing_permalink' ), 10, 2 );
        }
    }

    /**
     * Add link to admin bar.
     *
     * Adds a "Classic YOURLS" menu to the admin bar to access the shortlink and stats easily from the front end.
     *
     * @since 0.0.1
     *
     * @return void
     */
    public function action_admin_bar_menu() {
        global $wp_admin_bar, $post;

        if ( ! ( $post instanceof WP_Post ) || ! isset( $post->ID ) ) {
            return;
        }

        $post_type = get_post_type( $post->ID );

        if ( false === $post_type || 
             ( isset( $this->settings['post_types'] ) && 
               in_array( $post_type, $this->settings['post_types'], true ) ) ) {
            return;
        }

        $yourls_url = wp_get_shortlink( $post->ID, 'query' );

        if ( is_singular() && ! is_preview() && current_user_can( 'edit_post', $post->ID ) ) {
            $stats_url = $yourls_url . '+';

            $wp_admin_bar->remove_menu( 'get-shortlink' );

            $wp_admin_bar->add_menu( array(
                'id' => 'classic_yourls',
                'title' => esc_html__( 'Classic YOURLS', 'classic-yourls' ),
            ) );

            $wp_admin_bar->add_menu( array(
                'parent' => 'classic_yourls',
                'id' => 'classic_yourls-link',
                'title' => esc_html__( 'YOURLS Link', 'classic-yourls' ),
            ) );

            $wp_admin_bar->add_menu( array(
                'parent' => 'classic_yourls',
                'id' => 'classic_yourls-stats',
                'title' => esc_html__( 'Link Stats', 'classic-yourls' ),
                'href' => $stats_url,
                'meta' => array( 'target' => '_blank' ),
            ) );
        }
    }

    /**
     * Save keyword and create shortlink on post save.
     *
     * @since 1.0.3
     *
     * @param int $post_id The post ID.
     *
     * @return void
     */
    public function action_save_post( $post_id ) {
        if ( ! $this->_check_valid_post( $post_id ) ) {
            return;
        }

        $this->_generate_post_on_save( $post_id );
    }

    /**
     * Save on status transition.
     *
     * @since 2.1.0
     *
     * @param string  $new_status New post status.
     * @param string  $old_status Old post status.
     * @param WP_Post $post       Post object.
     *
     * @return void
     */
    public function action_transition_post_status( $new_status, $old_status, $post ) {
        if ( false === $this->_check_valid_post( $post->ID ) || 'publish' !== $new_status ) {
            return;
        }

        $this->_generate_post_on_save( $post->ID );
    }

    /**
     * Validate post type and status.
     *
     * @since 2.1.0
     *
     * @param int $post_id The post ID.
     *
     * @return bool
     */
    protected function _check_valid_post( $post_id ) {
        $post_type = get_post_type( $post_id );

        if (
            false === $post_type ||
            ( isset( $this->settings['post_types'] ) && in_array( $post_type, $this->settings['post_types'], true ) ) ||
            'nav_menu_item' === $post_type ||
            ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) ||
            ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ||
            ( defined( 'DOING_CRON' ) && DOING_CRON )
        ) {
            return false;
        }

        /**
         * Filter Classic YOURLS post statuses
         *
         * The post statuses upon which a URL should be generated.
         *
         * @since 2.0.0
         *
         * @param array Array of post statuses.
         */
        $post_statuses = apply_filters( 'classic_yourls_post_statuses', array( 'publish', 'future' ) );

        if ( ! in_array( get_post_status( $post_id ), $post_statuses, true ) ) {
            return false;
        }

        return true;
    }

    /**
     * Actually generate the YOURLS link on save.
     *
     * @since 2.1.0
     *
     * @param int $post_id The post ID.
     *
     * @return void
     */
    protected function _generate_post_on_save( $post_id ) {
        if ( defined( 'REST_REQUEST' ) ) {
            return;
        }

        if (
            ! $this->_check_valid_post( $post_id ) ||
            ( ! isset( $_POST['classic_yourls_nonce'] ) || 
              ! wp_verify_nonce( $_POST['classic_yourls_nonce'], 'classic_yourls_save_post' ) )
        ) {
            wp_die( esc_html__( 'Security Error', 'classic-yourls' ) );
        }

        $keyword = '';
        if ( isset( $_POST['classic-yourls-keyword'] ) ) {
            $keyword = sanitize_title( trim( $_POST['classic-yourls-keyword'] ) );
        }

        $keyword = apply_filters( 'classic_yourls_keyword', $keyword, $post_id );
        $link = $this->create_yourls_url( $post_id, $keyword, '', 'save_post' );

        // Keyword would be a duplicate so use a standard one.
        if ( '' !== $keyword && ! $link ) {
            $link = $this->create_yourls_url( $post_id, '', '', 'save_post' );
        }

        // Save the short URL only if it was generated correctly
        if ( $link ) {
            update_post_meta( $post_id, '_classic_yourls_short_link', $link );
            // Also update old meta key for backward compatibility
            update_post_meta( $post_id, '_better_yourls_short_link', $link );
        }
    }

    /**
     * Call YOURLS API to create a shortlink.
     *
     * Creates YOURLS link if not in post meta and saves new link to post meta where appropriate.
     *
     * @since 0.0.1
     *
     * @param int    $post_id The current post id.
     * @param string $keyword Optional keyword for shortlink.
     * @param string $title   Optional title for shortlink.
     * @param string $hook    The hook from which this function was called.
     *
     * @return bool|string The yourls shortlink or false.
     */
    public function create_yourls_url( $post_id, $keyword = '', $title = '', $hook = '' ) {
        if ( is_preview() && ! is_admin() ) {
            return false;
        }

        // Use the global helper function for consistency
        $existing = classic_yourls_get_link( $post_id );
        if ( $existing ) {
            return $existing;
        }

        $https = ( isset( $this->settings['https'] ) && true === $this->settings['https'] ) ? 's' : '';
        $yourls_url = esc_url_raw( 'http' . $https . '://' . $this->settings['domain'] . '/yourls-api.php' );

        $timestamp = current_time( 'timestamp' );

        $args = array(
            'body' => array(
                'title' => ( '' === trim( $title ) ) ? get_the_title( $post_id ) : $title,
                'timestamp' => $timestamp,
                'signature' => md5( $timestamp . $this->settings['key'] ),
                'action' => 'shorturl',
                'url' => get_permalink( $post_id ),
                'format' => 'JSON',
            ),
            'timeout' => 30,
            'user-agent' => 'Classic YOURLS WordPress Plugin/' . CYOURLS_VERSION,
        );

        if ( '' !== $keyword ) {
            $args['body']['keyword'] = sanitize_title( $keyword );
        }

        if ( isset( $this->settings['https_ignore'] ) && true === $this->settings['https_ignore'] ) {
            $args['sslverify'] = false;
        }

        $response = wp_remote_post( $yourls_url, $args );

        if ( is_wp_error( $response ) ) {
            error_log( 'Classic YOURLS API Error: ' . $response->get_error_message() );
            return false;
        }

        $response_code = wp_remote_retrieve_response_code( $response );
        if ( 200 !== $response_code ) {
            error_log( 'Classic YOURLS API HTTP Error: ' . $response_code );
            return false;
        }

        $short_link = wp_remote_retrieve_body( $response );
        $short_link = trim( $short_link );

        if ( empty( $short_link ) ) {
            return false;
        }

        // Handle JSON response format
        if ( 'JSON' === $args['body']['format'] ) {
            $json_data = json_decode( $short_link, true );
            if ( isset( $json_data['shorturl'] ) ) {
                $short_link = $json_data['shorturl'];
            } elseif ( isset( $json_data['status'] ) && 'fail' === $json_data['status'] ) {
                error_log( 'Classic YOURLS API Error: ' . ( $json_data['message'] ?? 'Unknown error' ) );
                return false;
            }
        }

        $url = esc_url( $short_link );

        if ( $this->validate_url( $url ) ) {
            $url = apply_filters( 'classic_yourls_shortlink', $url, $post_id, $hook );

            if ( false === $url ) {
                return false;
            }

            $url = esc_url_raw( $url );
            update_post_meta( $post_id, '_classic_yourls_short_link', $url );
            // Also update old meta key for backward compatibility
            update_post_meta( $post_id, '_better_yourls_short_link', $url );

            return $url;
        }

        return false;
    }

    /**
     * Validates a URL
     *
     * A slightly more complex version of a URL validator.
     *
     * @since 1.2
     *
     * @param string $url The url to validate.
     *
     * @return bool True if valid url else false.
     */
    private function validate_url( $url ) {
        $pattern = '/^(http|https|ftp):\/\/([A-Z0-9][A-Z0-9_-]*(?:\.[A-Z0-9][A-Z0-9_-]*)+):?(\d+)?\/?/i';
        return (bool) preg_match( $pattern, $url );
    }

    /**
     * Filter wp shortlink before display.
     *
     * Filters the default WordPress shortlink
     *
     * @since 0.0.1
     *
     * @param bool $short_link The shortlink to filter (defaults to false).
     * @param int  $id         The post id.
     *
     * @return bool The shortlink or false.
     */
    public function filter_get_shortlink( $short_link, $id ) {
        if ( ! $this->_check_valid_post( $id ) ) {
            return $short_link;
        }

        $link = $this->create_yourls_url( $id, '', '', 'get_shortlink' );

        return $link ? $link : $short_link;
    }

    /**
     * Filter wp shortlink before display.
     *
     * Filters the default WordPress shortlink
     *
     * @since 0.0.1
     *
     * @param bool $short_link The shortlink to filter (defaults to false).
     * @param int  $id         The post id.
     *
     * @return bool The shortlink or false.
     */
    public function filter_pre_get_shortlink( $short_link, $id ) {
        if ( ! $this->_check_valid_post( $id ) ) {
            return $short_link;
        }

        // Use the global helper function for consistency
        $link = classic_yourls_get_link( $id );

        return $link ? $link : $short_link;
    }

    /**
     * Adds the shortlink to Jetpack Sharing.
     *
     * If you're using JetPack for sharing links when publishing a post this will make sure the shared link uses your shortlink.
     *
     * @since 0.0.1
     *
     * @param string $link    The original link.
     * @param int    $post_id The post id.
     *
     * @return string The link to share.
     */
    public function filter_sharing_permalink( $link, $post_id ) {
        if ( ! $this->_check_valid_post( $post_id ) ) {
            return $link;
        }

        $yourls_shortlink = $this->create_yourls_url( $post_id, '', '', 'sharing_permalink' );

        return ( $yourls_shortlink ) ? $yourls_shortlink : $link;
    }

    /**
     * Enqueue script with admin bar.
     *
     * @since 0.0.1
     *
     * @return void
     */
    public function action_wp_enqueue_scripts() {
        global $post;

        if ( is_admin_bar_showing() && isset( $post->ID ) && current_user_can( 'edit_post', $post->ID ) ) {
            $min = ( defined( 'SCRIPT_DEBUG' ) && true === SCRIPT_DEBUG ) ? '' : '.min';

            wp_register_script( 'classic_yourls', CYOURLS_URL . 'assets/js/classic-yourls' . $min . '.js', array( 'jquery' ), CYOURLS_VERSION );
            wp_enqueue_script( 'classic_yourls' );

            wp_localize_script( 'classic_yourls', 'classic_yourls', array(
                'text' => esc_html__( 'Your Classic YOURLS short link is: ', 'classic-yourls' ),
                'yourls_url' => wp_get_shortlink( $post->ID ),
            ) );
        }
    }
}
