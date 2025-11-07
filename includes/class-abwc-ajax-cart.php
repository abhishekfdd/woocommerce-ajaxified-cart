<?php
/**
 * ABWC Ajaxified Cart
 *
 * @package ABWC_Ajax_Cart
 */

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @since      1.0.0
 *
 * @package    ABWC_Ajax_Cart
 * @subpackage ABWC_Ajax_Cart/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    ABWC_Ajax_Cart
 * @subpackage ABWC_Ajax_Cart/includes
 * @author     Abhishek Kumar (abhishekfdd@gmail.com)
 */
class ABWC_Ajax_Cart {

	/**
	 * The contains all the admin settings
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      ABWC_Ajax_Cart_Admin $admin_settings All the admin settings for the plugin.
	 */
	protected $admin_settings;

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      ABWC_Ajax_Cart_Loader $loader Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var      string $plugin_name The string used to uniquely identify this plugin.
	 */
	public $plugin_name;

	/**
	 * This options array is setup during class instantiation, holds
	 * default and saved options for the plugin.
	 *
	 * @var array
	 */
	public $options = array();

	/**
	 * Default options for the plugin, the strings are
	 * run through localization functions during instantiation,
	 * and after the user saves options the first time they
	 * are loaded from the DB.
	 *
	 * @var array
	 */
	private $default_options = array();


	/* Singleton
	 * ===================================================================
	 */

	/**
	 * Main Ajaxified Cart Instance.
	 *
	 * Ensures that only one instance exists.
	 *
	 * @return ABWC_Ajax_Cart Instance of the plugin main class.
	 * @since 1.2.0
	 */
	public static function instance() {
		// Store the instance locally to avoid private static replication
		static $instance = null;

		// Only run these methods if they haven't been run previously
		if ( null === $instance ) {
			$instance = new ABWC_Ajax_Cart();
			$instance->setup();
		}

		// Always return the instance
		return $instance;
	}

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.2.0
	 */

	private function setup() {
		$this->plugin_name = 'woocommerce-ajaxified-cart';

		$this->load_dependencies();
		$this->set_locale();
	}

	/**
	 * A dummy constructor to prevent Ajaxified Cart from being loaded more than once.
	 *
	 * @since 1.0.0
	 * @see ABWC_Ajax_Cart::instance()
	 * @see abwc_ajax_cart_run()
	 */
	private function __construct() {
		/* nothing here */
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - ABWC_Ajax_Cart_Loader. Orchestrates the hooks of the plugin.
	 * - ABWC_Ajax_Cart_Admin. Defines admin settings functionality.
	 *
	 * Create an instance of the loader
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-abwc-ajax-cart-loader.php';

		/**
		 * The class responsible for admin settings
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-abwc-ajax-settings.php';

		$this->admin_settings = new ABWC_Ajax_Cart_Admin();
		$this->loader         = new ABWC_Ajax_Cart_Loader();

		$saved_options = get_option( 'abwc_ajax_plugin_options' );
		$saved_options = maybe_unserialize( $saved_options );

		$this->options = wp_parse_args( $saved_options, $this->default_options );

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 * No operation required (auto-loaded by WordPress.org since 4.6).
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {
		// No-op.
	}

	/**
	 * Convenience function to access plugin options, returns null if missing.
	 *
	 * @param string $key Option key.
	 * @return mixed|null Option value or null if not set.
	 * @since 1.2.0
	 */
	public function option( $key ) {
		$key    = strtolower( $key );
		$option = isset( $this->options[ $key ] ) ? $this->options[ $key ] : null;

		return $option;
	}

}
