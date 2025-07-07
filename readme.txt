=== Classic YOURLS ===
Contributors: channeleaton, ChrisWiegman, michaelbeil, domsammut, Graham McKoen
Donate link: https://aaroneaton.blog
Tags: yourls, shortlink, custom shortlink, shortlink short codes, excerpts
Requires at least: 4.2
Tested up to: 6.8.1
Stable tag: 2.4.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Integrate your blog with <a href="http://yourls.org" target="_blank">YOURLS</a> custom URL generator with advanced shortcode support.

== License ==
Released under the terms of the GNU General Public License.

== Description ==

Integrates your blog with the <a href="http://yourls.org" target="_blank">YOURLS</a> custom URL generator, providing seamless short link creation with powerful shortcode functionality.

= Features =

* Creates YOURLS links for all content using wp_shortlink
* Saves links to post_meta to reduce server calls
* Easily access link stats from the admin bar
* Advanced shortcode support with [betteryourls_shortlink]
* Shortcode processing in post excerpts (optional)
* Comprehensive built-in documentation and How-To guide
* WordPress 6.8+ and ClassicPress compatibility
* Gutenberg block editor support

= Shortcode Usage =

* `[betteryourls_shortlink]` - Display short link for current post
* `[betteryourls_shortlink text="Custom Link Text"]` - Custom link text
* `[betteryourls_shortlink id="123"]` - Short link for specific post
* `[betteryourls_shortlink id="123" text="Read More"]` - Combined usage

= Translations =

* English

== Installation ==

1. Backup your WordPress database, config file, and .htaccess file
2. Upload the zip file to the `/wp-content/plugins/` directory
3. Unzip
4. Activate the plugin through the 'Plugins' menu in WordPress
5. Visit the Classic YOURLS page under settings to add domain and API key
6. Configure shortcode and excerpt options as needed

== Frequently Asked Questions ==

= Are you still developing this plugin? =
* Yes! This plugin is actively maintained by Graham McKoen with continued development and feature additions.

= Does this work with network or multisite installations? =
* Multisite compatibility is being evaluated. Please test in a staging environment first.

= How do I use shortcodes in excerpts? =
* Enable both "Enable Shortcode" and "Enable Shortcodes in Excerpts" in the plugin settings, then use shortcodes normally in your excerpt fields.

= What shortcode parameters are available? =
* The plugin includes a comprehensive How-To guide in the settings page with all available parameters and usage examples.

= Is this compatible with Gutenberg? =
* Yes! The plugin works with both the Gutenberg block editor and the classic editor.

= Can I help? =
* Of course! I am in constant need of testers and I would be more than happy to add the right contributor. In addition, I could always use help with translations for internationalization.

== Screenshots ==

1. Easy to use settings interface with comprehensive options
2. Built-in How-To guide with complete documentation
3. Post editor integration with shortcode examples

== Changelog ==

= 2.4.2 =
* Added comprehensive How-To guide in settings page with complete usage examples
* Enhanced excerpt shortcode processing with user control settings
* Added settings checkbox to enable/disable shortcodes in excerpts
* Improved user documentation and troubleshooting guide
* Confirmed WordPress 6.8.1 compatibility
* Enhanced settings interface with better descriptions and organization

= 2.4.1 =
* First stable release with complete shortcode functionality
* Complete project rebranding and name change implementation
* Optimized JavaScript builds and minification
* Updated language translations and i18n support
* Established stable foundation for future development

= 2.4.0 =
* Initial fork and rebrand to Classic YOURLS
* Plugin adopted by Graham McKoen <a href="https://github.com/graham55/classic-yourls" target="_blank">https://github.com/graham55/classic-yourls</a>
* Updated all internal references to use classic_yourls naming convention
* Maintained full backward compatibility with existing installations

= 2.3.0 =
* Plugin is now compatible with Gutenberg.
* Fixed security error when using Bulk Edit. Thanks <a href="https://github.com/clementduncan>@clementduncan</a>!
* Plugin adopted by <a href="https://aaroneaton.blog" target="_blank">Aaron Eaton</a>

= 2.2.3 =
* Fixed an error that prevented private post types from being handled correctly.

= 2.2.2 =
* Fixed deployment error

= 2.2.1 =
* Fixed error on settings save due to unavailable array.
* Fixed "Security Error" when saving ignored posts.
* Minor JS and CSS refactoring for easier debugging
* Moved .pot file to "languages" folder

= 2.2 =
* Added ability to properly handle non-public post types.
* Minor fixes and typo corrections.

= 2.1.6 =
* Minor code sniffer fixes.
* Added nonce to keyword form.

= 2.1.5 =
* Cleaned up various typos and other PHP Codesniffer issues.

= 2.1.4 =
* Fixed custom keyword issue (Credit Dom Sammut)
* Various typo and other minor fixes.

= 2.1.3 =
* 2.1.3 Cleans out extra files in the packaged plugin that my deployment script didn't catch.

= 2.1.2 =
* Fix: No longer will generate shortlinks for admin menu items
* Behind the scenes: Finally started adding proper Unit Tests to improve reliability. Coverage is up to about 25%

= 2.1.1 =
* Fix: ShortURL generation will now work better with many social sharing plugins such as Jetpack

= 2.1.0 =
* Enhancement: Allow for https access to YOURLS installation for API actions
* Enhancement: Disable short-url creation for specific content types
* Enhancement: Numerous additional hooks for more finer-grained control of URL creation
* Enhancement: Use POST instead of GET for URL creation
* Fix: Better checking of posts before creating a link to avoid issues

= 2.0.1 =
* Fix : Spaces should no longer be eliminated from titles
* Enhancement: Allow filtering of post types (credit to domsammut)

= 2.0.0 =
* Enhancement: complete refactor for better efficiency and less bugs

= 1.0.5 =
* Fixed: Fixed an issue preventing the shortlink from displaying for some URLS (see https://github.com/ChrisWiegman/Better-YOURLS/pull/1)

= 1.0.4 =
* Minor typo fixes and test with version 4.1

= 1.0.3 =
* Added hook to generate short url on post transition
* Added get_shortlink hook to cover normal shortlink generation
* No longer try to generate a shortlink in pre_get_shortlink. Just return it if it already exists
* More efficient shortlink creation
* General code cleanup

= 1.0.2 =
* Improved URL validation to avoid saving extraneous data
* Minor typo fixes

= 1.0.1 =
* Don't generate URLs in admin, wait for the first post view

= 1.0.0 =
* Initial Release

== Upgrade Notice ==

= 2.4.2 =
* Version 2.4.2 adds comprehensive documentation and excerpt shortcode controls. Recommended for all users.

= 2.4.1 =
* Version 2.4.1 is the first stable release with complete shortcode functionality. Recommended for all users.

= 2.4.0 =
* Version 2.4.0 represents a major rebranding and modernization. Recommended for all users.

= 2.1.4 =
* Version 2.1.3 is a bugfix update that is recommended for all users.

= 2.1.3 =
* Version 2.1.3 is a bugfix update that is recommended for all users.

= 2.1.2 =
* Version 2.1.2 is a bugfix update that is recommended for all users.

= 2.1.1 =
* Version 2.1.1 is a bugfix update that is recommended for all users.

= 2.1 =
* Version 2.1.0 contains new features to improve plugin use for everyone.

= 2.0.1 =
2.0.1 is a bugfix update that is recommended for all users.

= 1.0.5 =
* This is a bugfix release that is recommended for all users

= 1.0.1 =
* This fixes a small bug that could lead to your URL reporting as "Auto Draft" in the URLs admin

= 1.0.0 =
* Initial release. Thanks for Trying!
