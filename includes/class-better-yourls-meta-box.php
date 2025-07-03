<?php
/**
 * YOURLS Post Editor Metabox.
 *
 * Adds the YOURLS keyword and link box to post editor screens.
 *
 * @package better-yourls
 */

class Better_YOURLS_Meta_Box {

	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'register' ) );
		add_action( 'save_post', array( $this, 'save_meta_box' ) );
	}

	public function register() {
		$post_types = get_post_types( array( 'public' => true ), 'names' );

		foreach ( $post_types as $post_type ) {
			add_meta_box(
				'better_yourls_meta_box',
				esc_html__( 'YOURLS Keyword', 'better-yourls' ),
				array( $this, 'render_meta_box' ),
				$post_type,
				'side'
			);
		}
	}

	public function render_meta_box( $post ) {
		$keyword   = get_post_meta( $post->ID, '_better_yourls_keyword', true );
		$shortlink = get_post_meta( $post->ID, '_better_yourls', true );

		wp_nonce_field( 'better_yourls_save_meta_box', 'better_yourls_meta_box_nonce' );

		echo '<p>';
		echo '<label for="better_yourls_keyword">' . esc_html__( 'Enter YOURLS Keyword', 'better-yourls' ) . '</label><br />';
		echo '<input type="text" id="better_yourls_keyword" name="better_yourls_keyword" value="' . esc_attr( $keyword ) . '" style="width:100%;" />';
		echo '</p>';

		if ( $shortlink ) {
			echo '<p><strong>' . esc_html__( 'Short Link:', 'better-yourls' ) . '</strong><br />';
			echo '<a href="' . esc_url( $shortlink ) . '" target="_blank">' . esc_html( $shortlink ) . '</a></p>';
		}

		// Add Post ID and shortcode usage hint
		echo '<hr>';
		echo '<p><strong>' . esc_html__( 'Post ID:', 'better-yourls' ) . '</strong> ' . intval( $post->ID ) . '</p>';
		echo '<p><em>' . sprintf(
			esc_html__( 'Use in shortcode: [better_yourls_shortlink id="%d"]', 'better-yourls' ),
			intval( $post->ID )
		) . '</em></p>';
	}

	public function save_meta_box( $post_id ) {
		if ( ! isset( $_POST['better_yourls_meta_box_nonce'] ) || 
		     ! wp_verify_nonce( $_POST['better_yourls_meta_box_nonce'], 'better_yourls_save_meta_box' ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		if ( isset( $_POST['better_yourls_keyword'] ) ) {
			$keyword = sanitize_text_field( $_POST['better_yourls_keyword'] );
			update_post_meta( $post_id, '_better_yourls_keyword', $keyword );
		}
	}
}
