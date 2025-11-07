<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
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
		// Block product button enhancement.
		add_filter( 'render_block', array( $this, 'abwc_filter_block_product_button' ), 10, 2 );
		// AJAX to fetch variation form for block product cards.
		add_action( 'wp_ajax_abwc_get_variable_form', array( $this, 'abwc_get_variable_form' ) );
		add_action( 'wp_ajax_nopriv_abwc_get_variable_form', array( $this, 'abwc_get_variable_form' ) );
		add_filter( 'woocommerce_blocks_product_grid_item_html', array( $this, 'abwc_blocks_product_grid_item_html' ), 10, 3 );

		add_action( 'wp_ajax_abwc_get_variable_form_by_slug', array( $this, 'abwc_get_variable_form_by_slug' ) );
		add_action( 'wp_ajax_nopriv_abwc_get_variable_form_by_slug', array( $this, 'abwc_get_variable_form_by_slug' ) );
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

			$raw_input = apply_filters(
				'abwc_add_to_cart_link',
				sprintf(
					'<input type="hidden" data-product_id="%s" data-product_sku="%s" class="abwc-ajax-btn button" />',
					esc_attr( $product->get_id() ),
					esc_attr( $product->get_sku() )
				),
				$product
			);
			$allowed = array(
				'input' => array(
					'type'            => true,
					'data-product_id' => true,
					'data-product_sku'=> true,
					'class'           => true,
				),
			);
			echo wp_kses( $raw_input, $allowed ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped via wp_kses.
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
			wp_send_json_error( array( 'product_url' => '', 'message' => __( 'Security check failed.', 'ajaxified-cart-woocommerce' ) ) );
		}

		$product_id   = isset( $_POST['product_id'] ) ? absint( wp_unslash( $_POST['product_id'] ) ) : 0; // Sanitized & unslashed.
		$quantity_raw = isset( $_POST['quantity'] ) ? sanitize_text_field( wp_unslash( $_POST['quantity'] ) ) : '1'; // Sanitize at source.
		$quantity_raw = preg_replace( '/[^0-9.]/', '', $quantity_raw ); // Keep numeric only.
		$quantity     = ( '' === $quantity_raw ) ? 1 : wc_stock_amount( $quantity_raw );
		$variation_id = isset( $_POST['variation_id'] ) ? absint( wp_unslash( $_POST['variation_id'] ) ) : 0; // Sanitized & unslashed.
		$variation    = array();

		$variation_input = ( isset( $_POST['variation'] ) && is_array( $_POST['variation'] ) ) ? wp_unslash( $_POST['variation'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitized below.
		if ( ! empty( $variation_input ) ) {
			foreach ( $variation_input as $attr_key => $attr_val ) {
				$sanitized_key = sanitize_title( $attr_key );
				$sanitized_val = sanitize_text_field( $attr_val );
				$variation[ $sanitized_key ] = $sanitized_val;
			}
		}

		// Ensure variation_id corresponds to provided product if both are set.
		if ( $variation_id && $product_id ) {
			$product_obj = wc_get_product( $product_id );
			if ( $product_obj && $product_obj->is_type( 'variable' ) ) {
				$valid_ids = wp_list_pluck( $product_obj->get_available_variations(), 'variation_id' );
				if ( ! in_array( $variation_id, $valid_ids, true ) ) {
					wp_send_json_error( array( 'message' => __( 'Invalid variation.', 'ajaxified-cart-woocommerce' ) ) );
				}
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
	 * Filter to enhance block product button with AJAX attributes.
	 *
	 * @param string $block_content The block content.
	 * @param array  $block        The block data.
	 * @return string Modified block content.
	 */
	public function abwc_filter_block_product_button( $block_content, $block ) {
		if ( empty( $block_content ) ) {
			return $block_content;
		}
		if ( isset( $block['blockName'] ) && 'woocommerce/product-button' === $block['blockName'] ) {
			global $product;
			if ( $product instanceof WC_Product && $product->is_type( 'variable' ) ) {
				// Ensure data-abwc-variable attribute is present on first anchor.
				if ( false === strpos( $block_content, 'data-abwc-variable="1"' ) ) {
					$block_content = preg_replace( '/<a\b([^>]*)>/', '<a$1 data-abwc-variable="1">', $block_content, 1 );
				}
			}
		}
		return $block_content;
	}

	/**
	 * AJAX handler to fetch variable product form.
	 *
	 * @since    1.0.0
	 */
	public function abwc_get_variable_form() {
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'abwc_add_to_cart' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed.', 'ajaxified-cart-woocommerce' ) ) );
		}
		$product_id  = isset( $_POST['product_id'] ) ? absint( wp_unslash( $_POST['product_id'] ) ) : 0; // Sanitized & unslashed.
		$product_obj = wc_get_product( $product_id );
		if ( ! $product_obj || ! $product_obj->is_type( 'variable' ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid product.', 'ajaxified-cart-woocommerce' ) ) );
		}
		global $product, $post; // Set globals for WooCommerce template functions.
		$product = $product_obj; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$post    = get_post( $product_id ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		if ( $post ) {
			setup_postdata( $post );
		}
		ob_start();
		woocommerce_variable_add_to_cart();
		$form_html = ob_get_clean();
		if ( $post ) {
			wp_reset_postdata();
		}
		wp_send_json_success( array( 'html' => $form_html ) );
	}

	/**
	 * AJAX handler to fetch variable product form by slug.
	 *
	 * @since    1.0.0
	 */
	public function abwc_get_variable_form_by_slug() {
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'abwc_add_to_cart' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed.', 'ajaxified-cart-woocommerce' ) ) );
		}
		$posted_slug = isset( $_POST['product_slug'] ) ? sanitize_title( wp_unslash( $_POST['product_slug'] ) ) : ''; // Sanitized & unslashed.
		if ( empty( $posted_slug ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid product slug.', 'ajaxified-cart-woocommerce' ) ) );
		}
		$post = get_page_by_path( $posted_slug, OBJECT, 'product' );
		if ( ! $post ) {
			$q = new WP_Query( array( 'post_type' => 'product', 'name' => $posted_slug, 'posts_per_page' => 1 ) );
			if ( $q->have_posts() ) {
				$post = $q->posts[0];
			}
			wp_reset_postdata();
		}
		if ( ! $post ) {
			wp_send_json_error( array( 'message' => __( 'Product not found.', 'ajaxified-cart-woocommerce' ) ) );
		}
		$product_obj = wc_get_product( $post->ID );
		if ( ! $product_obj || ! $product_obj->is_type( 'variable' ) ) {
			wp_send_json_error( array( 'message' => __( 'Not a variable product.', 'ajaxified-cart-woocommerce' ) ) );
		}
		global $product, $post; // Set globals for WooCommerce template functions.
		$product = $product_obj; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$post    = get_post( $product_obj->get_id() ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		if ( $post ) {
			setup_postdata( $post );
		}
		ob_start();
		woocommerce_variable_add_to_cart();
		$html = ob_get_clean();
		if ( $post ) {
			wp_reset_postdata();
		}
		wp_send_json_success( array( 'html' => $html, 'product_id' => $product_obj->get_id() ) );
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
		wp_enqueue_script( 'abwc-ajax-variation-js', $variation_file, array( 'jquery', 'wc-add-to-cart', 'wc-add-to-cart-variation' ), ABWC_AJAX_CART_PLUGIN_VERSION, true );

		// Localize security + data for variation ajax.
		wp_localize_script( 'abwc-ajax-variation-js', 'abwc_ajax_frontend', array(
			'nonce'    => wp_create_nonce( 'abwc_add_to_cart' ),
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'i18n' => array(
				'loading'           => __( 'Loading options…', 'ajaxified-cart-woocommerce' ),
				'close'             => __( 'Close', 'ajaxified-cart-woocommerce' ),
				'error'             => __( 'Unable to load options.', 'ajaxified-cart-woocommerce' ),
				'not_variable'      => __( 'Product is not variable.', 'ajaxified-cart-woocommerce' ),
				'product_missing'   => __( 'Product not found.', 'ajaxified-cart-woocommerce' ),
				'selectOptionsText' => __( 'Select options', 'ajaxified-cart-woocommerce' ),
			),
		) );
		wp_enqueue_style( 'abwc-modal', ABWC_AJAX_CART_PLUGIN_URL . 'assets/css/abwc-modal.css', array(), ABWC_AJAX_CART_PLUGIN_VERSION );
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

	/**
	 * Inject data attributes for variable products in block editor product grid.
	 *
	 * @param string $html    Original HTML.
	 * @param array  $data    Block data.
	 * @param WC_Product $product Product object.
	 * @return string Modified HTML.
	 */
	public function abwc_blocks_product_grid_item_html( $html, $data, $product ) {
		if ( ! $product instanceof WC_Product ) {
			return $html;
		}
		if ( ! $product->is_type( 'variable' ) ) {
			return $html;
		}
		$product_id = $product->get_id();
		// Add a hidden marker + enhance any product-button anchor/button tag.
		$marker = '<input type="hidden" class="abwc-block-variable-product" data-product_id="' . esc_attr( $product_id ) . '" data-abwc-variable="1" />';
		// Inject data attributes into the first anchor/button with product link if not already added.
		$pattern = '/<(a|button)\b([^>]*)(href|class)[^>]*>(.*?)<\/\1>/is';
		$modified = preg_replace_callback( $pattern, function( $matches ) use ( $product_id ) {
			$tag     = $matches[1];
			$attrs   = $matches[2];
			$content = $matches[4];
			if ( false === strpos( $attrs, 'data-product_id' ) ) {
				$attrs .= ' data-product_id="' . esc_attr( $product_id ) . '" data-abwc-variable="1"';
			}
			return '<' . $tag . $attrs . '>' . $content . '</' . $tag . '>';
		}, $html, 1 );
		if ( $modified ) {
			$html = $modified . $marker;
		} else {
			$html .= $marker; // fallback append.
		}
		return $html;
	}

}
