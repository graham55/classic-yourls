<?php
/**
 * Shortcode for Better YOURLS.
 *
 * Usage: [better_yourls_shortlink id="123" text="Click here"]
 *
 * @package better-yourls
 */

// Helper function: fetches the shortlink from post meta
if ( ! function_exists( 'better_yourls_get_link' ) ) {
	function better_yourls_get_link( $post_id ) {
		$link = get_post_meta( $post_id, '_better_yourls_short_link', true );
		return $link ? esc_url( $link ) : '';
	}
}

// Register the shortcode
add_shortcode( 'better_yourls_shortlink', function( $atts ) {
	global $post;

	// Check if shortcode is enabled in plugin settings
	$options = get_option( 'better_yourls' );
	if ( empty( $options['shortcode_enabled'] ) ) {
		return '';
	}

	// Defaults
	$atts = shortcode_atts( array(
		'id'   => $post ? $post->ID : 0,
		'text' => '',
	), $atts );

	$id = absint( $atts['id'] );
	if ( ! $id ) {
		return '';
	}

	// Get the saved shortlink
	$link = better_yourls_get_link( $id );
	if ( ! $link ) {
		return '';
	}

	// Return linked text if provided, otherwise link using the URL itself
	if ( ! empty( $atts['text'] ) ) {
		return '<a href="' . esc_url( $link ) . '">' . esc_html( $atts['text'] ) . '</a>';
	}

	return '<a href="' . esc_url( $link ) . '">' . esc_html( $link ) . '</a>';
} );
