<?php
/**
 * Classic YOURLS Shortcodes
 *
 * @package Classic_YOURLS
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Register the shortcode with consistent naming
 */
add_shortcode( 'classicyourls_shortlink', function( $atts ) {
    global $post;
    
    $atts = shortcode_atts( array(
        'id' => $post ? $post->ID : 0,
        'text' => '',
    ), $atts );

    $id = absint( $atts['id'] );
    if ( ! $id ) {
        return '';
    }

    // Get the saved shortlink
    $link = classic_yourls_get_link( $id );
    if ( ! $link ) {
        return '';
    }

    // Return linked text if provided, otherwise link using the URL itself
    if ( ! empty( $atts['text'] ) ) {
        return '<a href="' . esc_url( $link ) . '">' . esc_html( $atts['text'] ) . '</a>';
    }

    return '<a href="' . esc_url( $link ) . '">' . esc_html( $link ) . '</a>';
} );

/**
 * Helper function to get shortlink (maintains backward compatibility)
 */
function classic_yourls_get_link( $post_id ) {
    // Try new meta key first
    $link = get_post_meta( $post_id, '_classic_yourls_short_link', true );
    
    // Fall back to old meta key for backward compatibility
    if ( ! $link ) {
        $link = get_post_meta( $post_id, '_better_yourls_short_link', true );
    }
    
    return $link;
}
