<?php
/**
 * Plugin Name: Checkout Captcha for WooCommerce
 * Description: Adds a CAPTCHA to WooCommerce checkout to enhance security and prevent automated spam orders.
 * Author:      Jamsheed KM
 * Version:     1.0.0
 * Author URI:  https://github.com/kmjamsheed0
 * Plugin URI:  https://github.com/kmjamsheed0/jkm-checkout-captcha-for-woo
 * Text Domain: jkm-checkout-captcha-for-woo
 * Domain Path: /languages
 * License:		GPL-2.0-or-later
 * License URI:	https://www.gnu.org/licenses/gpl-2.0.html
 * WC requires at least: 4.0.0
 * WC tested up to: 9.4
 */

if(!defined('ABSPATH')){ exit; }

// Add HPOS and Remote Logging compatibility declarations
add_action('before_woocommerce_init', function() {
    if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('remote_logging', __FILE__, true);
    }
});

if (!function_exists('is_woocommerce_active')){
	function is_woocommerce_active(){
	    $active_plugins = (array) get_option('active_plugins', array());
	    if(is_multisite()){
		   $active_plugins = array_merge($active_plugins, get_site_option('active_sitewide_plugins', array()));
	    }
	    return in_array('woocommerce/woocommerce.php', $active_plugins) || array_key_exists('woocommerce/woocommerce.php', $active_plugins) || class_exists('WooCommerce');
	}
}

if(is_woocommerce_active()) {
	
}