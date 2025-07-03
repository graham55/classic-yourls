<?php
/**
 * YOURLS actions.
 *
 * Non admin-specific actions.
 *
 * @package better-yourls
 */

/**
 * Class Better_YOURLS_Actions
 */
class Better_YOURLS_Actions {

	protected $settings;

	public function __construct() {
		$this->settings = get_option( 'better_yourls' );

		if (
			isset( $this->settings['domain'], $this->settings['key'] ) &&
			'' !== $this->settings['domain'] &&
			'' !== $this->settings['key']
		) {
			add_action( 'add_meta_boxes', array( $this, 'action_add_meta_boxes' ) );
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
	 * Adds meta box to post edit screen.
	 */
	public function action_add_meta_boxes() {
		global $post;

		if ( ! $post instanceof WP_Post ) {
			return;
		}

		$post_type = get_post_type( $post->ID );

		if ( false === $post_type || ( isset( $this->settings['post_types'] ) && in_array( $post_type, $this->settings['post_types'], true ) ) ) {
			return;
		}

		add_meta_box(
			'yourls_keyword',
			esc_html__( 'YOURLS Keyword', 'better-yourls' ),
			array( $this, 'yourls_keyword_metabox' ),
			$post->post_type,
			'side',
			'core'
		);
	}

	/**
	 * Render the YOURLS metabox in the editor.
	 */
	public function yourls_keyword_metabox() {
		global $post;

		$link     = get_post_meta( $post->ID, '_better_yourls_short_link', true );
		$readonly = $link ? 'readonly="readonly" ' : '';

		wp_nonce_field( 'better_yourls_save_post', 'better_yourls_nonce' );

		echo '<input type="text" id="better-yourls-keyword" name="better-yourls-keyword" style="width: 100%;" value="' . esc_attr( $link ) . '" ' . $readonly . '/>';
		echo '<p><em>' . esc_html__( 'If a short-url doesn\'t yet exist for this post you can enter a keyword above. If it already exists it will be displayed.', 'better-yourls' ) . '</em></p>';

		// NEW - Add Post ID and shortcode usage hint
		echo '<hr>';
		echo '<p><strong>' . esc_html__( 'Post ID:', 'better-yourls' ) . '</strong> ' . intval( $post->ID ) . '</p>';
		echo '<p><em>' . sprintf(
			esc_html__( 'Use in shortcode: [better_yourls_shortlink id="%d"]', 'better-yourls' ),
			intval( $post->ID )
		) . '</em></p>';
	}

	/**
	 * Add link to admin bar.
	 */
	public function action_admin_bar_menu() {
		global $wp_admin_bar, $post;

		if ( ! ( $post instanceof WP_Post ) || ! isset( $post->ID ) ) {
			return;
		}

		$post_type = get_post_type( $post->ID );

		if ( false === $post_type || ( isset( $this->settings['post_types'] ) && in_array( $post_type, $this->settings['post_types'], true ) ) ) {
			return;
		}

		$yourls_url = wp_get_shortlink( $post->ID, 'query' );

		if ( is_singular() && ! is_preview() && current_user_can( 'edit_post', $post->ID ) ) {
			$stats_url = $yourls_url . '+';

			$wp_admin_bar->remove_menu( 'get-shortlink' );

			$wp_admin_bar->add_menu( array(
				'id'    => 'better_yourls',
				'title' => esc_html__( 'YOURLS', 'better-yourls' ),
			) );

			$wp_admin_bar->add_menu( array(
				'parent' => 'better_yourls',
				'id'     => 'better_yourls-link',
				'title'  => esc_html__( 'YOURLS Link', 'better-yourls' ),
			) );

			$wp_admin_bar->add_menu( array(
				'parent' => 'better_yourls',
				'id'     => 'better_yourls-stats',
				'title'  => esc_html__( 'Link Stats', 'better-yourls' ),
				'href'   => $stats_url,
				'meta'   => array( 'target' => '_blank' ),
			) );
		}
	}

	/**
	 * Save keyword and create shortlink on post save.
	 */
	public function action_save_post( $post_id ) {
		if ( ! $this->_check_valid_post( $post_id ) ) {
			return;
		}
		$this->_generate_post_on_save( $post_id );
	}

	/**
	 * Save on status transition.
	 */
	public function action_transition_post_status( $new_status, $old_status, $post ) {
		if ( false === $this->_check_valid_post( $post->ID ) || 'publish' !== $new_status ) {
			return;
		}
		$this->_generate_post_on_save( $post->ID );
	}

	/**
	 * Validate post type and status.
	 */
	protected function _check_valid_post( $post_id ) {
		$post_type = get_post_type( $post_id );

		if (
			false === $post_type ||
			( isset( $this->settings['post_types'] ) && in_array( $post_type, $this->settings['post_types'], true ) ) ||
			'nav_menu_item' === $post_type ||
			defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ||
			defined( 'DOING_AJAX' ) && DOING_AJAX ||
			defined( 'DOING_CRON' ) && DOING_CRON
		) {
			return false;
		}

		$post_statuses = apply_filters( 'better_yourls_post_statuses', array( 'publish', 'future' ) );
		if ( ! in_array( get_post_status( $post_id ), $post_statuses, true ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Actually generate the YOURLS link on save.
	 */
	protected function _generate_post_on_save( $post_id ) {
		if ( defined( 'REST_REQUEST' ) ) {
			return;
		}

		if (
			! $this->_check_valid_post( $post_id ) &&
			( ! isset( $_POST['better_yourls_nonce'] ) || ! wp_verify_nonce( $_POST['better_yourls_nonce'], 'better_yourls_save_post' ) )
		) {
			wp_die( esc_html__( 'Security Error', 'better-yourls' ) );
		}

		$keyword = '';
		if ( isset( $_POST['better-yourls-keyword'] ) ) {
			$keyword = sanitize_title( trim( $_POST['better-yourls-keyword'] ) );
		}

		$keyword = apply_filters( 'better_yourls_keyword', $keyword, $post_id );
		$link = $this->create_yourls_url( $post_id, $keyword, '', 'save_post' );

		if ( '' !== $keyword && ! $link ) {
			$link = $this->create_yourls_url( $post_id, '', '', 'save_post' );
		}

		if ( $link ) {
			update_post_meta( $post_id, '_better_yourls_short_link', $link );
		}
	}

	/**
	 * Call YOURLS API to create a shortlink.
	 */
	public function create_yourls_url( $post_id, $keyword = '', $title = '', $hook = '' ) {
		if ( is_preview() && ! is_admin() ) {
			return false;
		}

		$existing = get_post_meta( $post_id, '_better_yourls_short_link', true );
		if ( $existing ) {
			return $existing;
		}

		$https      = ( isset( $this->settings['https'] ) && true === $this->settings['https'] ) ? 's' : '';
		$yourls_url = esc_url_raw( 'http' . $https . '://' . $this->settings['domain'] . '/yourls-api.php' );
		$timestamp  = current_time( 'timestamp' );

		$args = array(
			'body' => array(
				'title'     => ( '' === trim( $title ) ) ? get_the_title( $post_id ) : $title,
				'timestamp' => $timestamp,
				'signature' => md5( $timestamp . $this->settings['key'] ),
				'action'    => 'shorturl',
				'url'       => get_permalink( $post_id ),
				'format'    => 'JSON',
			),
		);

		if ( '' !== $keyword ) {
			$args['body']['keyword'] = sanitize_title( $keyword );
		}

		if ( isset( $this->settings['https_ignore'] ) && true === $this->settings['https_ignore'] ) {
			$args['sslverify'] = false;
		}

		$response = wp_remote_post( $yourls_url, $args );
		if ( is_wp_error( $response ) ) {
			return false;
		}

		$short_link = isset( $response['body'] ) ? trim( $response['body'] ) : false;
		$url = esc_url( $short_link );

		if ( $this->validate_url( $url ) ) {
			$url = apply_filters( 'better_urls_shortlink', $url, $post_id, $hook );
			if ( false === $url ) {
				return false;
			}

			$url = esc_url_raw( $url );
			update_post_meta( $post_id, '_better_yourls_short_link', $url );

			return $url;
		}

		return false;
	}

	private function validate_url( $url ) {
		$pattern = '/^(http|https|ftp):\/\/([A-Z0-9][A-Z0-9_-]*(?:\.[A-Z0-9][A-Z0-9_-]*)+):?(\d+)?\/?/i';
		return (bool) preg_match( $pattern, $url );
	}

	public function filter_get_shortlink( $short_link, $id ) {
		if ( ! $this->_check_valid_post( $id ) ) {
			return $short_link;
		}
		$link = $this->create_yourls_url( $id, '', '', 'get_shortlink' );
		return $link ? $link : $short_link;
	}

	public function filter_pre_get_shortlink( $short_link, $id ) {
		if ( ! $this->_check_valid_post( $id ) ) {
			return $short_link;
		}
		$link = get_post_meta( $id, '_better_yourls_short_link', true );
		return $link ? $link : $short_link;
	}

	public function filter_sharing_permalink( $link, $post_id ) {
		if ( ! $this->_check_valid_post( $post_id ) ) {
			return $link;
		}
		$yourls_shortlink = $this->create_yourls_url( $post_id, '', '', 'sharing_permalink' );
		return ( $yourls_shortlink ) ? $yourls_shortlink : $link;
	}

	public function action_wp_enqueue_scripts() {
		global $post;
		if ( is_admin_bar_showing() && isset( $post->ID ) && current_user_can( 'edit_post', $post->ID ) ) {
			$min = ( defined( 'SCRIPT_DEBUG' ) && true === SCRIPT_DEBUG ) ? '' : '.min';
			wp_register_script( 'better_yourls', BYOURLS_URL . 'assets/js/better-yourls' . $min . '.js', array( 'jquery' ), BYOURLS_VERSION );
			wp_enqueue_script( 'better_yourls' );
			wp_localize_script( 'better_yourls', 'better_yourls', array(
				'text'       => esc_html__( 'Your YOURLS short link is: ', 'better-yourls' ),
				'yourls_url' => wp_get_shortlink( $post->ID ),
			) );
		}
	}
}
