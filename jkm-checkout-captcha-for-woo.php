<?php
/**
 * Plugin Name: Checkout Captcha for WooCommerce
 * Description: Adds a CAPTCHA to WooCommerce checkout to enhance security and prevent automated spam orders.
 * Author:      Jamsheed KM
 * Version:     1.0.1
 * Author URI:  https://github.com/kmjamsheed0
 * Plugin URI:  https://github.com/kmjamsheed0/jkm-checkout-captcha-for-woo
 * Text Domain: jkm-checkout-captcha-for-woo
 * Domain Path: /languages
 * License:		GPL-2.0-or-later
 * License URI:	https://www.gnu.org/licenses/gpl-2.0.html
 * Requires Plugins: woocommerce
 * WC requires at least: 4.0.0
 * WC tested up to: 9.6
 */

if(!defined('ABSPATH')){ exit; }

// Add HPOS and Remote Logging compatibility declarations
add_action('before_woocommerce_init', function() {
    if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('remote_logging', __FILE__, true);
    }
});

if (!function_exists('jkmccfw_is_woocommerce_active')){
	function jkmccfw_is_woocommerce_active(){
	    $active_plugins = (array) get_option('active_plugins', array());
	    if(is_multisite()){
		   $active_plugins = array_merge($active_plugins, get_site_option('active_sitewide_plugins', array()));
	    }
	    return in_array('woocommerce/woocommerce.php', $active_plugins) || array_key_exists('woocommerce/woocommerce.php', $active_plugins) || class_exists('WooCommerce');
	}
}

if(jkmccfw_is_woocommerce_active()) {

	/**
	 * The code that runs during plugin activation.
	 */
	function jkmccfw_activate() {
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-jkmccfw-activator.php';
		JKMCCFW_Activator::activate();
	}
	
	/**
	 * The code that runs during plugin deactivation.
	 */
	function jkmccfw_deactivate() {
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-jkmccfw-deactivator.php';
		JKMCCFW_Deactivator::deactivate();
	}
	
	register_activation_hook( __FILE__, 'jkmccfw_activate' );
	register_deactivation_hook( __FILE__, 'jkmccfw_deactivate' );
	
	if(!class_exists('JKM_Checkout_Captcha_For_Woo')){
		class JKM_Checkout_Captcha_For_Woo {
			const TEXT_DOMAIN = 'jkm-checkout-captcha-for-woo';

			public function __construct(){
				add_action('init', array($this, 'init'));
			}

			public function init() {
				define('JKMCCFW_VERSION', '1.0.1');
				!defined('JKMCCFW_BASE_NAME') && define('JKMCCFW_BASE_NAME', plugin_basename( __FILE__ ));
				!defined('JKMCCFW_PATH') && define('JKMCCFW_PATH', plugin_dir_path( __FILE__ ));
				!defined('JKMCCFW_URL') && define('JKMCCFW_URL', plugins_url( '/', __FILE__ ));
				!defined('JKMCCFW_ADMIN_ASSETS_URL') && define('JKMCCFW_ADMIN_ASSETS_URL', JKMCCFW_URL .'admin/assets/');
				!defined('JKMCCFW_PUBLIC_ASSETS_URL') && define('JKMCCFW_PUBLIC_ASSETS_URL', JKMCCFW_URL .'public/assets/');

				$this->load_plugin_textdomain();

				require_once( JKMCCFW_PATH . 'includes/class-jkmccfw.php' );
				JKMCCFW::instance();
			}

			public function load_plugin_textdomain(){
				$locale = apply_filters('plugin_locale', get_locale(), self::TEXT_DOMAIN);

				load_textdomain(self::TEXT_DOMAIN, WP_LANG_DIR.'/jkm-checkout-captcha-for-woo/'.self::TEXT_DOMAIN.'-'.$locale.'.mo');
				load_plugin_textdomain(self::TEXT_DOMAIN, false, dirname(JKMCCFW_BASE_NAME) . '/languages/');
			}
		}
	}
	new JKM_Checkout_Captcha_For_Woo();
}