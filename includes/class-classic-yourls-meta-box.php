<?php
/**
 * Classic YOURLS Meta Box
 *
 * @package Classic_YOURLS
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class Classic_YOURLS_Meta_Box
 */
class Classic_YOURLS_Meta_Box {

    /**
     * The saved Classic YOURLS settings
     *
     * @since 0.0.1
     *
     * @var array|bool
     */
    protected $settings;

    /**
     * Constructor
     */
    public function __construct() {
        $this->settings = get_option( 'classic_yourls' );
        
        // Backward compatibility - migrate from old option name
        if ( ! $this->settings ) {
            $old_settings = get_option( 'better_yourls' );
            if ( $old_settings ) {
                update_option( 'classic_yourls', $old_settings );
                $this->settings = $old_settings;
            }
        }
        
        // Only add meta box if API is configured
        if ( isset( $this->settings['domain'], $this->settings['key'] ) && 
             '' !== $this->settings['domain'] && 
             '' !== $this->settings['key'] ) {
            add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
        }
    }

    /**
     * Add meta boxes
     */
    public function add_meta_boxes() {
        global $post;

        if ( ! $post instanceof WP_Post ) {
            return;
        }

        $post_type = get_post_type( $post->ID );

        // Check if this post type is excluded
        if ( false === $post_type || 
             ( isset( $this->settings['post_types'] ) && 
               in_array( $post_type, $this->settings['post_types'], true ) ) ) {
            return;
        }

        add_meta_box(
            'yourls_keyword', // Use same ID as actions class to replace it
            esc_html__( 'YOURLS Keyword', 'classic-yourls' ),
            array( $this, 'render_meta_box' ),
            $post_type,
            'side',
            'core'
        );
    }

    /**
     * Render enhanced meta box
     */
    public function render_meta_box( $post ) {
        // Get existing shortlink using the global helper function
        $shortlink = classic_yourls_get_link( $post->ID );
        
        // Use same nonce name as actions class for compatibility
        wp_nonce_field( 'classic_yourls_save_post', 'classic_yourls_nonce' );
        
        // Input field for keyword (same field name as actions class)
        echo '<p>';
        echo '<label for="classic-yourls-keyword">' . esc_html__( 'Keyword:', 'classic-yourls' ) . '</label>';
        
        $readonly = $shortlink ? 'readonly="readonly" ' : '';
        echo '<input type="text" id="classic-yourls-keyword" name="classic-yourls-keyword" value="' . esc_attr( $shortlink ) . '" ' . $readonly . 'style="width: 100%;" />';
        echo '</p>';

        echo '<p><em>' . esc_html__( 'If a short-url doesn\'t yet exist for this post you can enter a keyword above. If it already exists it will be displayed.', 'classic-yourls' ) . '</em></p>';

        // Display current short link status
        echo '<div style="background: #f9f9f9; padding: 10px; border-left: 4px solid #0073aa; margin: 10px 0;">';
        echo '<p><strong>' . esc_html__( 'Current Short Link:', 'classic-yourls' ) . '</strong></p>';
        if ( $shortlink ) {
            echo '<p><code style="word-break: break-all; background: #fff; padding: 4px 8px; border-radius: 3px;">' . esc_html( $shortlink ) . '</code></p>';
            echo '<p><small style="color: #666;">' . esc_html__( 'Link is already generated and saved.', 'classic-yourls' ) . '</small></p>';
        } else {
            echo '<p><em style="color: #666;">' . esc_html__( 'No short link generated yet. Will be created when post is saved.', 'classic-yourls' ) . '</em></p>';
        }
        echo '</div>';

        // Display Post ID
        echo '<p><strong>' . esc_html__( 'Post ID:', 'classic-yourls' ) . '</strong> ' . intval( $post->ID ) . '</p>';

        // Enhanced shortcode examples section
        echo '<div style="background: #f0f8ff; padding: 12px; border-left: 4px solid #0073aa; margin: 15px 0;">';
        echo '<p><strong>' . esc_html__( '📝 Shortcode Examples:', 'classic-yourls' ) . '</strong></p>';
        
        echo '<div style="margin-bottom: 10px;">';
        echo '<code style="background: #fff; padding: 4px 8px; border-radius: 3px; display: block; margin: 5px 0;">[classicyourls_shortlink id="' . intval( $post->ID ) . '"]</code>';
        echo '<small style="color: #666; margin-left: 8px;">' . esc_html__( '→ Display short link for this post', 'classic-yourls' ) . '</small>';
        echo '</div>';

        echo '<div style="margin-bottom: 10px;">';
        echo '<code style="background: #fff; padding: 4px 8px; border-radius: 3px; display: block; margin: 5px 0;">[classicyourls_shortlink id="' . intval( $post->ID ) . '" text="Read More"]</code>';
        echo '<small style="color: #666; margin-left: 8px;">' . esc_html__( '→ Custom link text', 'classic-yourls' ) . '</small>';
        echo '</div>';

        echo '<div style="margin-bottom: 10px;">';
        echo '<code style="background: #fff; padding: 4px 8px; border-radius: 3px; display: block; margin: 5px 0;">[classicyourls_shortlink]</code>';
        echo '<small style="color: #666; margin-left: 8px;">' . esc_html__( '→ Auto-detect current post (when used in this post)', 'classic-yourls' ) . '</small>';
        echo '</div>';
        echo '</div>';

        // Feature status indicators
        $this->render_feature_status();
    }

