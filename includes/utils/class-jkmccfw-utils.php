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

    // Check the reCAPTCHA on submit.
    public static function jkmccfw_recaptcha_check() {
        $postdata = "";
        if(isset($_POST['g-recaptcha-response'])) {
            $postdata = sanitize_text_field( wp_unslash( $_POST['g-recaptcha-response'] ) );
        }

        $key = esc_attr( get_option('jkmccfw_key') );
        $secret = esc_attr( get_option('jkmccfw_secret') );
        $guest = esc_attr( get_option('jkmccfw_guest_only') );

        if($key && $secret) {

            $verify = wp_remote_get( 'https://www.google.com/recaptcha/api/siteverify?secret=' . $secret . '&response=' . $postdata );
            $verify = wp_remote_retrieve_body( $verify );
            $response = json_decode($verify);

            $results['success'] = $response->success;

            foreach($response as $key => $val) {
                if($key == 'error-codes') {
                    foreach($val as $key => $error_val) {
                        $results['error_code'] = $error_val;
                    }
                }
            }

            return $results;

        } else {

            return false;

        }
    }

    // Admin test form to check reCAPTCHA response
    public static function jkmccfw_admin_test() {
        ?>
        <form action="" method="POST" class="jkmccfw-admin-form">
            <?php
            if (!empty(get_option('jkmccfw_key')) && !empty(get_option('jkmccfw_secret'))) {
                $check = self::jkmccfw_recaptcha_check();
                $success = '';
                $error = '';
                if (isset($check['success'])) $success = $check['success'];
                if (isset($check['error_code'])) $error = $check['error_code'];

                echo '<div class="jkmccfw-admin-form-container">';
                if ($success != true) {
                    echo '<p class="jkmccfw-form-title">' . esc_html__('Almost done...', 'jkm-checkout-captcha-for-woo') . '</p>';
                }
                if (!isset($_POST['g-recaptcha-response'])) {
                    echo '<p class="jkmccfw-info-text">'
                        . '<span class="jkmccfw-warning">' . esc_html__('API keys have been updated. Please test the reCAPTCHA API response below.', 'jkm-checkout-captcha-for-woo') . '</span>'
                        . '<br/>'
                        . esc_html__('reCAPTCHA will not be added to WP login until the test is successfully complete.', 'jkm-checkout-captcha-for-woo')
                        . '</p>';
                } else {
                    if ($success == true) {
                        echo '<p class="jkmccfw-success-message"><span class="dashicons dashicons-yes-alt"></span> ' . esc_html__('Success! reCAPTCHA seems to be working correctly with your API keys.', 'jkm-checkout-captcha-for-woo') . '</p>';
                        update_option('jkmccfw_tested', 'yes');
                    } else {
                        if ($error == "missing-input-response") {
                            echo '<p class="jkmccfw-error">' . esc_html__('Please verify that you are human.', 'jkm-checkout-captcha-for-woo') . '</p>';
                        } else {
                            echo '<p class="jkmccfw-error">' . esc_html__('Failed! There is an error with your API settings. Please check & update them.', 'jkm-checkout-captcha-for-woo') . '<br/>' . esc_html__('Error Code:', 'jkm-checkout-captcha-for-woo') . ' ' . esc_html($error) . '</p>';
                        }
                    }
                    if ($error) {
                        echo '<p class="jkmccfw-error">' . esc_html__('Error Message:', 'jkm-checkout-captcha-for-woo') . " " . esc_html__('Please verify that you are human.', 'jkm-checkout-captcha-for-woo') . '</p>';
                    }
                }
                if ($success != true) {
                    echo '<div class="jkmccfw-field-wrapper">';
                    echo esc_html( self::jkmccfw_field('', '') );
                    echo '</div>';
                    echo '<button type="submit" class="jkmccfw-submit-btn">'
                        . esc_html__('TEST RESPONSE', 'jkm-checkout-captcha-for-woo') . ' <span class="dashicons dashicons-arrow-right-alt"></span>'
                        . '</button>';
                }
                echo '</div>';
            }
            ?>
        </form>
        <?php
    }

    // Field
    public static function jkmccfw_field() {
        $key = esc_attr( get_option('jkmccfw_key') );
        $secret = esc_attr( get_option('jkmccfw_secret') );
        $theme = esc_attr( get_option('jkmccfw_theme') );
        if($key && $secret) {
            ?>
            <div class="g-recaptcha" <?php if($theme == "dark") { ?>data-theme="dark" <?php } ?>data-sitekey="<?php echo esc_attr($key); ?>"></div>
            <br/>
            <?php
        }
    }

    // Field WP Admin
    public static function jkmccfw_field_admin() {
        $key = esc_attr( get_option('jkmccfw_key') );
        $secret = esc_attr( get_option('jkmccfw_secret') );
        $theme = esc_attr( get_option('jkmccfw_theme') );
        if($key && $secret) {
            ?>
            <div style="margin-left: -15px;" class="g-recaptcha" <?php if($theme == "dark") { ?>data-theme="dark" <?php } ?>data-sitekey="<?php echo esc_attr($key); ?>"></div>
            <br/>
            <?php
        }
    }

    // Field Checkout
    public static function jkmccfw_field_checkout($checkout) {
        $key = esc_attr( get_option('jkmccfw_key') );
        $secret = esc_attr( get_option('jkmccfw_secret') );
        $theme = esc_attr( get_option('jkmccfw_theme') );
        $guest = esc_attr( get_option('jkmccfw_guest_only') );
        
        if(get_option('jkmccfw_woo_checkout_pos') == "afterpay") {
            echo "<br/>";
        }
        
        if( !$guest || ( $guest && !is_user_logged_in() ) ) {
            if($key && $secret) {
            ?>
            <div class="g-recaptcha" <?php if($theme == "dark") { ?>data-theme="dark" <?php } ?>data-sitekey="<?php echo esc_attr($key); ?>"></div>
            <br/>
            <?php
            }
        }
    }

}
endif;