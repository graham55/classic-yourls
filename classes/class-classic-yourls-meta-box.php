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

        // Add Post ID and shortcode usage hint
        echo '<p><strong>' . esc_html__( 'Short Link:', 'classic-yourls' ) . '</strong><br>';
        echo '<code>' . esc_html( $shortlink ) . '</code></p>';

        echo '<p><strong>' . esc_html__( 'Post ID:', 'classic-yourls' ) . '</strong> ' . intval( $post->ID ) . '</p>';

        echo '<p><em>' . sprintf(
            esc_html__( 'Use in shortcode: [classic_yourls_shortlink id="%d"]', 'classic-yourls' ),
            intval( $post->ID )
        ) . '</em></p>';
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
