<?php
/**
 * YOURLS admin interface.
 *
 * Admin-specific items such as settings.
 *
 * @package better-yourls
 */

class Better_YOURLS_Admin {

	protected $settings;

	public function __construct() {
		$this->settings = get_option( 'better_yourls' );

		add_action( 'admin_enqueue_scripts', array( $this, 'action_admin_enqueue_scripts' ) );
		add_action( 'admin_init', array( $this, 'action_admin_init' ) );
		add_action( 'admin_menu', array( $this, 'action_admin_menu' ) );
		add_filter( 'plugin_action_links', array( $this, 'filter_plugin_action_links' ), 10, 2 );
	}

	public function action_admin_enqueue_scripts() {
		if ( 'settings_page_better_yourls' === get_current_screen()->id ) {
			$min = ( defined( 'SCRIPT_DEBUG' ) && true === SCRIPT_DEBUG ) ? '' : '.min';
			wp_register_script( 'better_yourls_footer', BYOURLS_URL . 'assets/js/admin-footer' . $min . '.js', array( 'jquery' ), BYOURLS_VERSION, true );
			wp_register_style( 'better_yourls_admin', BYOURLS_URL . 'assets/css/better-yourls' . $min . '.css', array(), BYOURLS_VERSION );
			wp_enqueue_script( 'better_yourls_footer' );
			wp_enqueue_style( 'better_yourls_admin' );
		}
	}

	public function action_admin_init() {
		add_settings_section( 'better_yourls', '', '__return_empty_string', 'settings_page_better_yourls' );

		add_settings_field( 'better_yourls[domain]', esc_html__( 'YOURLS Domain', 'better-yourls' ), array( $this, 'settings_field_domain' ), 'settings_page_better_yourls', 'better_yourls' );
		add_settings_field( 'better_yourls[https]', esc_html__( 'Use https', 'better-yourls' ), array( $this, 'settings_field_https' ), 'settings_page_better_yourls', 'better_yourls' );
		add_settings_field( 'better_yourls[https_ignore]', esc_html__( 'Allow Self-signed https Certificate', 'better-yourls' ), array( $this, 'settings_field_https_ignore' ), 'settings_page_better_yourls', 'better_yourls' );
		add_settings_field( 'better_yourls[key]', esc_html__( 'YOURLS Token', 'better-yourls' ), array( $this, 'settings_field_key' ), 'settings_page_better_yourls', 'better_yourls' );
		add_settings_field( 'better_yourls[post_types]', esc_html__( 'Exclude Post Types', 'better-yourls' ), array( $this, 'settings_field_post_types' ), 'settings_page_better_yourls', 'better_yourls' );
		add_settings_field( 'better_yourls[private_post_types]', esc_html__( 'Allow Private Post Types', 'better-yourls' ), array( $this, 'settings_field_private_post_types' ), 'settings_page_better_yourls', 'better_yourls' );

		add_settings_field( 'better_yourls[shortcode_enabled]', esc_html__( 'Enable Shortcode', 'better-yourls' ), array( $this, 'settings_field_shortcode_enabled' ), 'settings_page_better_yourls', 'better_yourls' );

		register_setting( 'settings_page_better_yourls', 'better_yourls', array( $this, 'sanitize_module_input' ) );

		add_meta_box( 'better_yourls_intro', esc_html__( 'Better YOURLS', 'better-yourls' ), array( $this, 'metabox_settings' ), 'settings_page_better_yourls', 'main' );
		add_meta_box( 'better_yourls_subscribe', esc_html__( 'Subscribe', 'better-yourls' ), array( $this, 'metaboxSubscribe' ), 'settings_page_better_yourls', 'side' );
		add_meta_box( 'better_yourls_support', esc_html__( 'Support This Plugin', 'better-yourls' ), array( $this, 'metabox_support' ), 'settings_page_better_yourls', 'side' );
		add_meta_box( 'better_yourls_help', esc_html__( 'Need help?', 'better-yourls' ), array( $this, 'metabox_help' ), 'settings_page_better_yourls', 'side' );
	}

	public function action_admin_menu() {
		$page = add_options_page( esc_html__( 'Better YOURLS', 'better-yourls' ), esc_html__( 'Better YOURLS', 'better-yourls' ), 'manage_options', 'better_yourls', array( $this, 'render_page' ) );
		add_action( 'load-' . $page, array( $this, 'page_actions' ) );
	}

	public function page_actions() {
		add_screen_option( 'layout_columns', array( 'max' => 2, 'default' => 2 ) );
		wp_enqueue_script( 'common' );
		wp_enqueue_script( 'wp-lists' );
		wp_enqueue_script( 'postbox' );
	}

