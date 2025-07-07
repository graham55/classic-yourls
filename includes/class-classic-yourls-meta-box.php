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
     * Constructor
     */
    public function __construct() {
        add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
        add_action( 'save_post', array( $this, 'save_meta_box' ) );
    }

    /**
     * Add meta boxes
     */
    public function add_meta_boxes() {
        $post_types = get_post_types( array( 'public' => true ), 'names' );
        
        foreach ( $post_types as $post_type ) {
            add_meta_box(
                'classic_yourls_meta_box',
                esc_html__( 'YOURLS Keyword', 'classic-yourls' ),
                array( $this, 'render_meta_box' ),
                $post_type,
                'side'
            );
        }
    }

    /**
     * Render meta box
     */
    public function render_meta_box( $post ) {
        $keyword = get_post_meta( $post->ID, '_classic_yourls_keyword', true );
        $shortlink = classic_yourls_get_link( $post->ID );
        
        wp_nonce_field( 'classic_yourls_save_meta_box', 'classic_yourls_meta_box_nonce' );
        
        echo '<p>';
        echo '<label for="classic_yourls_keyword">' . esc_html__( 'Keyword:', 'classic-yourls' ) . '</label>';
        echo '<input type="text" id="classic_yourls_keyword" name="classic_yourls_keyword" value="' . esc_attr( $keyword ) . '" style="width: 100%;" />';
        echo '</p>';

        // Display current short link
        echo '<p><strong>' . esc_html__( 'Short Link:', 'classic-yourls' ) . '</strong><br>';
        if ( $shortlink ) {
            echo '<code style="word-break: break-all;">' . esc_html( $shortlink ) . '</code>';
        } else {
            echo '<em>' . esc_html__( 'No short link generated yet', 'classic-yourls' ) . '</em>';
        }
        echo '</p>';

        // Display Post ID
        echo '<p><strong>' . esc_html__( 'Post ID:', 'classic-yourls' ) . '</strong> ' . intval( $post->ID ) . '</p>';

        // Enhanced shortcode examples section
        echo '<div style="background: #f9f9f9; padding: 10px; border-left: 4px solid #0073aa; margin: 10px 0;">';
        echo '<p><strong>' . esc_html__( 'Shortcode Examples:', 'classic-yourls' ) . '</strong></p>';
        
        echo '<p><code>[classicyourls_shortlink id="' . intval( $post->ID ) . '"]</code><br>';
        echo '<small style="color: #666;">' . esc_html__( 'Display short link for this post', 'classic-yourls' ) . '</small></p>';

        echo '<p><code>[classicyourls_shortlink id="' . intval( $post->ID ) . '" text="Read More"]</code><br>';
        echo '<small style="color: #666;">' . esc_html__( 'Custom link text', 'classic-yourls' ) . '</small></p>';

        echo '<p><code>[classicyourls_shortlink]</code><br>';
        echo '<small style="color: #666;">' . esc_html__( 'Auto-detect current post (when used in this post)', 'classic-yourls' ) . '</small></p>';
        echo '</div>';

        // Check if shortcodes are enabled
        $settings = get_option( 'classic_yourls' );
        if ( empty( $settings['shortcode_enabled'] ) ) {
            echo '<div style="background: #fff3cd; padding: 8px; border-left: 4px solid #ffc107; margin: 10px 0;">';
            echo '<p><strong>' . esc_html__( 'Note:', 'classic-yourls' ) . '</strong> ';
            echo esc_html__( 'Shortcodes are currently disabled. Enable them in', 'classic-yourls' ) . ' ';
            echo '<a href="' . esc_url( admin_url( 'options-general.php?page=classic_yourls' ) ) . '">';
            echo esc_html__( 'Classic YOURLS Settings', 'classic-yourls' ) . '</a>.</p>';
            echo '</div>';
        } else {
            // Show excerpt processing status if shortcodes are enabled
            if ( ! empty( $settings['excerpt_shortcodes_enabled'] ) ) {
                echo '<div style="background: #d1ecf1; padding: 8px; border-left: 4px solid #17a2b8; margin: 10px 0;">';
                echo '<p><strong>' . esc_html__( 'Tip:', 'classic-yourls' ) . '</strong> ';
                echo esc_html__( 'Shortcodes also work in post excerpts!', 'classic-yourls' ) . '</p>';
                echo '</div>';
            }
        }
    }

    /**
     * Save meta box
     */
    public function save_meta_box( $post_id ) {
        if ( ! isset( $_POST['classic_yourls_meta_box_nonce'] ) ||
             ! wp_verify_nonce( $_POST['classic_yourls_meta_box_nonce'], 'classic_yourls_save_meta_box' ) ) {
            return;
        }

        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        if ( isset( $_POST['classic_yourls_keyword'] ) ) {
            $keyword = sanitize_text_field( $_POST['classic_yourls_keyword'] );
            update_post_meta( $post_id, '_classic_yourls_keyword', $keyword );
        }
    }
}
