Classic YOURLS — Shortcode Extension Notes
=========================================

Author of these changes: Graham McKoen (graham@cambmail.com)
Version: 2.4.2
Last Updated: July 2025

Purpose:
--------
This file documents the shortcode functionality that has been fully integrated into the Classic YOURLS plugin as of version 2.4.1+. These features are now part of the official plugin release and provide comprehensive shortcode support for YOURLS integration.

All existing YOURLS integration, metabox handling, and admin settings remain fully functional with these enhancements building upon the existing foundation.

Added Functionality
--------------------
- Introduced a new WordPress shortcode: [classicyourls_shortlink]
- Example usages:
  [classicyourls_shortlink id="123"]
  [classicyourls_shortlink id="123" text="Click here"]
  [classicyourls_shortlink] (defaults to current post ID)

- The shortcode renders as a clickable link in the post content.
  - If `text` is provided, it is used as the link text.
  - If no `text` is given, the short URL itself is used as the link text.

- Added plugin settings page checkboxes:
  ✅ Enable Shortcode
  ✅ Enable Shortcodes in Excerpts

  - The first toggle allows site admins to enable or disable shortcode processing globally.
  - The second toggle enables shortcode processing within post excerpts (requires first option to be enabled).
  - If disabled, shortcodes will return empty output.

Excerpt Processing (NEW in v2.4.2)
----------------------------------
- Shortcodes can now be processed in post excerpts when enabled
- Works with both manual excerpts and auto-generated excerpts
- Selective processing - only processes excerpts containing YOURLS shortcodes
- Perfect for archive pages, RSS feeds, and social media sharing
- Maintains performance by avoiding unnecessary processing
- Requires both "Enable Shortcode" and "Enable Shortcodes in Excerpts" to be checked

Metabox Changes
---------------
- The existing YOURLS Keyword metabox in the post editor is preserved.
- Below the existing input field, the metabox now displays:
  - The Post ID
  - A usage example of the shortcode for that post:
    [classicyourls_shortlink id="{post_id}"]

- This change is purely for user guidance and does not affect any save or API logic.

Implementation Notes
--------------------
- All calls to the YOURLS API remain handled by the original class-classic-yourls-actions.php.
- The shortcode itself is implemented in includes/shortcodes.php file.
- The plugin's settings page (class-classic-yourls-admin.php) was extended to include:
  - A field to enable/disable shortcode support
  - A field to enable/disable excerpt shortcode processing
  - Settings sanitization for both fields
- Excerpt processing uses selective filtering to maintain performance

Built-in Documentation (NEW in v2.4.2)
--------------------------------------
- Comprehensive How-To guide added to the settings page
- Complete usage examples with different parameters
- Troubleshooting section for common issues
- Step-by-step setup instructions
- Compatibility information and requirements
- Visual code examples with proper formatting
- User-friendly explanations for all features

Compatibility
-------------
- Tested and confirmed on WordPress 6.8.1
- Tested on ClassicPress v2.4.1 (which does not use the Gutenberg block editor)
- Classic Editor is fully supported
- Gutenberg Block Editor is fully supported
- Compatible with popular social sharing plugins
- Works with both manual and auto-generated excerpts

WordPress Features Supported:
- wp_shortlink integration
- Post meta storage
- Admin bar integration
- Translation system compatibility
- Multisite considerations (under evaluation)

Backward Compatibility
---------------------
- Maintains support for old meta keys (_better_yourls_short_link)
- Existing YOURLS short URLs continue to work unchanged
- No regeneration of existing links required
- Seamless transition from previous versions

Future Development Considerations
--------------------------------
- Gutenberg block integration for visual shortcode insertion
- Enhanced block editor sidebar panel
- Real-time link preview in editor
- Advanced shortcode attributes for styling
- Bulk shortcode operations

Integration Status
------------------
- These features are now part of the official Classic YOURLS plugin (v2.4.1+)
- No longer experimental - fully integrated and supported
- Backward compatibility maintained for existing installations
- Settings migration handled automatically

Performance Optimizations
-------------------------
- Conditional loading of shortcode functionality
- Selective excerpt processing (only when shortcodes are present)
- Efficient caching through existing post meta system
- Minimal overhead when features are disabled

Migration Notes
---------------
For users upgrading from previous versions:
- Old [betteryourls_shortlink] shortcodes need manual replacement with [classicyourls_shortlink]
- Existing YOURLS short URLs will continue to work unchanged
- No API regeneration required - links are preserved
- Simple find/replace operation for shortcode names in content

Thanks,
Graham McKoen
