=== “Ajaxified Cart plugin for eCommerce stores (compatible with WooCommerce)” ===
Contributors: abhishekfdd
Tags: woocommerce, ajax, cart
Requires at least: 5.8
Requires PHP: 7.4
Tested up to: 6.6.2
Stable tag: 2.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plugin adds AJAX add-to-cart to single product and variable products in archive pages. Compatible with WooCommerce.

== Description ==

WooCommerce doesn't provides ajax add to cart for single product page and also for variable products in archive page.
This plugin adds ajax feature for single product page and variable products in archive page.

== Installation ==

1. Upload `abwc-ajax-cart` folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress

== Changelog ==

= 2.0.0 =
* Tested up to WooCommerce 6.6.2 and WordPress 6.2.2

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
* Simple products archive ajax add to cart when varibale product archive ajax option is enabled issue fix.
* fixed 500 error when tried to add more variation products to cart than stock holds.

== Development ==

The legacy Grunt workflow has been replaced by Webpack.

Build commands:

1. Install dependencies:
   npm install
2. Production build (minified + source maps):
   npm run build
3. Watch during development:
   npm run watch
4. Update POT file:
   npm run pot
5. Compile .po -> .mo:
   npm run mo
6. Lint JS:
   npm run lint

Output JS bundles are generated into assets/js/dist/*.min.js and automatically enqueued with fallbacks to source files if the dist build is missing.

To revert to the old Grunt setup, restore Gruntfile.js and related devDependencies.
