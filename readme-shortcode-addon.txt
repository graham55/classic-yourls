Better YOURLS — Shortcode Extension Notes
=========================================

Author of these changes: Graham McKoen (graham@cambmail.com)

Purpose:
--------
This file documents additional shortcode functionality and settings that I developed on top of the existing Better YOURLS plugin. It is *not* an official fork, but a contribution intended for consideration by the original plugin author.

All existing YOURLS integration, metabox handling, and admin settings remain untouched except where explicitly extended as described below.


Added Functionality
--------------------
- Introduced a new WordPress shortcode: [better_yourls_shortlink]
- Example usages:
  [better_yourls_shortlink id="123"]
  [better_yourls_shortlink id="123" text="Click here"]
  [better_yourls_shortlink] (defaults to current post ID)

- The shortcode renders as a clickable link in the post content.
  - If `text` is provided, it is used as the link text.
  - If no `text` is given, the short URL itself is used as the link text.

- Added a plugin settings page checkbox:
  ✅ Enable Shortcode

  - This toggle allows site admins to enable or disable shortcode processing globally.
  - If disabled, the shortcode will return empty output.


Metabox Changes
---------------
- The existing YOURLS Keyword metabox in the post editor is preserved.
- Below the existing input field, the metabox now displays:
  - The Post ID
  - A usage example of the shortcode for that post:
    [better_yourls_shortlink id="{post_id}"]

- This change is purely for user guidance and does not affect any save or API logic.


Implementation Notes
--------------------
- All calls to the YOURLS API remain handled by the original class-better-yourls-actions.php.
- The shortcode itself is implemented in a new includes/shortcodes.php file.
- The plugin’s settings page (class-better-yourls-admin.php) was extended to include:
  - A new field to enable/disable shortcode support
  - Settings sanitization for this field


Compatibility
-------------
- Tested on ClassicPress v2.4.1 (which does not use the Gutenberg block editor).
- Classic Editor is fully supported.
- No Gutenberg block integration has been provided yet.

Suggestion for Future Gutenberg Support:
----------------------------------------
- Could register the shortcode as a core/button block variation.
- Could add a block pattern or dedicated block with attribute fields for ID and text.


Respect for Original Author
---------------------------
- No existing functionality has been removed.
- All new functionality is optional and can be disabled.
- This file is offered as documentation to allow the original author to merge these changes, adapt them, or ignore them as desired.

Thanks,
Graham McKoen
