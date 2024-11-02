<?php
/**
 * Checkout Captcha for WooCommerce
 *
 * @author   Jamsheed KM
 * @since    1.0.0
 *
 * @package    jkm-checkout-captcha-for-woo
 * @subpackage jkm-checkout-captcha-for-woo/includes
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

if(!class_exists('JKMCCFW')) :
class JKMCCFW {
    
    private static $instance = null;
    private $screen_id;

    private function __construct() {
        $this->load_dependencies();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    public static function instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function load_dependencies() {
        require_once JKMCCFW_PATH . 'includes/utils/class-jkmccfw-utils.php';
        require_once JKMCCFW_PATH . 'admin/class-jkmccfw-admin.php';
        require_once JKMCCFW_PATH . 'admin/class-jkmccfw-admin-settings.php';
        require_once JKMCCFW_PATH . 'public/class-jkmccfw-public.php';
    }

    private function define_admin_hooks() {
        $admin = new JKMCCFW_Admin();
        add_action( 'admin_init', array($admin, 'jkmccfw_activation_redirect') );
        add_action('admin_menu', array($admin, 'jkmccfw_admin_menu'));
        add_filter('plugin_action_links_'.JKMCCFW_BASE_NAME, array($admin, 'plugin_action_links'));
        add_action( 'admin_enqueue_scripts', array($admin, 'jkmccfw_admin_script_enqueue') );
    }

    private function define_public_hooks() {
        $public = new JKMCCFW_Public();
        add_action('wp_enqueue_scripts', array($public, 'enqueue_checkout_public_styles_and_scripts'));
        add_action('login_enqueue_scripts', array($public, 'enqueue_login_script'));
        add_filter('script_loader_tag', array($public, 'add_defer_attribute'), 10, 2);
    }

}
endif;