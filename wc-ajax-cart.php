<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://wordpress.org/plugins/ajaxified-cart-woocommerce/
 * @since             1.0.0
 * @package           ABWC_Ajax_Cart
 *
 * @wordpress-plugin
 * Plugin Name:       Ajaxified Cart
 * Plugin URI:        https://wordpress.org/plugins/ajaxified-cart-woocommerce/
 * Description:       AJAX add-to-cart for WooCommerce: simple & variable products on archives & blocks via accessible modal and instant cart refresh.
 * Version:           2.0.2
 * Author:            Abhishek Kumar
 * Author URI:        http://github.com/abhishekfdd/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       ajaxified-cart-woocommerce
 * Requires at least: 5.8
 * Requires PHP:      8.0
 * Requires Plugins:  woocommerce
 *
 * Note: Translations are auto-loaded from WP.org language packs (no Domain Path / manual loader required).
 *
 * WooCommerce is a registered trademark of Automattic Inc. This plugin is not affiliated with or endorsed by Automattic/WooCommerce.
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * ========================================================================
 * CONSTANTS
 * ========================================================================
 */
// Codebase version.
if ( ! defined( 'ABWC_AJAX_CART_PLUGIN_VERSION' ) ) {
	define( 'ABWC_AJAX_CART_PLUGIN_VERSION', '2.0.2' );
}

// Directory.
if ( ! defined( 'ABWC_AJAX_CART_PLUGIN_DIR' ) ) {
	define( 'ABWC_AJAX_CART_PLUGIN_DIR', trailingslashit( plugin_dir_path( __FILE__ ) ) );
}

// Url.
if ( ! defined( 'ABWC_AJAX_CART_PLUGIN_URL' ) ) {
	$abwc_ajax_cart_plugin_url = plugin_dir_url( __FILE__ );
	if ( is_ssl() ) {
		$abwc_ajax_cart_plugin_url = str_replace( 'http://', 'https://', $abwc_ajax_cart_plugin_url );
	}
	define( 'ABWC_AJAX_CART_PLUGIN_URL', $abwc_ajax_cart_plugin_url );
}

// File.
if ( ! defined( 'ABWC_AJAX_CART_PLUGIN_FILE' ) ) {
	define( 'ABWC_AJAX_CART_PLUGIN_FILE', __FILE__ );
}

/**
 * ========================================================================
 * MAIN FUNCTIONS
 * ========================================================================
 */

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-abwc-ajax-cart-activator.php
 */
function abwc_ajax_cart_activate() { // Renamed from activate_abwc_ajax_cart for prefix-first compliance.
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-abwc-ajax-cart-activator.php';
	ABWC_Ajax_Cart_Activator::activate();
}

register_activation_hook( __FILE__, 'abwc_ajax_cart_activate' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-abwc-ajax-cart.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function abwc_ajax_cart_run() { // Renamed from run_abwc_ajax_cart for WPCS prefix compliance.
	$plugin = ABWC_Ajax_Cart::instance();
	return $plugin;
}

abwc_ajax_cart_run();
