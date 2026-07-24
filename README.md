# Ajaxified Cart for WooCommerce

AJAX add-to-cart for WooCommerce: simple & variable products on the single product page, archives, and Product Collection blocks — via an accessible modal and instant cart refresh, no page reload required.

[![License: GPL v2+](https://img.shields.io/badge/license-GPL--2.0%2B-blue.svg)](LICENSE.txt)
![WordPress](https://img.shields.io/badge/WordPress-5.8%2B-21759b)
![WooCommerce](https://img.shields.io/badge/WooCommerce-required-96588a)
![PHP](https://img.shields.io/badge/PHP-8.0%2B-777bb4)

## Why

WooCommerce core doesn't AJAX-ify add-to-cart everywhere out of the box — variable products on archive pages and Product Collection blocks redirect to the single product view instead. This plugin closes that gap without touching core templates:

- **Simple products** — the add-to-cart button on the single product page becomes AJAX.
- **Variable products** — on archives and block-based Product Collection, clicking "Select options" opens an accessible modal to pick attributes, then adds to cart via AJAX.
- **Classic and block/FSE themes** — works with Product Collection blocks on modern themes (Twenty Twenty-Five and later) as well as classic shop templates.

## Features

- Updates WooCommerce fragments automatically, so the mini-cart refreshes instantly.
- Accessible modal: focus trap, ESC to close, focus restored to the triggering button.
- Nonce-secured AJAX endpoints with sanitized and validated input on every request.
- Settings page to toggle AJAX for variable products on archive pages.
- Intercepts block-theme "Select options" buttons, with slug/product ID fallback.
- Ships minified, Webpack-built assets with automatic fallback to source files if a build is missing.
- Translation-ready via WordPress.org language packs — no manual POT/PO/MO handling.
- Sends no data to external services; only uses WooCommerce's own cart/session APIs.

## Requirements

- WordPress 5.8+
- WooCommerce (active)
- PHP 8.0+

## Installation

1. Download or clone this repository into `wp-content/plugins/ajaxified-cart-woocommerce`.
2. Activate **Ajaxified Cart** from the Plugins screen in wp-admin.
3. *(Optional)* Go to **Settings → General → Ajaxified Cart** to enable AJAX for variable products on archive pages.
4. *(Developers)* If you modify the JS, build the assets first — see [Development](#development).

## Usage

Once active, add-to-cart buttons across the shop are progressively enhanced:

- Simple products submit via AJAX and update the mini-cart without a page reload.
- Variable products open a modal for attribute selection when clicked from an archive, block-based Product Collection, or similar listing context.
- Enable/disable the archive-page behavior for variable products at any time from the settings page.

## Development

```bash
npm install          # install dependencies
npm run build        # production build (minified + source maps)
npm run watch         # rebuild on file changes during development
npm run lint          # lint JS with ESLint
```

Source assets live in `assets/js` and `assets/css`; Webpack outputs minified bundles that the plugin loads automatically, falling back to source files if a build hasn't been run.

### Project structure

```
wc-ajax-cart.php        Plugin bootstrap
includes/               Core plugin, hook loader, activator
admin/                  Settings page (Settings → General → Ajaxified Cart)
assets/js               Source JavaScript (cart, variations, admin settings)
assets/css              Modal and cart styling
```

Translations are served automatically from WordPress.org language packs (WP 4.6+) — there's no bundled POT/PO/MO tooling and no manual `load_plugin_textdomain()` call to maintain.

## Frequently Asked Questions

**Does it work with block/FSE themes like Twenty Twenty-Five?**
Yes. Variable product "Select options" buttons inside Product Collection blocks are intercepted and open the attribute-selection modal.

**How do I enable AJAX for variable products on archive pages?**
Go to **Settings → General → Ajaxified Cart** and check "Enable ajaxified cart for variable products on archive page."

**Can I customize the modal styling?**
Yes — enqueue your own CSS after `abwc-modal`, or copy the plugin's CSS file and adjust it.

**Will this conflict with caching or optimization plugins?**
Typically no, as long as the localized nonce isn't stripped and minification doesn't remove required data attributes.

**Does it support multisite?**
Yes.

## Contributing

Issues and pull requests are welcome. Please run `npm run lint` and build assets (`npm run build`) before submitting a PR.

## License

GPLv2 or later. See [LICENSE.txt](LICENSE.txt).

---

WooCommerce is a registered trademark of Automattic Inc. This plugin is not affiliated with or endorsed by Automattic/WooCommerce.
