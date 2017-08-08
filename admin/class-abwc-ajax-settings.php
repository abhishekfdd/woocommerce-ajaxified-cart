<?php
/*
 * Our ABWC_Ajax_Cart_Admin class
 * This adds settings for the plugin
 * 
 * @since 1.2.0
 */

/**
 * All the magic happens here.
 *
 * Class ABWC_Ajax_Cart_Admin
 */
class ABWC_Ajax_Cart_Admin {

	/**
	 * Plugin options
	 *
	 * @var array
	 */
	public $options = array();

	/**
	 * Initialize the __construct .
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'setup' ) );
	}

	/**
	 * Get option
	 *
	 * @since 1.2.0
	 *
	 * @param  string $key Option key
	 *
	 * @uses ABWC_Ajax_Cart_Admin::option() Get option
	 *
	 * @return mixed      Option value
	 */
	public function option( $key ) {
		$value = run_abwc_ajax_cart()->option( $key );
		return $value;
	}

	/**
	 * Setup admin class
	 *
	 * @since 1.2.0
	 *
	 * @uses run_abwc_ajax_cart() Get options from main Ajaxified_Admin class
	 * @uses is_admin() Ensures we're in the admin area
	 * @uses curent_user_can() Checks for permissions
	 * @uses add_action() Add hooks
	 */
	public function setup() {

		if ( ( ! is_admin() && ! is_network_admin() ) || ! current_user_can( 'manage_options' ) ) {
			return;
		}

		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'admin_init', array( $this, 'admin_init' ) );
		
		add_filter( 'plugin_action_links_' . plugin_basename( ABWC_AJAX_CART_PLUGIN_FILE ), array( $this, 'plugin_settings_link' ) );
	}

	/**
	 * Add plugin settings page
	 *
	 * @uses add_options_page() Add plugin settings page
	 */
	public function admin_menu() {
		add_submenu_page( 'options-general.php', __( 'Ajaxified Cart', 'abwc-ajax-cart' ), __( 'Ajaxified Cart', 'abwc-ajax-cart' ), 'manage_options', 'abwc_ajaxified_settings', array( $this, 'options_page' ) );
	}

	/**
	 * Register admin settings
	 *
	 * @since 1.2.0
	 *
	 * @uses register_setting() Register plugin options
	 * @uses add_settings_section() Add settings page option sections
	 * @uses add_settings_field() Add settings page option
	 */
	public function admin_init() {

		register_setting( 'abwc_ajax_plugin_options', 'abwc_ajax_plugin_options', array( $this, 'plugin_options_validate' ) );
		add_settings_section( 'general_section', __( 'General Settings', 'abwc-ajax-cart' ), array( $this, 'section_general' ), 'abwc_ajaxified_settings' );

		add_settings_field( 'enable_on_archive_page', __( 'Enable on archive page', 'abwc-ajax-cart' ), array( $this, 'enable_on_archive_page_option' ), 'abwc_ajaxified_settings', 'general_section' );
	}

	/**
	 * Html for enable archive option
	 */
	public function enable_on_archive_page_option() {

		$enable_on_archive = $this->option( 'enable_on_archive_page' );
		?>
		<input type='checkbox' <?php echo 'yes' == $enable_on_archive ? 'checked' : ''; ?> name='abwc_ajax_plugin_options[enable_on_archive_page]' value="yes" /> <?php _e( 'Enable ajaxified cart for variable products on archive page', 'abwc-ajax-cart' ); ?>
		<?php
	}

	/* Settings Page 
	 * ===================================================================
	 */

	/**
	 * Render settings page
	 *
	 * @uses do_settings_sections() Render settings sections
	 * @uses settings_fields() Render settings fields
	 * @uses esc_attr_e() Escape and localize text
	 */
	public function options_page() {
		?>
		<div class="wrap">
			<h2><?php _e( "Ajaxified Cart", "abwc-ajax-cart" ); ?></h2>
			<form action="options.php" method="post" class="abwc-ajax-settings-form">
				<?php
				settings_fields( 'abwc_ajax_plugin_options' );
				do_settings_sections( 'abwc_ajaxified_settings' );
				?>
				<p class="submit">
					<input name="Submit" type="submit" class="button-primary" value="<?php esc_attr_e( __( "Save Changes", "abwc-ajax-cart" ) ); ?>" />
				</p>

			</form>
		</div>

		<?php
	}

	/**
	 * Validate functionalities
	 */
	public function plugin_options_validate( $input ) {
		return $input; //no validations for now
	}

	/**
	 * General settings section
	 */
	public function section_general() {
		
	}
	
	/**
	 * Adds setting link
	 * 
	 * @param array $links
	 * @return string
	 */
	function plugin_settings_link( $links ) {
		$links[] = '<a href="' . admin_url( "options-general.php?page=abwc_ajaxified_settings" ) . '">' . __( "Settings", "abwc-ajax-cart" ) . '</a>';
		return $links;
	}

}
