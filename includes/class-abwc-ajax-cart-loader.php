<?php
/**
 * Register all actions and filters for the plugin
 *
 * @link       https://wordpress.org/plugins/ajaxified-cart-woocommerce/
 * @since      1.0.0
 *
 * @package    ABWC_Ajax_Cart
 * @subpackage ABWC_Ajax_Cart/includes
 */

/**
 * Register all actions and filters for the plugin.
 *
 * Maintain a list of all hooks that are registered throughout
 * the plugin. Call the run function to execute the
 * list of actions and filters.
 *
 * @package    ABWC_Ajax_Cart
 * @subpackage ABWC_Ajax_Cart/includes
 * @author     Abhishek Kumar <abhishekfdd@gmail.com>
 */
class ABWC_Ajax_Cart_Loader {

	/**
	 * Initialize the collections .
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		add_action( 'woocommerce_after_add_to_cart_button', array( $this, 'single_product_ajaxified_button' ) );

		add_action( 'wp_enqueue_scripts', array( $this, 'assets' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_assets' ) );

		add_action( 'wp_ajax_woocommerce_add_to_cart_variable_rc', array(
			$this,
			'abwc_add_to_cart_variable_rc_callback'
		) );

		add_action( 'wp_ajax_nopriv_woocommerce_add_to_cart_variable_rc', array(
			$this,
			'abwc_add_to_cart_variable_rc_callback'
		) );

		add_action( 'after_setup_theme', array( $this, 'abwc_variable_product_archive_ajax' ) );
	}

	/**
	 * Adds hidden button with product attributes next to add to cart button
	 *
	 * @global WC_Product $product
	 *
	 * @since    1.0.0
	 */
	public function single_product_ajaxified_button() {

		global $product;

		if ( 'simple' === $product->get_type() ) {

			echo apply_filters( 'abwc_add_to_cart_link', sprintf( '<input type=hidden data-product_id="%s" data-product_sku="%s" class="abwc-ajax-btn button">', esc_attr( $product->get_id() ), esc_attr( $product->get_sku() )
			), $product );
		}
	}

