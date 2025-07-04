<?php
/**
 * Classic YOURLS Admin
 *
 * @package Classic_YOURLS
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class Classic_YOURLS_Admin
 */
class Classic_YOURLS_Admin {

    /**
     * The saved Classic YOURLS settings
     *
     * @since 0.0.1
     *
     * @var array|bool
     */
    protected $settings;

    /**
     * Classic YOURLS admin constructor.
     *
     * @since 0.0.1
     *
     * @return Classic_YOURLS_Admin
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

        add_action( 'admin_enqueue_scripts', array( $this, 'action_admin_enqueue_scripts' ) );
        add_action( 'admin_init', array( $this, 'action_admin_init' ) );
        add_action( 'admin_menu', array( $this, 'action_admin_menu' ) );
        add_filter( 'plugin_action_links', array( $this, 'filter_plugin_action_links' ), 10, 2 );
    }

    /**
     * Enqueue admin scripts
     */
    public function action_admin_enqueue_scripts() {
        if ( 'settings_page_classic_yourls' === get_current_screen()->id ) {
            $min = ( defined( 'SCRIPT_DEBUG' ) && true === SCRIPT_DEBUG ) ? '' : '.min';
            
            wp_register_script( 'classic_yourls_footer', CYOURLS_URL . 'assets/js/admin-footer' . $min . '.js', array( 'jquery' ), CYOURLS_VERSION, true );
            wp_register_style( 'classic_yourls_admin', CYOURLS_URL . 'assets/css/classic-yourls' . $min . '.css', array(), CYOURLS_VERSION );
            
            wp_enqueue_script( 'classic_yourls_footer' );
            wp_enqueue_style( 'classic_yourls_admin' );
        }
    }

    /**
     * Admin init
     */
    public function action_admin_init() {
        add_settings_section( 'classic_yourls', '', '__return_empty_string', 'settings_page_classic_yourls' );

        add_settings_field( 'classic_yourls[domain]', esc_html__( 'YOURLS Domain', 'classic-yourls' ), array( $this, 'settings_field_domain' ), 'settings_page_classic_yourls', 'classic_yourls' );
        add_settings_field( 'classic_yourls[https]', esc_html__( 'Use https', 'classic-yourls' ), array( $this, 'settings_field_https' ), 'settings_page_classic_yourls', 'classic_yourls' );
        add_settings_field( 'classic_yourls[https_ignore]', esc_html__( 'Allow Self-signed https Certificate', 'classic-yourls' ), array( $this, 'settings_field_https_ignore' ), 'settings_page_classic_yourls', 'classic_yourls' );
        add_settings_field( 'classic_yourls[key]', esc_html__( 'YOURLS Token', 'classic-yourls' ), array( $this, 'settings_field_key' ), 'settings_page_classic_yourls', 'classic_yourls' );
        add_settings_field( 'classic_yourls[post_types]', esc_html__( 'Exclude Post Types', 'classic-yourls' ), array( $this, 'settings_field_post_types' ), 'settings_page_classic_yourls', 'classic_yourls' );
        add_settings_field( 'classic_yourls[private_post_types]', esc_html__( 'Allow Private Post Types', 'classic-yourls' ), array( $this, 'settings_field_private_post_types' ), 'settings_page_classic_yourls', 'classic_yourls' );
        add_settings_field( 'classic_yourls[shortcode_enabled]', esc_html__( 'Enable Shortcode', 'classic-yourls' ), array( $this, 'settings_field_shortcode_enabled' ), 'settings_page_classic_yourls', 'classic_yourls' );

        register_setting( 'settings_page_classic_yourls', 'classic_yourls', array( $this, 'sanitize_module_input' ) );

        add_meta_box( 'classic_yourls_intro', esc_html__( 'Classic YOURLS', 'classic-yourls' ), array( $this, 'metabox_settings' ), 'settings_page_classic_yourls', 'main' );
        add_meta_box( 'classic_yourls_subscribe', esc_html__( 'Subscribe', 'classic-yourls' ), array( $this, 'metaboxSubscribe' ), 'settings_page_classic_yourls', 'side' );
        add_meta_box( 'classic_yourls_support', esc_html__( 'Support This Plugin', 'classic-yourls' ), array( $this, 'metabox_support' ), 'settings_page_classic_yourls', 'side' );
        add_meta_box( 'classic_yourls_help', esc_html__( 'Need help?', 'classic-yourls' ), array( $this, 'metabox_help' ), 'settings_page_classic_yourls', 'side' );
    }

