<?php

/**
 * Register all actions and filters for the plugin
 *
 * @link       http://example.com
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
            
            add_action( 'wp_ajax_woocommerce_add_to_cart_variable_rc', array( $this, 'abwc_add_to_cart_variable_rc_callback' ) );
            add_action( 'wp_ajax_nopriv_woocommerce_add_to_cart_variable_rc', array( $this, 'abwc_add_to_cart_variable_rc_callback' ) );
            
            add_action( 'after_setup_theme', array( $this, 'abwc_variable_product_archive_ajax' ) );
            
	}
        
        /**
         * Adds hidden button with product atrributes next to add to cart button
         * 
         * @global type $product
         * 
         * @since    1.0.0
         */
        public function single_product_ajaxified_button() {
            
            global $product;
            
            if ( 'simple' == $product->product_type ) {
            
                echo apply_filters( 'abwc_add_to_cart_link',
                        sprintf( '<input type=hidden data-product_id="%s" data-product_sku="%s" class="abwc-ajax-btn button">',
                                esc_attr( $product->id ),
                                esc_attr( $product->get_sku() )
                        ),
                $product );
            
            }
            
        }
        
        /**
         * Ajax callback for variable products
         * 
         * @since    1.0.0
         */
        function abwc_add_to_cart_variable_rc_callback() {
            
            $product_id = apply_filters( 'woocommerce_add_to_cart_product_id', absint( $_POST['product_id'] ) );
            $quantity = empty( $_POST['quantity'] ) ? 1 : apply_filters( 'woocommerce_stock_amount', $_POST['quantity'] );
            $variation_id = $_POST['variation_id'];
            $variation  = $_POST['variation'];
            $passed_validation = apply_filters( 'woocommerce_add_to_cart_validation', true, $product_id, $quantity );

            if ( $passed_validation && WC()->cart->add_to_cart( $product_id, $quantity, $variation_id, $variation  ) ) {
                
                    do_action( 'woocommerce_ajax_added_to_cart', $product_id );
                    
                    if ( get_option( 'woocommerce_cart_redirect_after_add' ) == 'yes' ) {
                            wc_add_to_cart_message( $product_id );
                    }

                    // Return fragments
                    WC_AJAX::get_refreshed_fragments();
                    
            } else {
                    $this->json_headers();

                    // If there was an error adding to the cart, redirect to the product page to show any errors
                    $data = array(
                            'error' => true,
                            'product_url' => apply_filters( 'woocommerce_cart_redirect_after_error', get_permalink( $product_id ), $product_id )
                            );
                    echo json_encode( $data );
            }
            wp_die();
	}
        
        /**
         * Variable product ajax
         * 
         * Loads markup for woocomerce variable products in 
         * archive pages and prepares it for ajax.
         */
        function abwc_variable_product_archive_ajax() {
            
            $category_page = get_option( 'wc_ajax_add_to_cart_variable_category_page' );
            
            if( !isset($category_page) || ( isset($category_page) && $category_page != "yes" ) ) {
                return;
            }
                
                if ( ! function_exists( 'woocommerce_template_loop_add_to_cart' ) ) { 

                        /**
                         * Get the add to cart template for the loop.
                         *
                         * @subpackage	Loop
                         */
                        function woocommerce_template_loop_add_to_cart( $args = array() ) {
                                global $product;

                                if ( $product ) {
                                        $defaults = array(
                                                'quantity' => 1,
                                                'class'    => implode( ' ', array_filter( array(
                                                                'button',
                                                                'product_type_' . $product->product_type,
                                                                $product->is_purchasable() && $product->is_in_stock() ? 'add_to_cart_button' : '',
                                                                $product->supports( 'ajax_add_to_cart' ) ? 'ajax_add_to_cart' : ''
                                                ) ) )
                                        );

                                        $args = apply_filters( 'woocommerce_loop_add_to_cart_args', wp_parse_args( $args, $defaults ), $product );

                                        if ($product->product_type == "variable" ) {
                                                woocommerce_variable_add_to_cart();
                                        }
                                        else {
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
            
            wp_enqueue_script('abwc-ajax-js', ABWC_AJAX_CART_PLUGIN_URL . 'assets/js/abwc-ajax-cart.js', array( 'jquery' ), ABWC_AJAX_CART_PLUGIN_VERSION. TRUE );
            wp_enqueue_script('abwc-ajax-variation-js', ABWC_AJAX_CART_PLUGIN_URL . 'assets/js/abwc-ajax-variation-cart.js', array( 'jquery' ), ABWC_AJAX_CART_PLUGIN_VERSION. TRUE );
            
            
        }

}
