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

if (!class_exists('JKMCCFW_Admin')) :
    class JKMCCFW_Admin {
        private $screen_id;
        private $settings;

        public function __construct() {
            $this->settings = new JKMCCFW_Admin_Settings();
            add_action('admin_init', array($this, 'register_jkmccfw_settings'));
            add_action('update_option_jkmccfw_key', array($this, 'jkmccfw_keys_updated'), 10);
            add_action('update_option_jkmccfw_secret', array($this, 'jkmccfw_keys_updated'), 10);
        }

        public function jkmccfw_admin_script_enqueue($hook) {
            if ($hook !== $this->screen_id) {
                return;
            }
            $debug_mode = apply_filters('jkmfs_debug_mode', false);
            $suffix = $debug_mode ? '' : '.min';

            wp_enqueue_style(
                'jkmccfw-admin-style',
                JKMCCFW_ADMIN_ASSETS_URL . 'css/jkmccfw-admin'. $suffix .'.css',
                array(),
                JKMCCFW_VERSION
            );

            wp_enqueue_script(
                'jkmccfw-admin-script',
                JKMCCFW_ADMIN_ASSETS_URL . 'js/jkmccfw-admin'. $suffix .'.js',
                array('jquery'),
                JKMCCFW_VERSION,
                true
            );

            wp_register_script(
                "recaptcha", 
                "https://www.google.com/recaptcha/api.js?explicit&hl=" . get_locale()
            );
            wp_enqueue_script("recaptcha");
        }

        public function jkmccfw_admin_menu() {
            $capability = JKMCCFW_Utils::jkmccfw_capability();
            $page_title = esc_html__('Checkout Captcha for WooCommerce', 'jkm-checkout-captcha-for-woo');
            $menu_title = esc_html__('Checkout Captcha', 'jkm-checkout-captcha-for-woo');
            
            $this->screen_id = add_submenu_page(
                'woocommerce',
                $page_title,
                $menu_title,
                $capability,
                'jkmccfw_settings',
                array($this, 'render_settings_page'),
                99
            );
        }

        public function render_settings_page() {
            if (!current_user_can('manage_options')) {
                wp_die(__('You do not have sufficient permissions to access this page.', 'jkm-checkout-captcha-for-woo'));
            }
            ?>
            <div class="wrap">
                <h1><?php esc_html_e('Checkout Captcha for WooCommerce', 'jkm-checkout-captcha-for-woo'); ?></h1>

                <?php
                    if (empty(get_option('jkmccfw_tested')) || get_option('jkmccfw_tested') != 'yes') {
                        echo JKMCCFW_Utils::jkmccfw_admin_test();
                    } else {
                        echo '<p style="font-weight: bold; color: green; margin-top: 28px;">
                                <span class="dashicons dashicons-yes-alt"></span> ' . __('Success! reCAPTCHA seems to be working correctly with your API keys.', 'jkm-checkout-captcha-for-woo') . '</p>';
                    }
                    $this->settings->render_tabs(); 
                 ?>
                <form method="post" action="options.php">
                    <?php
                    settings_fields('jkmccfw-settings-group');
                    do_settings_sections('jkmccfw-settings-group');
                    
                    $this->settings->render_woocommerce_settings();
                    $this->settings->render_wordpress_settings();
                    $this->settings->render_configure_settings();
                    submit_button();
                    ?>
                </form>
            </div>
            <?php
        }

        public function register_jkmccfw_settings() {
            $settings = [
                'jkmccfw_key',
                'jkmccfw_secret',
                'jkmccfw_theme',
                'jkmccfw_login',
                'jkmccfw_register',
                'jkmccfw_reset',
                'jkmccfw_woo_checkout',
                'jkmccfw_guest_only',
                'jkmccfw_woo_login',
                'jkmccfw_woo_register',
                'jkmccfw_woo_reset',
                'jkmccfw_selected_payment_methods',
                'jkmccfw_woo_checkout_pos'
            ];
            
            foreach ($settings as $setting) {
                register_setting('jkmccfw-settings-group', $setting);
            }
        }

        public function jkmccfw_keys_updated() {
            update_option('jkmccfw_tested', 'no');
        }

        public function plugin_action_links($links) {
            $settings_url = admin_url('admin.php?page=jkmccfw_settings&tab=settings');
            $settings_link = sprintf(
                '<a href="%s">%s</a>',
                esc_url($settings_url),
                esc_html__('Settings', 'jkm-checkout-captcha-for-woo')
            );
            array_unshift($links, $settings_link);
            return $links;
        }

        public function jkmccfw_activation_redirect() {
            if (!wp_doing_ajax() && get_option('jkmccfw_activation_redirect', false) == wp_get_current_user()->ID) {
                delete_option('jkmccfw_activation_redirect');
                wp_safe_redirect(admin_url('admin.php?page=jkmccfw_settings&tab=settings'));
                exit;
            }
        }
    }
endif;