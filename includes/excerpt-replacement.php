<?php
/**
 * Classic YOURLS Excerpt Replacement
 *
 * @package Classic_YOURLS
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Replace "Read More" links in excerpts with YOURLS short URLs
 */
add_filter( 'get_the_excerpt', 'classic_yourls_replace_excerpt_readmore', 10, 2 );

function classic_yourls_replace_excerpt_readmore( $excerpt, $post = null ) {
    // Check if the feature is enabled
    $settings = get_option( 'classic_yourls' );
    if ( empty( $settings['replace_excerpt_readmore'] ) ) {
        return $excerpt;
    }
    
    // Get the post object
    if ( ! $post ) {
        global $post;
    }
    
    if ( ! $post || ! is_object( $post ) ) {
        return $excerpt;
    }
    
    // Get the short URL for this post
    $short_url = classic_yourls_get_link( $post->ID );
    if ( ! $short_url ) {
        return $excerpt;
    }
    
    // Define patterns to match common "Read More" indicators
    $patterns = array(
        '/\[&hellip;\]/',           // WordPress default [&hellip;]
        '/\[…\]/',                  // Alternative ellipsis format
        '/\.\.\.$/',                // Three dots at end
        '/…$/',                     // Single ellipsis character at end
        '/\[\.\.\.more\]/',         // Some themes use this
        '/\[more\]/',               // Simple [more] tag
        '/\s*\[&hellip;\]\s*$/',    // Hellip with whitespace
        '/\s*…\s*$/',               // Ellipsis with whitespace
    );
    
    // Get the replacement text from settings or use default
    $replacement_text = ! empty( $settings['excerpt_replacement_text'] ) 
        ? $settings['excerpt_replacement_text'] 
        : 'Read More';
    
    // Try to replace each pattern
    foreach ( $patterns as $pattern ) {
        if ( preg_match( $pattern, $excerpt ) ) {
            $replacement = ' <a href="' . esc_url( $short_url ) . '" class="classic-yourls-excerpt-link">' . esc_html( $replacement_text ) . '</a>';
            $excerpt = preg_replace( $pattern, $replacement, $excerpt );
            break; // Only replace the first match
        }
    }
    
    return $excerpt;
}

/**
 * Add CSS for excerpt links (optional styling)
 */
add_action( 'wp_head', 'classic_yourls_excerpt_link_styles' );

function classic_yourls_excerpt_link_styles() {
    $settings = get_option( 'classic_yourls' );
    if ( empty( $settings['replace_excerpt_readmore'] ) ) {
        return;
    }
    
    ?>
    <style type="text/css">
    .classic-yourls-excerpt-link {
        font-weight: bold;
        text-decoration: none;
        color: inherit;
    }
    .classic-yourls-excerpt-link:hover {
        text-decoration: underline;
    }
    </style>
    <?php
}
