<?php
/**
 * Checkout Captcha for WooCommerce
 *
 * @package jkm-checkout-captcha-for-woo
 * @subpackage jkm-checkout-captcha-for-woo/public
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

if (!class_exists('JKMCCFW_Public')) :
    class JKMCCFW_Public {

        public function __construct() {

            // Conditionally hook additional actions based on options
            if (!empty(get_option('jkmccfw_key')) && !empty(get_option('jkmccfw_secret'))) {
                $this->conditionally_hook_login();
                $this->conditionally_hook_register();
                $this->conditionally_hook_reset();
                $this->conditionally_hook_woocommerce();
            }
        }

        // Enqueue scripts for WooCommerce account/checkout pages
        public function enqueue_checkout_public_styles_and_scripts() {
            if (is_woocommerce_active()) {
                if (is_checkout() || is_account_page()) {
                    $this->enqueue_scripts();
                }
            }
        }

        // Enqueue scripts for login page
        public function enqueue_login_script() {
            $this->enqueue_scripts();
        }

        // Add defer attribute to specific scripts
        public function add_defer_attribute($tag, $handle) {
            if (in_array($handle, ['jkmccfw-public-script', 'recaptcha'])) {
                return str_replace(' src', ' defer src', $tag);
            }
            return $tag;
        }

        // Main enqueue script method
        private function enqueue_scripts() {
            $debug_mode = apply_filters('jkmfs_debug_mode', false);
            $suffix = $debug_mode ? '' : '.min';

            wp_enqueue_script(
                'jkmccfw-public-script',
                JKMCCFW_PUBLIC_ASSETS_URL . 'js/jkmccfw-public'. $suffix .'.js',
                array('jquery'),
                JKMCCFW_VERSION,
                true
            );
            wp_enqueue_script(
                'recaptcha',
                'https://www.google.com/recaptcha/api.js?explicit&hl=' . get_locale(),
                array(),
                JKMCCFW_VERSION,
                true
            );
        }

        // WP Login reCAPTCHA
        private function conditionally_hook_login() {
            if (get_option('jkmccfw_login') && get_option('jkmccfw_tested') == 'yes') {
                add_action('login_form', array('JKMCCFW_Utils', 'jkmccfw_field_admin'));
                add_action('authenticate', array($this, 'check_login_recaptcha'), 21, 1);
            }
            // Clear session on login
            add_action('wp_login', array($this, 'clear_login_session'), 10, 2);
        }

        public function check_login_recaptcha($user) {
            if (!session_id()) { session_start(); }

            if (!isset($user->ID) || $this->skip_recaptcha_checks($user)) { return $user; }

            if(isset($_POST['woocommerce-login-nonce']) && wp_verify_nonce(sanitize_text_field( wp_unslash( $_POST['woocommerce-login-nonce'])), 'woocommerce-login')) { return $user; } // Skip Woo
            if(is_wp_error($user) && isset($user->errors['empty_username']) && isset($user->errors['empty_password']) ) {return $user; } // Skip Errors

            if (isset($_SESSION['jkmccfw_login_checked']) && wp_verify_nonce(sanitize_text_field($_SESSION['jkmccfw_login_checked']), 'jkmccfw_login_check')) {
                return $user;
            }

            if ($this->is_wp_login_page()) {
                $check = JKMCCFW_Utils::jkmccfw_recaptcha_check();
                if (!$check['success']) {
                    return new WP_Error('authentication_failed', __('Please complete the reCAPTCHA to verify that you are not a robot.', 'jkm-checkout-captcha-for-woo'));
                }
                $_SESSION['jkmccfw_login_checked'] = wp_create_nonce('jkmccfw_login_check');
            }
            return $user;
        }

        public function clear_login_session($user_login, $user) {
            if (isset($_SESSION['jkmccfw_login_checked'])) {
                unset($_SESSION['jkmccfw_login_checked']);
            }
        }

        // WP Registration reCAPTCHA
        private function conditionally_hook_register() {
            if (get_option('jkmccfw_register')) {
                add_action('register_form', array('JKMCCFW_Utils', 'jkmccfw_field_admin'));
                add_action('registration_errors', array($this, 'check_register_recaptcha'), 10, 3);
            }
        }

        public function check_register_recaptcha($errors, $sanitized_user_login, $user_email) {
            if ($this->skip_recaptcha_checks()) { return $errors; }

            $check = JKMCCFW_Utils::jkmccfw_recaptcha_check();
            if (!$check['success']) {
                $errors->add('jkmccfw_error', __('<strong>ERROR</strong>: Please complete the reCAPTCHA to verify that you are not a robot.', 'jkm-checkout-captcha-for-woo'));
            }
            return $errors;
        }

        // WP Reset Password reCAPTCHA
        private function conditionally_hook_reset() {
            if (get_option('jkmccfw_woo_reset') && !is_admin()) {
                add_action('lostpassword_form', array('JKMCCFW_Utils', 'jkmccfw_field_admin'));
                add_action('lostpassword_post', array($this, 'check_reset_recaptcha'), 10, 1);
            }
        }

        public function check_reset_recaptcha($validation_errors) {
            if ($this->is_wp_login_page()) {
                $check = JKMCCFW_Utils::jkmccfw_recaptcha_check();
                if (!$check['success']) {
                    $validation_errors->add('jkmccfw_error', __('Please complete the reCAPTCHA to verify that you are not a robot.', 'jkm-checkout-captcha-for-woo'));
                }
            }
            return $validation_errors;
        }

        // WooCommerce Checkout reCAPTCHA
        private function conditionally_hook_woocommerce() {
            if (is_woocommerce_active() && get_option('jkmccfw_key') && get_option('jkmccfw_woo_checkout')) {
                $this->setup_checkout_position_hooks();
                add_action('woocommerce_checkout_process', array($this, 'check_checkout_recaptcha'));
            }

            if (get_option('jkmccfw_woo_login')) {
                add_action('woocommerce_login_form', array('JKMCCFW_Utils', 'jkmccfw_field'));
                add_action('authenticate', array($this, 'check_woocommerce_login_recaptcha'), 21, 1);
            }

            if (get_option('jkmccfw_woo_register')) {
                add_action('woocommerce_register_form', array('JKMCCFW_Utils', 'jkmccfw_field'));
                add_action('woocommerce_register_post', array($this, 'check_woocommerce_register_recaptcha'), 10, 3);
            }

            if (get_option('jkmccfw_woo_reset')) {
                add_action('woocommerce_lostpassword_form', array('JKMCCFW_Utils', 'jkmccfw_field'));
                add_action('lostpassword_post', array($this, 'check_woocommerce_reset_recaptcha'), 10, 1);
            }
        }

        public function check_checkout_recaptcha() {
            // Skip if reCAPTCHA disabled for payment method
            $skip = false;

            if (isset($_POST['payment_method'])) {
                $chosen_payment_method = sanitize_text_field(wp_unslash($_POST['payment_method']));
                $selected_payment_methods = get_option('jkmccfw_selected_payment_methods', array());

                if (is_array($selected_payment_methods)) {
                    // Check if the chosen payment method is in the selected payment methods array
                    if (in_array($chosen_payment_method, $selected_payment_methods, true)) {
                        $skip = true;
                    }
                }
            }

            // Check if guest only is enabled
            $guest_only = esc_attr(get_option('jkmccfw_guest_only'));
            
            // Check reCAPTCHA if conditions are met
            if (!$skip && (!$guest_only || ($guest_only && !is_user_logged_in()))) {
                $check = JKMCCFW_Utils::jkmccfw_recaptcha_check(); // Assuming this method exists
                $success = $check['success'];
                if (!$success) {
                    wc_add_notice(__('Please complete the reCAPTCHA to verify that you are not a robot.', 'jkm-checkout-captcha-for-woo'), 'error');
                }
            }
        }

        public function check_woocommerce_login_recaptcha($user) {
            // Skip checks for XMLRPC and REST requests
            if (!isset($user->ID) || $this->skip_recaptcha_checks($user)) { return $user; }
            // Check for reCAPTCHA nonce
            if (isset($_POST['woocommerce-login-nonce'])) {
                // Verify the nonce
                if (!wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['woocommerce-login-nonce'])), 'woocommerce-login')) {
                    return new WP_Error('nonce_verification_failed', __('Nonce verification failed. Please try again.', 'jkm-checkout-captcha-for-woo'));
                }
                $check = JKMCCFW_Utils::jkmccfw_recaptcha_check();
                $success = $check['success'];
                if (!$success) {
                    return new WP_Error('authentication_failed', __('Please complete the reCAPTCHA to verify that you are not a robot.', 'jkm-checkout-captcha-for-woo'));
                }
            }
            
            return $user;
        }

        public function check_woocommerce_register_recaptcha($username, $email, $validation_errors) {
            // Check for reCAPTCHA on registration
            if (!is_checkout()) {
                $check = JKMCCFW_Utils::jkmccfw_recaptcha_check(); // Assuming this method exists
                $success = $check['success'];
                if (!$success) {
                    $validation_errors->add('jkmccfw_error', __('Please complete the reCAPTCHA to verify that you are not a robot.', 'jkm-checkout-captcha-for-woo'));
                }
            }
        }

        public function check_woocommerce_reset_recaptcha($validation_errors) {
            // Check for reCAPTCHA on password reset
            if (isset($_POST['woocommerce-lost-password-nonce'])) {

                $nonce = sanitize_text_field( wp_unslash($_POST['woocommerce-lost-password-nonce']));

                // Verify the nonce
                if (!wp_verify_nonce($nonce, 'woocommerce-lost-password')) {
                    return new WP_Error('nonce_verification_failed', esc_html__('Nonce verification failed. Please try again.', 'jkm-checkout-captcha-for-woo'));
                }
                $check = JKMCCFW_Utils::jkmccfw_recaptcha_check();
                $success = $check['success'];
                if (!$success) {
                    $validation_errors->add('jkmccfw_error', __('Please complete the reCAPTCHA to verify that you are not a robot.', 'jkm-checkout-captcha-for-woo'));
                }
            }
        }


        private function setup_checkout_position_hooks() {
            $checkout_position = get_option('jkmccfw_woo_checkout_pos');
            
            // Apply filter to allow changing the position
            $checkout_position = apply_filters('jkmccfw_checkout_captcha_position_hook', $checkout_position);
            $checkout_position_hp = apply_filters('jkmccfw_checkout_captcha_position_hook_priority', 9999);

            // Set up hooks based on the filtered position
            if (empty($checkout_position) || $checkout_position == "beforepay") {
                add_action('woocommerce_review_order_before_payment', array('JKMCCFW_Utils', 'jkmccfw_field_checkout'), 10);
            } elseif ($checkout_position == "afterpay") {
                add_action('woocommerce_review_order_after_payment', array('JKMCCFW_Utils', 'jkmccfw_field_checkout'), 10);
            } elseif ($checkout_position == "beforebilling") {
                add_action('woocommerce_before_checkout_billing_form', array('JKMCCFW_Utils', 'jkmccfw_field_checkout'), 10);
            } elseif ($checkout_position == "afterbilling") {
                add_action('woocommerce_after_checkout_billing_form', array('JKMCCFW_Utils', 'jkmccfw_field_checkout'), 10);
            } elseif ($checkout_position == "beforeform") {
                add_action('woocommerce_before_checkout_form', array('JKMCCFW_Utils', 'jkmccfw_field_checkout'), 10);
            } elseif ($checkout_position == "afterform") {
                add_action('woocommerce_after_checkout_form', array('JKMCCFW_Utils', 'jkmccfw_field_checkout'), 10);
            } else {
                // Check if the position is a valid action hook and add the callback
                if (has_action($checkout_position)) {
                    add_action($checkout_position, array('JKMCCFW_Utils', 'jkmccfw_field_checkout'), $checkout_position_hp);
                } else {
                    add_action('woocommerce_review_order_before_payment', array('JKMCCFW_Utils', 'jkmccfw_field_checkout'), 10);
                }
            }
        }

        private function skip_recaptcha_checks($user = null) {
            return (defined('XMLRPC_REQUEST') && XMLRPC_REQUEST) ||
                   (defined('REST_REQUEST') && REST_REQUEST) ||
                   (is_wp_error($user) && isset($user->errors['empty_username']) && isset($user->errors['empty_password']));
        }

        private function is_wp_login_page() {
            if (isset($_SERVER["REQUEST_URI"])) {
                $request_uri = sanitize_text_field( wp_unslash($_SERVER["REQUEST_URI"]) );
                return stripos($request_uri, strrchr(wp_login_url(), '/')) !== false;
            }
            return false;
        }

        public function add_recaptcha_field() {
            echo '<div class="recaptcha-field">[Recaptcha field here]</div>'; // Placeholder for actual reCAPTCHA field
        }
    }
endif;
