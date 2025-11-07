=== Ajaxified Cart ===
Contributors: abhishekfdd
Donate link: https://github.com/abhishekfdd
Tags: woocommerce, ajax, cart, add to cart, block themes
Requires at least: 5.8
Tested up to: 6.8
Requires PHP: 8.0
Stable tag: 2.0.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Seamless AJAX add‑to‑cart for WooCommerce: simple & variable products (archive + blocks) with accessible modal, fast fragments, zero reloads lean UX!

== Description ==
WooCommerce core does not provide AJAX add-to-cart on the single product page or for variable products listed on archive pages (it redirects to the single view). This plugin adds an unobtrusive AJAX workflow for:

* Simple products (single product page button becomes AJAX)
* Variable products on archive & block-based Product Collection (opens an accessible modal to choose attributes, then adds via AJAX)
* Classic themes and modern block/FSE themes (2025 and later) with Product Collection blocks

Highlights:
* Works with WooCommerce fragments (mini-cart updates automatically)
* Accessible modal (focus trap, ESC to close, restore focus)
* Nonce-secured AJAX endpoints; sanitized and validated input
* Settings page to enable/disable variable product archive functionality
* Block theme compatibility (intercepts "Select options" buttons) with slug/product_id fallback
* Performance-minded (loads minified bundles built via Webpack; falls back to source if missing)
* Translations auto-loaded from WordPress.org (no manual POT/PO generation required)

Privacy: This plugin does not send any data to external services. It only uses WooCommerce cart/session APIs.

== Installation ==
1. Upload the `ajaxified-cart-woocommerce` folder to `/wp-content/plugins/`.
2. Activate the plugin through the "Plugins" menu in WordPress.
3. (Optional) Visit Settings > General > Ajaxified Cart to enable variable product archive AJAX.
4. (Developers) Build assets if modifying JS: `npm install && npm run build`.

== Frequently Asked Questions ==
= Does it work with block / FSE themes like Twenty Twenty-Five? =
Yes. Variable product "Select options" buttons in Product Collection blocks are intercepted, opening a modal for attributes.

= How do I enable AJAX for variable products on archive pages? =
Go to Settings > General > Ajaxified Cart and check "Enable ajaxified cart for variable products on archive page".

= Can I customize the modal styling? =
Yes. Override or enqueue your own CSS after `abwc-modal` or copy the CSS file and adjust.

= Will this conflict with caching or optimization plugins? =
Typically no. Ensure the localized nonce is not stripped and that minification does not remove required data attributes.

= Does it support multi-site? =
Yes. Options are deleted across sites on uninstall.

= How are translations handled? =
They are served automatically from WordPress.org language packs; no manual tools or bundled .mo files are needed.

== Screenshots ==
1. Variable product modal on block-based archive
2. AJAX add-to-cart on single product page

(Provide actual screenshot images named screenshot-1.png, screenshot-2.png before submitting.)

== Changelog ==
= 2.0.1 =
* Security: Strengthened sanitization for quantity, variation data, product slug, and variation_id validation.
* Added Requires Plugins header for explicit WooCommerce dependency.
* Escaped output of hidden AJAX input via wp_kses.
* Removed legacy translation loader & uninstall cleanup code; simplified README.

= 2.0.0 =
* Build migration: moved from Grunt to Webpack (minified bundles + source fallback).
* Performance & code structure improvements.
* Compatibility updates: Tested up to WordPress 6.8.3 and recent WooCommerce versions.
* Block theme support: Product Collection block variable products open an AJAX modal.
* Security: Added nonce verification + full sanitization for all AJAX inputs.
* Accessibility: Focus trap, ESC close, refresh link, restored focus to triggering button, improved modal semantics.
* Internationalization: Auto-load via WordPress.org (removed manual textdomain loader & POT tooling).
* Styling: Introduced dedicated modal stylesheet (abwc-modal.css).
* Compliance: ABSPATH guards across files, WPCS docblock adjustments, sanitized settings.
* Fallback logic: Ensures dist minified JS used when available, source files otherwise.

= 1.0.0 =
* Initial release

= 1.0.1 =
* Code Refactor for performance

= 1.0.2 =
* Single Product page AJAX fix

= 1.0.3 =
* Cart Display fix

= 1.2.0 =
* Added admin settings page for enabling/disabling ajaxified cart for variable products on shop page.
* Now ajaxified cart is working for variable products on shop page.
* Notices fixed on product single page.

= 1.2.1 =
* Fatal error fix

= 1.2.2 =
* Simple products archive ajax add to cart when variable product archive ajax option is enabled issue fix.
* Fixed 500 error when trying to add more variation products to cart than stock holds.

== Upgrade Notice ==
= 2.0.0 =
Major release with build migration, accessibility improvements, security hardening, and block theme support. Please update.

== Development ==
Build commands:

1. Install dependencies:
   npm install
2. Production build (minified + source maps):
   npm run build
3. Watch during development:
   npm run watch
4. Lint JS:
   npm run lint

Translations: WordPress.org language packs auto-load this plugin's strings. The former POT/PO/MO tooling and i18n loader file were removed (WP 4.6+). Do not add a load_plugin_textdomain() call; simply keep the Text Domain header accurate.

WooCommerce is a registered trademark of Automattic Inc. This plugin is not affiliated with or endorsed by Automattic/WooCommerce.
