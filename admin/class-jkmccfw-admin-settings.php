<?php
/**
 * Admin Settings Handler for Checkout Captcha
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('JKMCCFW_Admin_Settings')) :
    class JKMCCFW_Admin_Settings {
        /**
         * Holds the settings tabs
         */
        private $tabs = array();
        
        /**
         * Constructor
         */
        public function __construct() {
            $this->init_tabs();
        }

        /**
         * Initialize settings tabs
         */
        private function init_tabs() {
            $this->tabs = array(
                'woocommerce' => array(
                    'label' => __('WooCommerce Forms', 'jkm-checkout-captcha-for-woo'),
                    'callback' => array($this, 'render_woocommerce_settings')
                ),
                'wordpress' => array(
                    'label' => __('WordPress Forms', 'jkm-checkout-captcha-for-woo'),
                    'callback' => array($this, 'render_wordpress_settings')
                ),
                'settings' => array(
                    'label' => __('Configure', 'jkm-checkout-captcha-for-woo'),
                    'callback' => array($this, 'render_configure_settings')
                )
            );
        }

        /**
         * Get current active tab
         */
        public function get_current_tab() {
            return isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'woocommerce';
        }

        /**
         * Render tabs navigation
         */
        public function render_tabs() {
            $current_tab = $this->get_current_tab();
            echo '<h2 class="nav-tab-wrapper">';
            foreach ($this->tabs as $tab_key => $tab_data) {
                $active = ($current_tab === $tab_key) ? 'nav-tab-active' : '';
                echo sprintf(
                    '<a href="?page=jkmccfw_settings&tab=%s" class="nav-tab %s">%s</a>',
                    esc_attr($tab_key),
                    esc_attr($active),
                    esc_html($tab_data['label'])
                );
            }
            echo '</h2>';
        }

        /**
         * Render WooCommerce settings
         */
        public function render_woocommerce_settings() {
            $is_woocommerce_active = class_exists('WooCommerce');
            ?>
            <div id="tab-woocommerce" class="tab-content">
            <table class="form-table">
                <tr valign="top" class="<?php echo (!$is_woocommerce_active ? 'jkmccfw-disabled-row' : ''); ?>">
                    <th scope="row"><?php esc_html_e('WooCommerce Login', 'jkm-checkout-captcha-for-woo'); ?></th>
                    <td>
                        <input type="checkbox" name="jkmccfw_woo_login" <?php checked(get_option('jkmccfw_woo_login') == 'on'); ?>>
                    </td>
                </tr>
                <tr valign="top" class="<?php echo (!$is_woocommerce_active ? 'jkmccfw-disabled-row' : ''); ?>">
                    <th scope="row"><?php esc_html_e('WooCommerce Register', 'jkm-checkout-captcha-for-woo'); ?></th>
                    <td>
                        <input type="checkbox" name="jkmccfw_woo_register" <?php checked(get_option('jkmccfw_woo_register') == 'on'); ?>>
                    </td>
                </tr>
                <tr valign="top" class="<?php echo (!$is_woocommerce_active ? 'jkmccfw-disabled-row' : ''); ?>">
                    <th scope="row">
                        <?php esc_html_e('WooCommerce Checkout', 'jkm-checkout-captcha-for-woo'); ?><br/><br/>
                        <?php esc_html_e('Guest Checkout Only', 'jkm-checkout-captcha-for-woo'); ?>
                    </th>
                    <td>
                        <input type="checkbox" name="jkmccfw_woo_checkout" <?php checked(get_option('jkmccfw_woo_checkout') == 'on' ); ?>><br/><br/>
                        <input type="checkbox" name="jkmccfw_guest_only" <?php checked(get_option('jkmccfw_guest_only') == 'on'); ?>>
                    </td>
                </tr>
                <tr valign="top" class="<?php echo (!$is_woocommerce_active ? 'jkmccfw-disabled-row' : ''); ?>">
                    <th scope="row" style="padding-top: 0px;">
                        <?php esc_html_e('Widget Location on Checkout', 'jkm-checkout-captcha-for-woo'); ?>
                    </th>
                    <td style="padding-top: 0px;">
                        <select name="jkmccfw_woo_checkout_pos" class="jkmccfw-select">
                            <option value="beforepay" <?php selected(get_option('jkmccfw_woo_checkout_pos'), 'beforepay', true); ?>>
                                <?php esc_html_e('Before Payment', 'jkm-checkout-captcha-for-woo'); ?>
                            </option>
                            <option value="afterpay" <?php selected(get_option('jkmccfw_woo_checkout_pos'), 'afterpay', true); ?>>
                                <?php esc_html_e('After Payment', 'jkm-checkout-captcha-for-woo'); ?>
                            </option>
                            <option value="beforebilling" <?php selected(get_option('jkmccfw_woo_checkout_pos'), 'beforebilling', true); ?>>
                                <?php esc_html_e('Before Billing', 'jkm-checkout-captcha-for-woo'); ?>
                            </option>
                            <option value="afterbilling" <?php selected(get_option('jkmccfw_woo_checkout_pos'), 'afterbilling', true); ?>>
                                <?php esc_html_e('After Billing', 'jkm-checkout-captcha-for-woo'); ?>
                            </option>
                        </select>
                    </td>
                </tr>
                <?php $this->render_payment_methods_section(); ?>
            </table>
            </div>
            <?php
        }

        /**
         * Render WordPress settings
         */
        public function render_wordpress_settings() {
            ?>
            <div id="tab-wordpress" class="tab-content" style="display:none;">
            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><?php esc_html_e('WordPress Login', 'jkm-checkout-captcha-for-woo'); ?></th>
                    <td>
                        <input type="checkbox" name="jkmccfw_login" <?php checked(get_option('jkmccfw_login') == 'on'); ?>>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php esc_html_e('WordPress Register', 'jkm-checkout-captcha-for-woo'); ?></th>
                    <td>
                        <input type="checkbox" name="jkmccfw_register" <?php checked(get_option('jkmccfw_register') == 'on'); ?>>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php esc_html_e('Reset Password', 'jkm-checkout-captcha-for-woo'); ?></th>
                    <td>
                        <input type="checkbox" name="jkmccfw_woo_reset" <?php checked(get_option('jkmccfw_woo_reset') == 'on'); ?>>
                    </td>
                </tr>
            </table>
            </div>
            <?php
        }

        /**
         * Render configuration settings
         */
        public function render_configure_settings() {
            ?>
            <div id="tab-settings" class="tab-content" style="display:none;">
            <table class="form-table">
                <tr valign="top">
                    <th scope="row" colspan="2">
                        <p class="description">
                            <?php esc_html_e('You can get your site key and secret from here:', 'jkm-checkout-captcha-for-woo'); ?>
                            <a href="https://www.google.com/recaptcha/admin/create" target="_blank">https://www.google.com/recaptcha/admin/create</a>
                        </p>
                    </th>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php esc_html_e('Site Key (v2)', 'jkm-checkout-captcha-for-woo'); ?></th>
                    <td>
                        <input type="text" name="jkmccfw_key" value="<?php echo esc_attr(get_option('jkmccfw_key')); ?>" class="regular-text">
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php esc_html_e('Site Secret (v2)', 'jkm-checkout-captcha-for-woo'); ?></th>
                    <td>
                        <input type="text" name="jkmccfw_secret" value="<?php echo esc_attr(get_option('jkmccfw_secret')); ?>" class="regular-text">
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php esc_html_e('reCAPTCHA Theme', 'jkm-checkout-captcha-for-woo'); ?></th>
                    <td>
                        <select name="jkmccfw_theme">
                            <option value="light" <?php selected(get_option('jkmccfw_theme'), 'light', true); ?>><?php esc_html_e('Light', 'jkm-checkout-captcha-for-woo'); ?></option>
                            <option value="dark" <?php selected(get_option('jkmccfw_theme'), 'dark', true); ?>><?php esc_html_e('Dark', 'jkm-checkout-captcha-for-woo'); ?></option>
                        </select>
                    </td>
                </tr>
            </table>
            </div>
            <?php
        }

        /**
         * Render payment methods section
         */
        private function render_payment_methods_section() {

            if (!class_exists('WooCommerce')) {
                return;
            }

            $available_gateways = WC()->payment_gateways->get_available_payment_gateways();

            if (empty($available_gateways)) {
                return;
            }

            // Ensure $selected_payment_methods is always an array
            $selected_payment_methods = get_option('jkmccfw_selected_payment_methods', []);
            if (!is_array($selected_payment_methods)) {
                $selected_payment_methods = [];
            }
            ?>

            <tr valign="top">
                <th scope="row">
                    <?php esc_html_e('Payment Methods to Skip', 'jkm-checkout-captcha-for-woo'); ?>
                    <span class="dashicons dashicons-info tooltip" aria-label="<?php esc_attr_e('Skip reCAPTCHA for the following payment methods to enhance Express Checkout compatibility.', 'jkm-checkout-captcha-for-woo'); ?>"></span>
                    <span class="tooltip-text">
                        <?php esc_html_e('Skip reCAPTCHA for the following payment methods to enhance Express Checkout compatibility.', 'jkm-checkout-captcha-for-woo'); ?>
                    </span>
                </th>

                <td class="jkmccfw-payment-methods-td">
                    <div class="jkmccfw-payment-methods__div">
                    <div class="payment-methods-wrapper">
                        <p class="description">
                            <?php esc_html_e('Select payment methods to skip reCAPTCHA verification', 'jkm-checkout-captcha-for-woo'); ?>
                        </p>
                        <?php foreach ($available_gateways as $gateway) : ?>
                            <label>
                                <input type="checkbox" 
                                       name="jkmccfw_selected_payment_methods[]" 
                                       value="<?php echo esc_attr($gateway->id); ?>"
                                       <?php checked(in_array($gateway->id, $selected_payment_methods, true)); ?> />
                                <?php echo esc_html($gateway->get_title()); ?>
                            </label><br>
                        <?php endforeach; ?>
                    </div>
                    </div>
                </td>
            </tr>

            <?php
        }
    }
endif;