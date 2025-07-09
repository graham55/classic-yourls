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
        add_settings_field( 'classic_yourls[excerpt_shortcodes_enabled]', esc_html__( 'Enable Shortcodes in Excerpts', 'classic-yourls' ), array( $this, 'settings_field_excerpt_shortcodes_enabled' ), 'settings_page_classic_yourls', 'classic_yourls' );
        add_settings_field( 'classic_yourls[replace_excerpt_readmore]', esc_html__( 'Replace Excerpt Read More', 'classic-yourls' ), array( $this, 'settings_field_replace_excerpt_readmore' ), 'settings_page_classic_yourls', 'classic_yourls' );
        add_settings_field( 'classic_yourls[excerpt_replacement_text]', esc_html__( 'Replacement Link Text', 'classic-yourls' ), array( $this, 'settings_field_excerpt_replacement_text' ), 'settings_page_classic_yourls', 'classic_yourls' );
        add_settings_field( 'classic_yourls[debug_enabled]', esc_html__( 'Enable Debug Logging', 'classic-yourls' ), array( $this, 'settings_field_debug_enabled' ), 'settings_page_classic_yourls', 'classic_yourls' );

        register_setting( 'settings_page_classic_yourls', 'classic_yourls', array( $this, 'sanitize_module_input' ) );

        add_meta_box( 'classic_yourls_intro', esc_html__( 'Classic YOURLS', 'classic-yourls' ), array( $this, 'metabox_settings' ), 'settings_page_classic_yourls', 'main' );
        add_meta_box( 'classic_yourls_howto', esc_html__( 'How to Use Classic YOURLS', 'classic-yourls' ), array( $this, 'metabox_howto' ), 'settings_page_classic_yourls', 'main' );
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
     * How-To metabox
     */
    public function metabox_howto() {
        ?>
        <div class="classic-yourls-howto">
            <h3><?php esc_html_e( '🚀 Getting Started', 'classic-yourls' ); ?></h3>
            <ol>
                <li><strong><?php esc_html_e( 'Setup YOURLS:', 'classic-yourls' ); ?></strong> <?php esc_html_e( 'Install YOURLS on your server or use a hosted service', 'classic-yourls' ); ?></li>
                <li><strong><?php esc_html_e( 'Configure Domain:', 'classic-yourls' ); ?></strong> <?php esc_html_e( 'Enter your YOURLS domain (e.g., short.example.com)', 'classic-yourls' ); ?></li>
                <li><strong><?php esc_html_e( 'Add API Token:', 'classic-yourls' ); ?></strong> <?php esc_html_e( 'Get your API token from YOURLS admin and paste it above', 'classic-yourls' ); ?></li>
                <li><strong><?php esc_html_e( 'Save Settings:', 'classic-yourls' ); ?></strong> <?php esc_html_e( 'Click "Save Changes" to activate the plugin', 'classic-yourls' ); ?></li>
            </ol>

            <h3><?php esc_html_e( '🔗 Automatic Short Links', 'classic-yourls' ); ?></h3>
            <p><?php esc_html_e( 'Once configured, Classic YOURLS automatically creates short links for all your posts and pages. These links are:', 'classic-yourls' ); ?></p>
            <ul>
                <li><?php esc_html_e( 'Generated when posts are published', 'classic-yourls' ); ?></li>
                <li><?php esc_html_e( 'Saved to post metadata for fast access', 'classic-yourls' ); ?></li>
                <li><?php esc_html_e( 'Accessible via WordPress shortlink functions', 'classic-yourls' ); ?></li>
                <li><?php esc_html_e( 'Visible in the admin bar for easy copying', 'classic-yourls' ); ?></li>
            </ul>

            <h3><?php esc_html_e( '📝 Using Shortcodes', 'classic-yourls' ); ?></h3>
            <p><?php esc_html_e( 'When shortcodes are enabled, you can use the following shortcode in your posts, pages, and excerpts:', 'classic-yourls' ); ?></p>
            
            <div class="classic-yourls-code-examples">
                <h4><?php esc_html_e( 'Basic Usage:', 'classic-yourls' ); ?></h4>
                <code>[classicyourls_shortlink]</code>
                <p class="description"><?php esc_html_e( 'Displays the short link for the current post', 'classic-yourls' ); ?></p>

                <h4><?php esc_html_e( 'With Custom Text:', 'classic-yourls' ); ?></h4>
                <code>[classicyourls_shortlink text="Click here for short link"]</code>
                <p class="description"><?php esc_html_e( 'Creates a clickable link with custom text', 'classic-yourls' ); ?></p>

                <h4><?php esc_html_e( 'For Specific Post:', 'classic-yourls' ); ?></h4>
                <code>[classicyourls_shortlink id="123"]</code>
                <p class="description"><?php esc_html_e( 'Shows the short link for post ID 123', 'classic-yourls' ); ?></p>

                <h4><?php esc_html_e( 'Combined Example:', 'classic-yourls' ); ?></h4>
                <code>[classicyourls_shortlink id="123" text="Read the full article"]</code>
                <p class="description"><?php esc_html_e( 'Custom text linking to a specific post', 'classic-yourls' ); ?></p>
            </div>

            <h3><?php esc_html_e( '📄 Excerpt Integration', 'classic-yourls' ); ?></h3>
            <p><?php esc_html_e( 'When "Enable Shortcodes in Excerpts" is activated:', 'classic-yourls' ); ?></p>
            <ul>
                <li><?php esc_html_e( 'Shortcodes work in custom excerpt fields', 'classic-yourls' ); ?></li>
                <li><?php esc_html_e( 'Auto-generated excerpts process shortcodes', 'classic-yourls' ); ?></li>
                <li><?php esc_html_e( 'Perfect for archive pages and RSS feeds', 'classic-yourls' ); ?></li>
                <li><?php esc_html_e( 'Great for social media sharing', 'classic-yourls' ); ?></li>
            </ul>

            <h3><?php esc_html_e( '🔄 Excerpt Read More Replacement', 'classic-yourls' ); ?></h3>
            <p><?php esc_html_e( 'When "Replace Excerpt Read More" is enabled:', 'classic-yourls' ); ?></p>
            <ul>
                <li><?php esc_html_e( 'Automatically finds [...] and similar patterns in excerpts', 'classic-yourls' ); ?></li>
                <li><?php esc_html_e( 'Replaces them with clickable short links', 'classic-yourls' ); ?></li>
                <li><?php esc_html_e( 'Uses your custom replacement text (default: "Read More")', 'classic-yourls' ); ?></li>
                <li><?php esc_html_e( 'Perfect for social media sharing and RSS feeds', 'classic-yourls' ); ?></li>
                <li><?php esc_html_e( 'Works with both manual and auto-generated excerpts', 'classic-yourls' ); ?></li>
            </ul>
            <p><?php esc_html_e( 'This feature is separate from shortcodes and works automatically on excerpt display.', 'classic-yourls' ); ?></p>

            <h3><?php esc_html_e( '⚙️ Advanced Settings', 'classic-yourls' ); ?></h3>
            <ul>
                <li><strong><?php esc_html_e( 'HTTPS Support:', 'classic-yourls' ); ?></strong> <?php esc_html_e( 'Enable if your YOURLS installation uses HTTPS', 'classic-yourls' ); ?></li>
                <li><strong><?php esc_html_e( 'Self-signed Certificates:', 'classic-yourls' ); ?></strong> <?php esc_html_e( 'Allow connections to YOURLS with self-signed SSL certificates', 'classic-yourls' ); ?></li>
                <li><strong><?php esc_html_e( 'Post Type Exclusions:', 'classic-yourls' ); ?></strong> <?php esc_html_e( 'Prevent short link creation for specific post types', 'classic-yourls' ); ?></li>
                <li><strong><?php esc_html_e( 'Private Post Types:', 'classic-yourls' ); ?></strong> <?php esc_html_e( 'Allow short links for non-public post types', 'classic-yourls' ); ?></li>
                <li><strong><?php esc_html_e( 'Debug Logging:', 'classic-yourls' ); ?></strong> <?php esc_html_e( 'Enable detailed logging for troubleshooting issues', 'classic-yourls' ); ?></li>
            </ul>

            <h3><?php esc_html_e( '🎯 Post Editor Integration', 'classic-yourls' ); ?></h3>
            <p><?php esc_html_e( 'In the post editor, you\'ll find a "YOURLS Keyword" metabox that shows:', 'classic-yourls' ); ?></p>
            <ul>
                <li><?php esc_html_e( 'The current post ID', 'classic-yourls' ); ?></li>
                <li><?php esc_html_e( 'Example shortcode usage for that specific post', 'classic-yourls' ); ?></li>
                <li><?php esc_html_e( 'Easy copy-paste shortcode examples', 'classic-yourls' ); ?></li>
            </ul>

            <h3><?php esc_html_e( '🔧 Troubleshooting Tips', 'classic-yourls' ); ?></h3>
            <ul>
                <li><strong><?php esc_html_e( 'Links not generating?', 'classic-yourls' ); ?></strong> <?php esc_html_e( 'Check your YOURLS domain and API token', 'classic-yourls' ); ?></li>
                <li><strong><?php esc_html_e( 'HTTPS errors?', 'classic-yourls' ); ?></strong> <?php esc_html_e( 'Enable "Allow Self-signed https Certificate" if needed', 'classic-yourls' ); ?></li>
                <li><strong><?php esc_html_e( 'Shortcodes not working?', 'classic-yourls' ); ?></strong> <?php esc_html_e( 'Ensure "Enable Shortcode" is checked', 'classic-yourls' ); ?></li>
                <li><strong><?php esc_html_e( 'Excerpts not processing?', 'classic-yourls' ); ?></strong> <?php esc_html_e( 'Both shortcode options must be enabled', 'classic-yourls' ); ?></li>
                <li><strong><?php esc_html_e( 'Read More replacement not working?', 'classic-yourls' ); ?></strong> <?php esc_html_e( 'Enable "Replace Excerpt Read More" and ensure posts have short links', 'classic-yourls' ); ?></li>
                <li><strong><?php esc_html_e( 'Need detailed debugging?', 'classic-yourls' ); ?></strong> <?php esc_html_e( 'Enable "Debug Logging" and check your WordPress error log', 'classic-yourls' ); ?></li>
            </ul>

            <div class="classic-yourls-compatibility">
                <h3><?php esc_html_e( '✅ Compatibility', 'classic-yourls' ); ?></h3>
                <p><?php esc_html_e( 'Classic YOURLS works with:', 'classic-yourls' ); ?></p>
                <ul>
                    <li><?php esc_html_e( 'WordPress 6.8+ (tested and confirmed)', 'classic-yourls' ); ?></li>
                    <li><?php esc_html_e( 'ClassicPress (full compatibility)', 'classic-yourls' ); ?></li>
                    <li><?php esc_html_e( 'Gutenberg Block Editor', 'classic-yourls' ); ?></li>
                    <li><?php esc_html_e( 'Classic Editor', 'classic-yourls' ); ?></li>
                    <li><?php esc_html_e( 'Popular social sharing plugins', 'classic-yourls' ); ?></li>
                </ul>
            </div>
        </div>

        <style>
        .classic-yourls-howto h3 {
            margin-top: 25px;
            margin-bottom: 10px;
            color: #23282d;
        }
        .classic-yourls-howto h4 {
            margin-top: 15px;
            margin-bottom: 5px;
            color: #555;
        }
        .classic-yourls-howto code {
            background: #f1f1f1;
            padding: 4px 8px;
            border-radius: 3px;
            font-family: Consolas, Monaco, monospace;
            font-size: 13px;
            display: inline-block;
            margin: 5px 0;
        }
        .classic-yourls-code-examples {
            background: #f9f9f9;
            padding: 15px;
            border-left: 4px solid #0073aa;
            margin: 15px 0;
        }
        .classic-yourls-compatibility {
            background: #e7f7e7;
            padding: 15px;
            border-left: 4px solid #46b450;
            margin: 20px 0;
        }
        .classic-yourls-howto ul, .classic-yourls-howto ol {
            margin-left: 20px;
        }
        .classic-yourls-howto li {
            margin-bottom: 8px;
            line-height: 1.5;
        }
        </style>
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
        <p class="description"><?php esc_html_e( 'Allow the [classicyourls_shortlink] shortcode to be used in posts and pages.', 'classic-yourls' ); ?></p>
        <?php
    }

    /**
     * Excerpt shortcodes enabled settings field
     */
    public function settings_field_excerpt_shortcodes_enabled() {
        $enabled = isset( $this->settings['excerpt_shortcodes_enabled'] ) ? (bool) $this->settings['excerpt_shortcodes_enabled'] : false;
        ?>
        <input type="checkbox" id="classic_yourls_excerpt_shortcodes_enabled" name="classic_yourls[excerpt_shortcodes_enabled]" value="1" <?php checked( $enabled ); ?> />
        <label for="classic_yourls_excerpt_shortcodes_enabled"><?php esc_html_e( 'Allow YOURLS shortcodes in post excerpts', 'classic-yourls' ); ?></label>
        <p class="description"><?php esc_html_e( 'When enabled, shortcodes like [classicyourls_shortlink] will work in excerpt fields and auto-generated excerpts.', 'classic-yourls' ); ?></p>
        <?php
    }

    /**
     * Replace excerpt read more settings field
     */
    public function settings_field_replace_excerpt_readmore() {
        $enabled = isset( $this->settings['replace_excerpt_readmore'] ) ? (bool) $this->settings['replace_excerpt_readmore'] : false;
        ?>
        <input type="checkbox" id="classic_yourls_replace_excerpt_readmore" name="classic_yourls[replace_excerpt_readmore]" value="1" <?php checked( $enabled ); ?> />
        <label for="classic_yourls_replace_excerpt_readmore"><?php esc_html_e( 'Replace "Read More" links in excerpts with YOURLS short URLs', 'classic-yourls' ); ?></label>
        <p class="description"><?php esc_html_e( 'Automatically replaces [...] and similar patterns in excerpts with clickable short links. Perfect for social media sharing.', 'classic-yourls' ); ?></p>
        <?php
    }

    /**
     * Excerpt replacement text settings field
     */
    public function settings_field_excerpt_replacement_text() {
        $value = isset( $this->settings['excerpt_replacement_text'] ) ? $this->settings['excerpt_replacement_text'] : 'Read More';
        ?>
        <input type="text" id="classic_yourls_excerpt_replacement_text" name="classic_yourls[excerpt_replacement_text]" value="<?php echo esc_attr( $value ); ?>" class="regular-text" />
        <p class="description"><?php esc_html_e( 'Text to display for the replacement link (default: "Read More")', 'classic-yourls' ); ?></p>
        <?php
    }

    /**
     * Debug enabled settings field
     */
    public function settings_field_debug_enabled() {
        $enabled = isset( $this->settings['debug_enabled'] ) ? (bool) $this->settings['debug_enabled'] : false;
        ?>
        <input type="checkbox" id="classic_yourls_debug_enabled" name="classic_yourls[debug_enabled]" value="1" <?php checked( $enabled ); ?> />
        <label for="classic_yourls_debug_enabled"><?php esc_html_e( 'Enable detailed debug logging for troubleshooting', 'classic-yourls' ); ?></label>
        <p class="description"><?php esc_html_e( 'When enabled, detailed debug information will be logged to help diagnose issues. Only enable when troubleshooting problems.', 'classic-yourls' ); ?></p>
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
        $input['excerpt_shortcodes_enabled'] = isset( $input['excerpt_shortcodes_enabled'] ) ? (bool) $input['excerpt_shortcodes_enabled'] : false;
        $input['replace_excerpt_readmore'] = isset( $input['replace_excerpt_readmore'] ) ? (bool) $input['replace_excerpt_readmore'] : false;
        $input['excerpt_replacement_text'] = isset( $input['excerpt_replacement_text'] ) ? sanitize_text_field( $input['excerpt_replacement_text'] ) : 'Read More';
        $input['debug_enabled'] = isset( $input['debug_enabled'] ) ? (bool) $input['debug_enabled'] : false;
        
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
