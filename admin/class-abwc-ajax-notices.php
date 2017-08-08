<?php

/**
 * The file that defines the plugin notices
 *
 * Contains all the admin notices for this plugin
 *
 * @since      1.2.0
 * @package    ABWC_Ajax_Cart_notices
 * @author     Abhishek Kumar <abhishekfdd@gmail.com>
 */
class ABWC_Ajax_Cart_notices {

	/**
	 * Ajaxified Cart Notices Instance.
	 *
	 * Insures that only one instance of Ajaxified Cart Notices exists in memory at any
	 * one time.
	 *
	 * @since 1.2.0
	 *
	 * @static object $instance
	 * @uses ABWC_Ajax_Cart_notices::setup() Setup the require functions.
	 */
	public static function instance() {
		// Store the instance locally to avoid private static replication
		static $instance = null;

		// Only run these methods if they haven't been run previously
		if ( null === $instance ) {
			$instance = new ABWC_Ajax_Cart_notices();
			$instance->setup();
		}

		// Always return the instance
		return $instance;
	}

	/**
	 * Load all the notices here
	 *
	 * @since    1.2.0
	 */
	private function setup() {
		add_action( 'admin_notices', array( $this, 'new_setting_notice_1_2_0' ) );
		add_action( 'wp_ajax_abwc_dismiss_notice', array( $this, 'abwc_dismiss_notice' ) );
	}

	/**
	 * A dummy constructor to prevent Ajaxified Cart Notices from being loaded more than once.
	 *
	 * @since 1.2.0
	 * @see ABWC_Ajax_Cart_notices::instance()
	 */
	private function __construct() {
		/* nothing here */
	}

	/**
	 * Show a notice on plugin activation about new settings page
	 * 
	 * @since 1.0.0
	 */
	public function new_setting_notice_1_2_0() {

		$dismiss = get_option( 'abwc-ajax-dismiss' );
		if ( $dismiss ) {
			return;
		}

		$link = '<a href="' . admin_url( "options-general.php?page=abwc_ajaxified_settings" ) . '">' . __( "Settings", "abwc-ajax-cart" ) . '</a>';
		?>
		<div data-dismiss="true" class="notice notice-success is-dismissible">
			<p><?php _e( '<b>Ajaxified Cart WooCommerce:</b> Have you checked our new ' . $link . ' page!', 'abwc-ajax-cart' ); ?></p>
		</div>
		<?php
	}

	/**
	 * Ajax handler to dismiss admin notice
	 */
	public function abwc_dismiss_notice() {
		$dimiss = $_POST[ 'dismiss' ];

		if ( $dimiss ) {
			update_option( 'abwc-ajax-dismiss', 'yes' );
		}
	}

}
