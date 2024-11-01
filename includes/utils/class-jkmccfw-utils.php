<?php
/**
 * Checkout Captcha for WooCommerce
 *
 * @author   Jamsheed KM
 * @since    1.0.0
 *
 * @package    jkm-checkout-captcha-for-woo
 * @subpackage jkm-checkout-captcha-for-woo/includes/utils
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}
if(!class_exists('JKMCCFW_Utils')) :
class JKMCCFW_Utils {

    public static function jkmccfw_capability() {
        $allowed = array('manage_woocommerce', 'manage_options');
        $capability = apply_filters('jkmccfw_required_capability', 'manage_woocommerce');

        if(!in_array($capability, $allowed)){
            $capability = 'manage_woocommerce';
        }
        return $capability;
    }
}
endif;