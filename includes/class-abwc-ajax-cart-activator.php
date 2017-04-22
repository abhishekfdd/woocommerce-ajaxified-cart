<?php
/**
 * Fired during plugin activation
 *
 * @since      1.0.0
 *
 * @package    ABWC_Ajax_Cart
 * @subpackage ABWC_Ajax_Cart/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    ABWC_Ajax_Cart
 * @subpackage ABWC_Ajax_Cart/includes
 * @author     Abhishek Kumar <abhishekfdd@gmail.com>
 */
class ABWC_Ajax_Cart_Activator {

	/**
	 * Checks if WooCommerce is active.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {

		if ( ! function_exists( 'WC' ) ) {
			return;
		}
	}

}
