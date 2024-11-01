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
        // add_action( 'woocommerce_product_options_related', array( $admin, 'jkmccfw_write_panel_tab' ) );
        // add_action( 'woocommerce_process_product_meta', array( $admin, 'jkmccfw_process_extra_product_meta' ), 1, 2 );
    }

    private function define_public_hooks() {
        // $public = new JKMCCFW_Public();

        // add_action('wp_enqueue_scripts', array($public, 'enqueue_public_styles_and_scripts'));

        // // Retrieve settings from the database
        // $options = get_option('jkmccfw_settings');
        // $hook_name = isset($options['display_position']) ? $options['display_position'] : 'woocommerce_before_add_to_cart_button';

        // //Product display related hooks:
        // $prd_display_hn = apply_filters('jkmccfw_products_display_hook_name', $hook_name);        
        // $prd_display_hp = apply_filters('jkmccfw_products_display_hook_priority', 10);

        // add_action( $prd_display_hn, array( $public, 'jkmccfw_show_force_sell_products' ), $prd_display_hp );

        // add_action( 'woocommerce_add_to_cart', array( $public, 'jkmccfw_add_force_sell_items_to_cart' ), 11, 6 );
        // add_action( 'woocommerce_after_cart_item_quantity_update', array( $public, 'jkmccfw_update_force_sell_quantity_in_cart' ), 1, 2 );
        // add_action( 'woocommerce_remove_cart_item', array( $public, 'jkmccfw_update_force_sell_quantity_in_cart' ), 1, 1 );
        // add_filter( 'woocommerce_get_cart_item_from_session', array( $public, 'jkmccfw_get_cart_item_from_session' ), 10, 2 );
        // add_filter( 'woocommerce_get_item_data', array( $public, 'jkmccfw_get_linked_to_product_data' ), 10, 2 );
        // add_action( 'woocommerce_cart_loaded_from_session', array( $public, 'jkmccfw_remove_orphan_force_sells' ) );
        // add_action( 'woocommerce_cart_loaded_from_session', array( $public, 'jkmccfw_maybe_remove_duplicate_force_sells' ) );
        // add_filter( 'woocommerce_cart_item_remove_link', array( $public, 'jkmccfw_cart_item_remove_link' ), 10, 2 );
        // add_filter( 'woocommerce_cart_item_quantity', array( $public, 'jkmccfw_cart_item_quantity' ), 10, 2 );
        // add_action( 'woocommerce_cart_item_removed', array( $public, 'jkmccfw_cart_item_removed' ), 30 );
        // add_action( 'woocommerce_cart_item_restored', array( $public, 'jkmccfw_cart_item_restored' ), 30 );
    }

}
endif;