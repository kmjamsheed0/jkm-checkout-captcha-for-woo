<?php
/**
 * Checkout Captcha for WooCommerce
 *
 * @author   Jamsheed KM
 * @since    1.0.0
 *
 * @package    jkm-checkout-captcha-for-woo
 * @subpackage jkm-checkout-captcha-for-woo/admin
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

if(!class_exists('JKMCCFW_Admin')) :
class JKMCCFW_Admin {

    private $screen_id;

     public function __construct() {
        add_action('admin_init', array($this, 'register_jkmccfw_settings'));
        add_action('update_option_jkmccfw_key', array($this, 'jkmccfw_keys_updated'), 10);
        add_action('update_option_jkmccfw_secret', array($this, 'jkmccfw_keys_updated'), 10);
    }

    public function jkmccfw_admin_menu() {
        $capability = JKMCCFW_Utils::jkmccfw_capability();
        $page_title = esc_html__('Checkout Captcha for WooCommerce', 'jkm-checkout-captcha-for-woo');
        $menu_title = esc_html__('Checkout Captcha', 'jkm-checkout-captcha-for-woo');
        
        $this->screen_id = add_submenu_page('woocommerce', $page_title, $menu_title, $capability, 'jkmccfw_settings', array($this, 'output_settings'));
    }

    public function add_screen_id($ids){
        $ids[] = 'woocommerce_page_jkmccfw_settings';
        $ids[] = strtolower(esc_html__('WooCommerce', 'jkm-checkout-captcha-for-woo')) .'_page_jkmccfw_settings';

        return $ids;
    }

    // public function get_current_tab(){
    //     return isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'wc_form';
    // }

    public function output_settings(){
        if (!current_user_can('manage_options')) {
            wp_die( __( 'You do not have sufficient permissions to access this page.','jkm-checkout-captcha-for-woo'));
        }

        echo '<div class="wrap">';
        echo '<h1>' . __('Checkout Settings', 'jkm-checkout-captcha-for-woo') . '</h1>';

        if (empty(get_option('jkmccfw_tested')) || get_option('jkmccfw_tested') != 'yes') {
            echo $this->jkmccfw_admin_test();
        } else {
            echo '<p style="font-weight: bold; color: green; margin-top: 28px;">
                    <span class="dashicons dashicons-yes-alt"></span> ' . __('Success! reCAPTCHA seems to be working correctly with your API keys.', 'jkm-checkout-captcha-for-woo') . '</p>';
        }

        echo '<form method="post" action="options.php">';
        settings_fields('jkmccfw-settings-group');
        do_settings_sections('jkmccfw-settings-group');
        
        // Settings form structure begins
        echo '<table class="form-table">';

        echo '<tr valign="top">
                <th scope="row" style="padding-bottom: 0;">
                    <p style="font-size: 19px; margin-top: 0;">' . __('API Key Settings:', 'jkm-checkout-captcha-for-woo') . '</p>
                    <p style="margin-bottom: 2px;">' . __('You can get your site key and secret from here:', 'jkm-checkout-captcha-for-woo') .
                    ' <a href="https://www.google.com/recaptcha/admin/create" target="_blank">https://www.google.com/recaptcha/admin/create</a></p>
                </th>
            </tr>';

        echo '</table><table class="form-table">';
        
        // Input fields for Site Key and Secret
        echo '<tr valign="top">
                <th scope="row">' . __('Site Key', 'jkm-checkout-captcha-for-woo') . ' (v2)</th>
                <td><input type="text" name="jkmccfw_key" value="' . esc_attr(get_option('jkmccfw_key')) . '" /></td>
              </tr>';

        echo '<tr valign="top">
                <th scope="row">' . __('Site Secret', 'jkm-checkout-captcha-for-woo') . ' (v2)</th>
                <td><input type="text" name="jkmccfw_secret" value="' . esc_attr(get_option('jkmccfw_secret')) . '" /></td>
              </tr>';

        // Dropdown for Theme Selection
        echo '<tr valign="top">
                <th scope="row">' . __('reCAPTCHA Theme', 'jkm-checkout-captcha-for-woo') . '</th>
                <td>
                    <select name="jkmccfw_theme">
                        <option value="light"' . (get_option('jkmccfw_theme') === "light" ? ' selected' : '') . '>' . esc_html__('Light', 'jkm-checkout-captcha-for-woo') . '</option>
                        <option value="dark"' . (get_option('jkmccfw_theme') === "dark" ? ' selected' : '') . '>' . esc_html__('Dark', 'jkm-checkout-captcha-for-woo') . '</option>
                    </select>
                </td>
              </tr>';

        // Checkbox options for WordPress Forms
        echo '<tr valign="top">
                <th scope="row" style="padding-bottom: 0;">
                    <p style="font-size: 19px; margin-top: 0; margin-bottom: 0;">' . __('WordPress Forms:', 'jkm-checkout-captcha-for-woo') . '</p>
                </th>
              </tr>';

        echo '<tr valign="top"><th scope="row">' . __('WordPress Login', 'jkm-checkout-captcha-for-woo') . '</th>
                <td><input type="checkbox" name="jkmccfw_login" ' . (get_option('jkmccfw_login') ? 'checked' : '') . '></td></tr>';

        echo '<tr valign="top"><th scope="row">' . __('WordPress Register', 'jkm-checkout-captcha-for-woo') . '</th>
                <td><input type="checkbox" name="jkmccfw_register" ' . (get_option('jkmccfw_register') ? 'checked' : '') . '></td></tr>';

        echo '<tr valign="top"><th scope="row">' . __('Reset Password', 'jkm-checkout-captcha-for-woo') . '</th>
                <td><input type="checkbox" name="jkmccfw_woo_reset" ' . (get_option('jkmccfw_woo_reset') ? 'checked' : '') . '></td></tr>';

        // WooCommerce Forms Section with Availability Check
        echo '<tr valign="top"><th scope="row" style="padding-bottom: 0;">
                <p style="font-size: 19px; margin-top: 0; margin-bottom: 0;">' . __('WooCommerce Forms:', 'jkm-checkout-captcha-for-woo') . '</p></th></tr>';

        $is_woocommerce_active = class_exists('WooCommerce') ? '' : 'style="opacity: 0.5; pointer-events: none;"';

        echo "<tr valign='top' $is_woocommerce_active>
                <th scope='row'>" . __('WooCommerce Login', 'jkm-checkout-captcha-for-woo') . "</th>
                <td><input type='checkbox' name='jkmccfw_woo_login' " . (get_option('jkmccfw_woo_login') ? 'checked' : '') . "></td></tr>";

        echo "<tr valign='top' $is_woocommerce_active>
                <th scope='row'>" . __('WooCommerce Register', 'jkm-checkout-captcha-for-woo') . "</th>
                <td><input type='checkbox' name='jkmccfw_woo_register' " . (get_option('jkmccfw_woo_register') ? 'checked' : '') . "></td></tr>";

        echo "<tr valign='top' $is_woocommerce_active>
                <th scope='row'>" . __('WooCommerce Checkout', 'jkm-checkout-captcha-for-woo') . "<br/><br/>" . __('Guest Checkout Only', 'jkm-checkout-captcha-for-woo') . "</th>
                <td><input type='checkbox' name='jkmccfw_woo_checkout' " . (get_option('jkmccfw_woo_checkout') ? 'checked' : '') . "><br/><br/>
                    <input type='checkbox' name='jkmccfw_guest_only' " . (get_option('jkmccfw_guest_only') ? 'checked' : '') . ">
                </td></tr>";

        // Position of the reCAPTCHA widget on Checkout
        echo "<tr valign='top' $is_woocommerce_active>
                <th scope='row'>" . __('Widget Location on Checkout', 'jkm-checkout-captcha-for-woo') . "</th>
                <td>
                    <select name='jkmccfw_woo_checkout_pos'>
                        <option value='beforepay'" . (get_option('jkmccfw_woo_checkout_pos') === "beforepay" ? ' selected' : '') . ">" . esc_html__('Before Payment', 'jkm-checkout-captcha-for-woo') . "</option>
                        <option value='afterpay'" . (get_option('jkmccfw_woo_checkout_pos') === "afterpay" ? ' selected' : '') . ">" . esc_html__('After Payment', 'jkm-checkout-captcha-for-woo') . "</option>
                        <option value='beforebilling'" . (get_option('jkmccfw_woo_checkout_pos') === "beforebilling" ? ' selected' : '') . ">" . esc_html__('Before Billing', 'jkm-checkout-captcha-for-woo') . "</option>
                        <option value='afterbilling'" . (get_option('jkmccfw_woo_checkout_pos') === "afterbilling" ? ' selected' : '') . ">" . esc_html__('After Billing', 'jkm-checkout-captcha-for-woo') . "</option>
                    </select>
                </td></tr>";
        echo '</table>';

         // Payment Methods Toggle Section
        if (class_exists('WooCommerce')) {
            $available_gateways = WC()->payment_gateways->get_available_payment_gateways();
            if (!empty($available_gateways)) {
                $selected_payment_methods = get_option('jkmccfw_selected_payment_methods', []);
                echo '<p style="font-size: 15px; font-weight: 600; margin-top: 0;">' . __('Payment Methods to Skip', 'jkm-checkout-captcha-for-woo') . '<span id="toggleButtonSkipMethods" class="dashicons dashicons-arrow-down" style="cursor:pointer;"></span></p>';
                echo '<div id="toggleContentSkipMethods" style="display: none;">';
                echo '<i style="font-size: 10px;">' . __("If selected below, reCAPTCHA check will not be run for that specific payment method.", 'jkm-checkout-captcha-for-woo') . '<br/>';
                echo __("Useful for 'Express Checkout' payment methods compatibility.", 'jkm-checkout-captcha-for-woo') . '</i>';
                echo '<div style="margin-top: 10px; max-width: 200px;">';
                foreach ($available_gateways as $gateway) {
                    $checked = in_array($gateway->id, $selected_payment_methods, true) ? 'checked' : '';
                    echo "<p><input type='checkbox' name='jkmccfw_selected_payment_methods[]' value='" . esc_attr($gateway->id) . "' $checked><label> " . __("Skip:", 'jkm-checkout-captcha-for-woo') . " " . esc_html($gateway->get_title()) . "</label></p>";
                }
                echo '</div></div>';
                echo '<script type="text/javascript">
                        document.getElementById("toggleButtonSkipMethods").addEventListener("click", function() {
                            var content = document.getElementById("toggleContentSkipMethods");
                            content.style.display = content.style.display === "none" ? "block" : "none";
                            this.className = content.style.display === "none" ? "dashicons dashicons-arrow-down" : "dashicons dashicons-arrow-up";
                        });
                      </script>';
            }
        }

        submit_button();
        echo '</form>';
        echo '</div>';
    }

    public function register_jkmccfw_settings() {
        $settings = [
            'jkmccfw_key', 'jkmccfw_secret', 'jkmccfw_theme', 'jkmccfw_login', 'jkmccfw_register',
            'jkmccfw_reset', 'jkmccfw_woo_checkout', 'jkmccfw_guest_only', 'jkmccfw_woo_login',
            'jkmccfw_woo_register', 'jkmccfw_woo_reset', 'jkmccfw_selected_payment_methods', 'jkmccfw_woo_checkout_pos'
        ];
        foreach ($settings as $setting) {
            register_setting('jkmccfw-settings-group', $setting);
        }
    }

    public function jkmccfw_keys_updated() {
        update_option('jkmccfw_tested', 'no');
    }

   // Admin test form to check reCAPTCHA response
   private function jkmccfw_admin_test() {
        ?>
        <form action="" method="POST">
        <?php
        if(!empty(get_option('jkmccfw_key')) && !empty(get_option('jkmccfw_secret'))) {
            $check = jkmccfw_recaptcha_check();
            $success = '';
            $error = '';
            if(isset($check['success'])) $success = $check['success'];
            if(isset($check['error_code'])) $error = $check['error_code'];
            echo '<br/><div style="padding: 20px 20px 25px 20px; background: #fff; border-radius: 20px; max-width: 500px; border: 2px solid #d5d5d5;">';
            if($success != true) {
                echo '<p style="font-weight: 600; font-size: 19px; margin-top: 0; margin-bottom: 0;">' . __( 'Almost done...', 'recaptcha-woo' ) . '</p>';
            }
            if(!isset($_POST['g-recaptcha-response'])) {
                echo '<p>'
                . '<span style="color: red; font-weight: bold;">' . __( 'API keys have been updated. Please test the reCAPTCHA API response below.', 'recaptcha-woo' ) . '</span>'
                . '<br/>'
                . __( 'reCAPTCHA will not be added to WP login until the test is successfully complete.', 'recaptcha-woo' )
                . '</p>';
            } else {
                if($success == true) {
                    echo '<p style="font-weight: bold; color: green; margin-top: -2px; margin-bottom: -4px;"><span class="dashicons dashicons-yes-alt"></span> ' . __( 'Success! reCAPTCHA seems to be working correctly with your API keys.', 'recaptcha-woo' ) . '</p>';
                    update_option('jkmccfw_tested', 'yes');
                } else {
                    if($error == "missing-input-response") {
                        echo '<p style="font-weight: bold; color: red;">' . esc_html__( 'Please verify that you are human.', 'recaptcha-woo' ) . '</p>';
                    } else {
                        echo '<p style="font-weight: bold; color: red;">' . esc_html__( 'Failed! There is an error with your API settings. Please check & update them.', 'recaptcha-woo' ) . '<br/>' . esc_html__( 'Error Code:', 'recaptcha-woo' ) . ' ' . $error . '</p>';
                    }
                }
                if($error) {
                    echo '<p style="font-weight: bold;">' . esc_html__( 'Error Message:', 'recaptcha-woo' ) . " " . esc_html__( 'Please verify that you are human.', 'recaptcha-woo' ) . '</p>';
                }
            }
            if($success != true) {
                echo '<div style="margin-left: 0;">';
                echo jkmccfw_field('', '');
                echo '</div><div style="margin-bottom: -20px;"></div>';
                echo '<button type="submit" style="margin-top: 10px; padding: 7px 10px; background: #1c781c; color: #fff; font-size: 15px; font-weight: bold; border: 1px solid #176017; border-radius: 4px; cursor: pointer;">
                '.__( 'TEST RESPONSE', 'recaptcha-woo' ).' <span class="dashicons dashicons-arrow-right-alt"></span>
                </button>';
            }
            echo '</div>';
        }
        ?>
        </form>
        <?php
    }


    public function plugin_action_links($links) {
        $settings_link = '<a href="'.admin_url('admin.php?page=jkmccfw_settings').'">'. esc_html__('Settings', 'jkm-checkout-captcha-for-woo') .'</a>';
        array_unshift($links, $settings_link);
        return $links;
    }

    /**
     * Redirects the user after plugin activation.
     */
    public function jkmccfw_activation_redirect() {
        // Make sure it's the correct user
        if ( !wp_doing_ajax() && intval( get_option( 'jkmccfw_activation_redirect', false ) ) === wp_get_current_user()->ID ) {
            // Make sure we don't redirect again after this one
            delete_option( 'jkmccfw_activation_redirect' );
            wp_safe_redirect(admin_url('admin.php?page=jkmccfw_settings'));
            exit;
        }
    }
}
endif;