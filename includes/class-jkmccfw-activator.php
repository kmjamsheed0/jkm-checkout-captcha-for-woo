<?php
/**
 * Fired during plugin activation.
 *
 * @author   Jamsheed KM
 * @since    1.0.0
 *
 * @package    jkm-checkout-captcha-for-woo
 * @subpackage jkm-checkout-captcha-for-woo/includes
 */
if(!defined('WPINC')){	die; }

if(!class_exists('JKMCCFW_Activator')):

class JKMCCFW_Activator {

	/**
	 * Initiate plugin activate
	 */
	public static function activate() {
		self::save_option_activation_redirect();
	}

	/**
	 * Plugin activation callback. Registers option to redirect on next admin load.
	 *
	 * Saves user ID to ensure it only redirects for the user who activated the plugin.
	 */
	public static function save_option_activation_redirect() {
		// Verify nonce before processing the request
	    if ( isset( $_POST['_wpnonce'] ) && ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'bulk-plugins' ) ) {
	        return;
	    }
		// Don't do redirects when multiple plugins are bulk activated
		if (
			( isset( $_REQUEST['action'] ) && 'activate-selected' === $_REQUEST['action'] ) &&
			( isset( $_POST['checked'] ) && count( $_POST['checked'] ) > 1 ) ) {
			return;
		}
		add_option( 'jkmccfw_activation_redirect', wp_get_current_user()->ID, '', false);
	}

}

endif;