    /**
     * Render feature status indicators
     */
    private function render_feature_status() {
        // Check if shortcodes are enabled
        if ( empty( $this->settings['shortcode_enabled'] ) ) {
            echo '<div style="background: #fff3cd; padding: 10px; border-left: 4px solid #ffc107; margin: 15px 0;">';
            echo '<p><strong>⚠️ ' . esc_html__( 'Shortcodes Disabled', 'classic-yourls' ) . '</strong></p>';
            echo '<p>' . esc_html__( 'Shortcodes are currently disabled. Enable them in', 'classic-yourls' ) . ' ';
            echo '<a href="' . esc_url( admin_url( 'options-general.php?page=classic_yourls' ) ) . '" target="_blank">';
            echo esc_html__( 'Classic YOURLS Settings', 'classic-yourls' ) . '</a> ';
            echo esc_html__( 'to use the shortcode examples above.', 'classic-yourls' ) . '</p>';
            echo '</div>';
        } else {
            // Show active features
            echo '<div style="background: #d4edda; padding: 10px; border-left: 4px solid #28a745; margin: 15px 0;">';
            echo '<p><strong>✅ ' . esc_html__( 'Active Features:', 'classic-yourls' ) . '</strong></p>';
            echo '<ul style="margin: 5px 0 5px 20px;">';
            echo '<li>' . esc_html__( 'Shortcodes enabled', 'classic-yourls' ) . '</li>';
            
            if ( ! empty( $this->settings['excerpt_shortcodes_enabled'] ) ) {
                echo '<li>' . esc_html__( 'Shortcodes work in excerpts', 'classic-yourls' ) . '</li>';
            }
            
            if ( ! empty( $this->settings['replace_excerpt_readmore'] ) ) {
                $replacement_text = ! empty( $this->settings['excerpt_replacement_text'] ) 
                    ? $this->settings['excerpt_replacement_text'] 
                    : 'Read More';
                echo '<li>' . sprintf( 
                    esc_html__( 'Excerpt "Read More" replacement active (text: "%s")', 'classic-yourls' ),
                    esc_html( $replacement_text )
                ) . '</li>';
            }
            echo '</ul>';
            echo '</div>';
        }

        // Show tips for inactive features
        if ( ! empty( $this->settings['shortcode_enabled'] ) ) {
            $inactive_features = array();
            
            if ( empty( $this->settings['excerpt_shortcodes_enabled'] ) ) {
                $inactive_features[] = esc_html__( 'Shortcodes in excerpts', 'classic-yourls' );
            }
            
            if ( empty( $this->settings['replace_excerpt_readmore'] ) ) {
                $inactive_features[] = esc_html__( 'Excerpt "Read More" replacement', 'classic-yourls' );
            }
            
            if ( ! empty( $inactive_features ) ) {
                echo '<div style="background: #d1ecf1; padding: 10px; border-left: 4px solid #17a2b8; margin: 15px 0;">';
                echo '<p><strong>💡 ' . esc_html__( 'Available Features:', 'classic-yourls' ) . '</strong></p>';
                echo '<p>' . esc_html__( 'You can also enable:', 'classic-yourls' ) . '</p>';
                echo '<ul style="margin: 5px 0 5px 20px;">';
                foreach ( $inactive_features as $feature ) {
                    echo '<li>' . $feature . '</li>';
                }
                echo '</ul>';
                echo '<p><a href="' . esc_url( admin_url( 'options-general.php?page=classic_yourls' ) ) . '" target="_blank">';
                echo esc_html__( 'Configure in Classic YOURLS Settings →', 'classic-yourls' ) . '</a></p>';
                echo '</div>';
            }
        }
    }
}