    /**
     * Add admin menu
     */
    public function action_admin_menu() {
        $page = add_options_page(
            esc_html__( 'Classic YOURLS', 'classic-yourls' ),
            esc_html__( 'Classic YOURLS', 'classic-yourls' ),
            'manage_options',
            'classic_yourls',
            array( $this, 'render_page' )
        );
        
        add_action( 'load-' . $page, array( $this, 'page_actions' ) );
    }

    /**
     * Page actions
     */
    public function page_actions() {
        add_screen_option( 'layout_columns', array( 'max' => 2, 'default' => 2 ) );
        wp_enqueue_script( 'common' );
        wp_enqueue_script( 'wp-lists' );
        wp_enqueue_script( 'postbox' );
    }

    /**
     * Render settings page
     */
    public function render_page() {
        $screen = get_current_screen()->id;
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Classic YOURLS Settings', 'classic-yourls' ); ?></h1>
            
            <div id="poststuff">
                <div id="post-body" class="metabox-holder columns-<?php echo 1 == get_current_screen()->get_columns() ? '1' : '2'; ?>">
                    <div id="post-body-content">
                        <div class="meta-box-sortables ui-sortable">
                            <?php do_meta_boxes( $screen, 'main', null ); ?>
                        </div>
                    </div>
                    <div id="postbox-container-1" class="postbox-container">
                        <div class="meta-box-sortables">
                            <?php do_meta_boxes( $screen, 'side', null ); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Settings metabox
     */
    public function metabox_settings() {
        ?>
        <form method="post" action="options.php">
            <?php settings_fields( 'settings_page_classic_yourls' ); ?>
            <?php do_settings_sections( 'settings_page_classic_yourls' ); ?>
            <?php submit_button(); ?>
        </form>
        <?php
    }

    /**
     * Support metabox
     */
    public function metabox_support() {
        echo '<p>' . esc_html__( 'Have you found this plugin useful? Please help support its continued development with a donation.', 'classic-yourls' ) . '</p>';
    }

    /**
     * Help metabox
     */
    public function metabox_help() {
        echo '<p>' . esc_html__( 'If you need help getting this plugin or have found a bug please visit the support forums.', 'classic-yourls' ) . '</p>';
    }

    /**
     * Subscribe metabox
     */
    public function metaboxSubscribe() {
        echo '<p>' . esc_html__( 'Subscribe to updates about this plugin.', 'classic-yourls' ) . '</p>';
    }

    /**
     * Domain settings field
     */
    public function settings_field_domain() {
        $domain = isset( $this->settings['domain'] ) ? $this->settings['domain'] : '';
        echo '<input type="text" id="classic_yourls_domain" name="classic_yourls[domain]" value="' . esc_attr( $domain ) . '" class="regular-text" />';
    }

    /**
     * HTTPS settings field
     */
    public function settings_field_https() {
        $https = isset( $this->settings['https'] ) ? (bool) $this->settings['https'] : false;
        echo '<input type="checkbox" id="classic_yourls_https" name="classic_yourls[https]" value="1"' . checked( $https, true, false ) . ' />';
    }

    /**
     * HTTPS ignore settings field
     */
    public function settings_field_https_ignore() {
        $https_ignore = isset( $this->settings['https_ignore'] ) ? (bool) $this->settings['https_ignore'] : false;
        echo '<input type="checkbox" id="classic_yourls_https_ignore" name="classic_yourls[https_ignore]" value="1"' . checked( $https_ignore, true, false ) . ' />';
    }

    /**
     * API key settings field
     */
    public function settings_field_key() {
        $key = isset( $this->settings['key'] ) ? $this->settings['key'] : '';
        echo '<input type="text" id="classic_yourls_key" name="classic_yourls[key]" value="' . esc_attr( $key ) . '" class="regular-text" />';
    }

    /**
     * Post types settings field
     */
    public function settings_field_post_types() {
        $post_types = get_post_types( array( 'public' => true ), 'objects' );
        $excluded = isset( $this->settings['post_types'] ) ? $this->settings['post_types'] : array();
        
        foreach ( $post_types as $post_type ) {
            $checked = in_array( $post_type->name, $excluded, true ) ? 'checked="checked"' : '';
            echo '<label><input type="checkbox" name="classic_yourls[post_types][]" value="' . esc_attr( $post_type->name ) . '" ' . $checked . ' /> ' . esc_html( $post_type->labels->name ) . '</label><br>';
        }
        
        echo '<p class="description">' . esc_html__( 'Check any post type for which you do NOT want to generate a short link.', 'classic-yourls' ) . '</p>';
    }

    /**
     * Private post types settings field
     */
    public function settings_field_private_post_types() {
        $private_post_types = !empty( $this->settings['private_post_types'] );
        echo '<input type="checkbox" id="classic_yourls_private_post_types" name="classic_yourls[private_post_types]" value="1"' . checked( $private_post_types, true, false ) . ' />';
        echo '<label for="classic_yourls_private_post_types">' . esc_html__( 'Allow private post types', 'classic-yourls' ) . '</label>';
    }

    /**
     * Shortcode enabled settings field
     */
    public function settings_field_shortcode_enabled() {
        $enabled = isset( $this->settings['shortcode_enabled'] ) ? (bool) $this->settings['shortcode_enabled'] : true;
        ?>
        <input type="checkbox" id="classic_yourls_shortcode_enabled" name="classic_yourls[shortcode_enabled]" value="1" <?php checked( $enabled ); ?> />
        <label for="classic_yourls_shortcode_enabled"><?php esc_html_e( 'Enable shortcode support', 'classic-yourls' ); ?></label>
        <?php
    }

    /**
     * Filter plugin action links
     */
    public function filter_plugin_action_links( $links, $file ) {
        static $this_plugin;
        
        if ( empty( $this_plugin ) ) {
            $this_plugin = 'classic-yourls/classic-yourls.php';
        }
        
        if ( $file == $this_plugin ) {
            $links[] = '<a href="' . admin_url( 'options-general.php?page=classic_yourls' ) . '">' . esc_html__( 'Settings', 'classic-yourls' ) . '</a>';
        }
        
        return $links;
    }

    /**
     * Sanitize input
     */
    public function sanitize_module_input( $input ) {
        $input['domain'] = sanitize_text_field( $input['domain'] );
        $input['key'] = sanitize_text_field( $input['key'] );
        $input['https'] = isset( $input['https'] ) ? (bool) $input['https'] : false;
        $input['https_ignore'] = isset( $input['https_ignore'] ) ? (bool) $input['https_ignore'] : false;
        $input['private_post_types'] = isset( $input['private_post_types'] ) ? (bool) $input['private_post_types'] : false;
        $input['shortcode_enabled'] = isset( $input['shortcode_enabled'] ) ? (bool) $input['shortcode_enabled'] : false;
        
        $excluded = array();
        if ( isset( $input['post_types'] ) && is_array( $input['post_types'] ) ) {
            $args = array( 'public' => true );
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

    /**
     * Sort post types
     */
    public function sort_post_types( $a, $b ) {
        return strcmp( $a->labels->name, $b->labels->name );
    }
}
