<?php
/**
 * Uninstall script for Ajaxified Cart.
 *
 * Removes plugin options and transients.
 */
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Delete stored options.
delete_option( 'abwc_ajax_plugin_options' );
// Multisite cleanup.
if ( is_multisite() ) {
	global $wpdb;
	$blog_ids = $wpdb->get_col( "SELECT blog_id FROM {$wpdb->blogs}" );
	foreach ( $blog_ids as $blog_id ) {
		switch_to_blog( $blog_id );
		delete_option( 'abwc_ajax_plugin_options' );
		restore_current_blog();
	}
}