	/**
	 * Ajax callback for variable products
	 *
	 * @since    1.0.0
	 */
	function abwc_add_to_cart_variable_rc_callback() {

		// Security: verify nonce.
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'abwc_add_to_cart' ) ) {
			wp_send_json( array( 'error' => true, 'product_url' => '', 'message' => __( 'Security check failed.', 'abwc-ajax-cart' ) ) );
		}

		$product_id   = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
		$quantity_raw = isset( $_POST['quantity'] ) ? wp_unslash( $_POST['quantity'] ) : 1;
		$quantity     = empty( $quantity_raw ) ? 1 : wc_stock_amount( wc_clean( $quantity_raw ) );
		$variation_id = isset( $_POST['variation_id'] ) ? absint( $_POST['variation_id'] ) : 0;
		$variation    = array();

		if ( isset( $_POST['variation'] ) && is_array( $_POST['variation'] ) ) {
			foreach ( $_POST['variation'] as $attr_key => $attr_val ) {
				$variation[ sanitize_title( wp_unslash( $attr_key ) ) ] = sanitize_text_field( wp_unslash( $attr_val ) );
			}
		}

		$passed_validation = apply_filters( 'woocommerce_add_to_cart_validation', true, $product_id, $quantity );

		if ( $passed_validation && WC()->cart->add_to_cart( $product_id, $quantity, $variation_id, $variation ) ) {

			do_action( 'woocommerce_ajax_added_to_cart', $product_id );

			if ( 'yes' === get_option( 'woocommerce_cart_redirect_after_add' ) ) {
				wc_add_to_cart_message( $product_id );
			}

			WC_AJAX::get_refreshed_fragments();
		} else {
			$data = array(
				'error'       => true,
				'product_url' => apply_filters( 'woocommerce_cart_redirect_after_error', get_permalink( $product_id ), $product_id ),
			);
			wp_send_json( $data );
		}
		wp_die();
	}

	/**
	 * Variable product ajax
	 *
	 * Loads markup for WooCommerce variable products in
	 * archive pages and prepares it for ajax.
	 */
	function abwc_variable_product_archive_ajax() {

		$category_page = run_abwc_ajax_cart()->option( 'enable_on_archive_page' );

		if ( ! isset( $category_page ) || ( isset( $category_page ) && 'yes' !== $category_page ) ) {
			return;
		}

		if ( ! function_exists( 'woocommerce_template_loop_add_to_cart' ) ) {

			/**
			 * Get the add to cart template for the loop.
			 *
			 * @param array $args args for the function.
			 *
			 * @subpackage    Loop
			 *
			 */
			function woocommerce_template_loop_add_to_cart( $args = array() ) {
				global $product;

				if ( $product ) {
					$defaults = array(
						'quantity'   => 1,
						'class'      => implode( ' ', array_filter( array(
							'button',
							'product_type_' . $product->get_type(),
							$product->is_purchasable() && $product->is_in_stock() ? 'add_to_cart_button' : '',
							$product->supports( 'ajax_add_to_cart' ) ? 'ajax_add_to_cart' : '',
						) ) ),
						'attributes' => array(
							'data-product_id'  => $product->get_id(),
							'data-product_sku' => $product->get_sku(),
							'aria-label'       => $product->add_to_cart_description(),
							'rel'              => 'nofollow',
						),
					);

					$args = apply_filters( 'woocommerce_loop_add_to_cart_args', wp_parse_args( $args, $defaults ), $product );

					if ( 'variable' === $product->get_type() ) {
						woocommerce_variable_add_to_cart();
					} else {
						wc_get_template( 'loop/add-to-cart.php', $args );
					}
				}
			}
		}
	}

	/**
	 * Loading js required for this plugin
	 *
	 * @since    1.0.0
	 */
	public function assets() {

		$dist_path = ABWC_AJAX_CART_PLUGIN_DIR . 'assets/js/dist/';
		$dist_url  = ABWC_AJAX_CART_PLUGIN_URL . 'assets/js/dist/';

		$main_file      = is_readable( $dist_path . 'abwc-ajax-cart.min.js' ) ? $dist_url . 'abwc-ajax-cart.min.js' : ABWC_AJAX_CART_PLUGIN_URL . 'assets/js/abwc-ajax-cart.js';
		$variation_file = is_readable( $dist_path . 'abwc-ajax-variation-cart.min.js' ) ? $dist_url . 'abwc-ajax-variation-cart.min.js' : ABWC_AJAX_CART_PLUGIN_URL . 'assets/js/abwc-ajax-variation-cart.js';

		wp_enqueue_script( 'abwc-ajax-js', $main_file, array( 'jquery', 'wc-add-to-cart' ), ABWC_AJAX_CART_PLUGIN_VERSION, true );
		wp_enqueue_script( 'abwc-ajax-variation-js', $variation_file, array( 'jquery', 'wc-add-to-cart' ), ABWC_AJAX_CART_PLUGIN_VERSION, true );

		// Localize security + data for variation ajax.
		wp_localize_script( 'abwc-ajax-variation-js', 'abwc_ajax_frontend', array(
			'nonce' => wp_create_nonce( 'abwc_add_to_cart' ),
		) );
	}

	/**
	 * Loading admin js required for this plugin
	 *
	 * @since    1.0.0
	 */
	public function admin_assets() {
		$dist_path = ABWC_AJAX_CART_PLUGIN_DIR . 'assets/js/dist/';
		$dist_url  = ABWC_AJAX_CART_PLUGIN_URL . 'assets/js/dist/';
		$admin_file = is_readable( $dist_path . 'abwc-ajax-cart-admin.min.js' ) ? $dist_url . 'abwc-ajax-cart-admin.min.js' : ABWC_AJAX_CART_PLUGIN_URL . 'assets/js/abwc-ajax-cart-admin.js';
		wp_enqueue_script( 'abwc-ajax-admin-js', $admin_file, array( 'jquery' ), ABWC_AJAX_CART_PLUGIN_VERSION, true );
		wp_localize_script( 'abwc-ajax-admin-js', 'abwc_ajax_data', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
	}

}