	public function render_page() {
		$screen = get_current_screen()->id;
		?>
		<div class="wrap">
			<h2><?php esc_html_e( 'Better YOURLS', 'better-yourls' ); ?></h2>
			<?php
			wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false );
			wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false );
			?>
			<div id="poststuff">
				<div id="post-body" class="metabox-holder columns-<?php echo 1 === get_current_screen()->get_columns() ? '1' : '2'; ?>">
					<div id="postbox-container-2" class="postbox-container">
						<?php do_meta_boxes( $screen, 'main', null ); ?>
					</div>
					<div id="postbox-container-1" class="postbox-container">
						<?php do_meta_boxes( $screen, 'side', null ); ?>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
	
	/**
	* Build the settings metabox.
	*
	* @since 0.0.1
	*
	* @return void
	*/
	public function metabox_settings() {
		echo '<p>' . esc_html__( 'Use the settings below to configure Better YOURLs for your site.', 	'better-yourls' ) . '</p>';
		?>
		<form method="post" action="options.php" class="better-yourls-form">
			<?php settings_fields( 'settings_page_better_yourls' ); ?>
			<?php do_settings_sections( 'settings_page_better_yourls' ); ?>
			<p class="submit">
				<input type="submit" class="button-primary" value="<?php esc_html_e( 'Save Changes', 'better-yourls' ); ?>"/>
			</p>
		</form>
		<?php
	}

	public function metabox_help() {
		$support_page = 'https://wordpress.org/plugins/better-yourls/support/';
		printf(
			esc_html__( 'If you need help getting this plugin or have found a bug please visit the 	%1$ssupport forums%2$s.', 'better-yourls' ),
			'<a href="' . esc_url( $support_page ) . '" target="_blank">',
			'</a>'
		);
	}

	public function metabox_support() {
		echo '<p>' . esc_html__( 'Have you found this plugin useful? Please help support its continued 	development with a donation.', 'better-yourls' ) . '</p>';
		?>
		<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
			<input type="hidden" name="cmd" value="_donations" />
			<input type="hidden" name="business" value="CFJJ7YCD72RLA" />
			<input type="hidden" name="item_name" value="Better YOURLs" />
			<input type="hidden" name="currency_code" value="USD" />
			<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_LG.gif" border="0" 	name="submit" alt="Donate with PayPal button" />
		</form>
		<?php
	}

	public function metaboxSubscribe() {
		?>
		<!-- Begin Mailchimp Signup Form -->
		<div id="mc_embed_signup">
			<form action=	"https://blog.us8.list-manage.com/subscribe/post?u=d6c5983af28b0d029985583e9&amp;id=af9099f5d0" method="post" target="_blank">
				<div id="mc_embed_signup_scroll">
					<h2>Subscribe for plugin updates, tutorials and more!</h2>
					<div class="mc-field-group">
						<label for="mce-EMAIL">Email Address</label>
						<input type="email" value="" name="EMAIL" class="required email" id="mce-EMAIL">
					</div>
					<div class="clear">
						<input type="submit" value="Subscribe" name="subscribe" id="mc-embedded-subscribe" 	class="button">
					</div>
				</div>
			</form>
		</div>
		<?php
	}


	public function filter_plugin_action_links( $links, $file ) {
		static $this_plugin;
		if ( empty( $this_plugin ) ) {
			$this_plugin = 'better-yourls/better-yourls.php';
		}
		if ( $file === $this_plugin ) {
			$links[] = '<a href="options-general.php?page=better_yourls">' . esc_html__( 'Settings', 'better-yourls' ) . '</a>';
		}
		return $links;
	}

	/**
	 * Settings Fields
	 */
	public function settings_field_domain() {
		$domain = isset( $this->settings['domain'] ) ? sanitize_text_field( $this->settings['domain'] ) : '';
		echo '<input class="text" name="better_yourls[domain]" id="better_yourls_domain" value="' . esc_attr( $domain ) . '" type="text">';
		echo '<label for="better_yourls_domain"><p class="description"> ' . esc_html__( 'The short domain you are using for YOURLS. Enter only the domain name.', 'better-yourls' ) . '</p></label>';
	}

	public function settings_field_https() {
		$https = !empty( $this->settings['https'] );
		echo '<input name="better_yourls[https]" id="better_yourls_https" value="1" type="checkbox" ' . checked( true, $https, false ) . '>';
		echo '<label for="better_yourls_https"><p class="description"> ' . esc_html__( 'Check this box to access your YOURLS installation over https.', 'better-yourls' ) . '</p></label>';
	}

	public function settings_field_https_ignore() {
		$https_ignore = !empty( $this->settings['https_ignore'] );
		echo '<input name="better_yourls[https_ignore]" id="better_yourls_https_ignore" value="1" type="checkbox" ' . checked( true, $https_ignore, false ) . '>';
		echo '<label for="better_yourls_https_ignore"><p class="description"> ' . esc_html__( 'Check this box to ignore security checks on your https certificate. Only use this if you are using a self-signed certificate.', 'better-yourls' ) . '</p></label>';
	}

	public function settings_field_key() {
		$key = isset( $this->settings['key'] ) ? sanitize_text_field( $this->settings['key'] ) : '';
		echo '<input class="text" name="better_yourls[key]" id="better_yourls_key" value="' . esc_attr( $key ) . '" type="text">';
		echo '<label for="better_yourls_key"><p class="description"> ' . esc_html__( 'This can be found on the tools page in your YOURLS installation.', 'better-yourls' ) . '</p></label>';
	}

	public function settings_field_post_types() {
		$args = array( 'public' => true );
		if ( !empty( $this->settings['private_post_types'] ) ) {
			unset( $args['public'] );
		}
		$post_types = get_post_types( $args, 'objects' );
		uasort( $post_types, array( $this, 'sort_post_types' ) );

		$excluded_post_types = isset( $this->settings['post_types'] ) ? $this->settings['post_types'] : array();
		foreach ( $post_types as $post_type ) {
			$checked = in_array( $post_type->name, $excluded_post_types, true );
			echo '<input type="checkbox" name="better_yourls[post_types][' . esc_attr( $post_type->name ) . ']" value="' . esc_attr( $post_type->name ) . '" ' . checked( true, $checked, false ) . '><label for="better_yourls[post_types][' . esc_attr( $post_type->name ) . ']"> ' . esc_html( $post_type->labels->name ) . '</label><br />';
		}
		echo '<p class="description"> ' . esc_html__( 'Check any post type for which you do NOT want to generate a short link.', 'better-yourls' ) . '</p>';
	}

	public function settings_field_private_post_types() {
		$private_post_types = !empty( $this->settings['private_post_types'] );
		echo '<input name="better_yourls[private_post_types]" id="better_yourls_private_post_types" value="1" type="checkbox" ' . checked( true, $private_post_types, false ) . '>';
		echo '<label for="better_yourls_private_post_types"><p class="description"> ' . esc_html__( 'Check this box to allow private post types to be indexed.', 'better-yourls' ) . '</p></label>';
	}

	public function settings_field_shortcode_enabled() {
		$enabled = isset( $this->settings['shortcode_enabled'] ) ? (bool) $this->settings['shortcode_enabled'] : true;
		?>
		<label>
			<input type="checkbox" name="better_yourls[shortcode_enabled]" value="1" <?php checked( $enabled, true ); ?> />
			<?php esc_html_e( 'Enable the [better_yourls_shortlink] shortcode for use in posts and pages.', 'better-yourls' ); ?>
		</label>
		<p class="description">
			<?php esc_html_e( 'Usage: [better_yourls_shortlink id="123" text="Click here"].', 'better-yourls' ); ?>
		</p>
		<?php
	}

	public function sanitize_module_input( $input ) {
		$input['shortcode_enabled'] = isset( $input['shortcode_enabled'] ) ? 1 : 0;

		$input['domain'] = isset( $input['domain'] ) ? sanitize_text_field( $input['domain'] ) : '';
		$input['domain'] = str_replace( array( 'http://', ' ' ), '', $input['domain'] );
		$input['domain'] = trim( $input['domain'], '/' );

		$input['key'] = isset( $input['key'] ) ? sanitize_text_field( $input['key'] ) : '';
		$input['https'] = !empty( $input['https'] ) ? true : false;
		$input['https_ignore'] = !empty( $input['https_ignore'] ) ? true : false;
		$input['private_post_types'] = !empty( $input['private_post_types'] ) ? true : false;

		$excluded = array();
		if ( isset( $input['post_types'] ) && is_array( $input['post_types'] ) ) {
			$args = array();
			if ( empty( $this->settings['private_post_types'] ) ) {
				$args['public'] = true;
			}
			$public_post_types = get_post_types( $args );
			foreach ( $input['post_types'] as $post_type ) {
				if ( in_array( $post_type, $public_post_types, true ) ) {
					$excluded[] = sanitize_text_field( $post_type );
				}
			}
			$input['post_types'] = $excluded;
		}

		return $input;
	}

	public function sort_post_types( $a, $b ) {
		return strcmp( $a->labels->name, $b->labels->name );
	}
}
