<?php
/**
 * Checkout block integration for Checkout Captcha for WooCommerce.
 *
 * @package jkm-checkout-captcha-for-woo
 * @subpackage jkm-checkout-captcha-for-woo/public
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

use Automattic\WooCommerce\Blocks\Integrations\IntegrationInterface;

if ( ! class_exists( 'JKMCCFW_Checkout_Block_Integration' ) && interface_exists( IntegrationInterface::class ) ) :
    class JKMCCFW_Checkout_Block_Integration implements IntegrationInterface {

        const NAME = 'jkm-checkout-captcha-for-woo';

        /**
         * The integration name.
         *
         * @return string
         */
        public function get_name() {
            return self::NAME;
        }

        /**
         * Register block assets.
         */
        public function initialize() {
            $this->register_recaptcha_script();
            $this->register_store_api_schema();

            wp_register_script(
                'jkmccfw-checkout-block-frontend',
                JKMCCFW_PUBLIC_ASSETS_URL . 'js/jkmccfw-checkout-block.js',
                array( 'wp-data', 'wp-element', 'wp-i18n', 'wc-blocks-checkout', 'wc-blocks-data-store', 'wc-settings', 'recaptcha' ),
                JKMCCFW_VERSION,
                true
            );

            wp_register_script(
                'jkmccfw-checkout-block-editor',
                JKMCCFW_URL . 'blocks/checkout-captcha-block/index.js',
                array( 'wp-blocks', 'wp-block-editor', 'wp-data', 'wp-dom-ready', 'wp-element', 'wp-i18n', 'wc-blocks-checkout', 'wc-settings' ),
                JKMCCFW_VERSION,
                true
            );

            wp_register_style(
                'jkmccfw-checkout-block',
                JKMCCFW_PUBLIC_ASSETS_URL . 'css/jkmccfw-checkout-block.css',
                array(),
                JKMCCFW_VERSION
            );

            register_block_type(
                JKMCCFW_PATH . 'blocks/checkout-captcha-block',
                array(
                    'render_callback' => array( $this, 'render_checkout_captcha_block' ),
                    'style'           => 'jkmccfw-checkout-block',
                    'editor_style'    => 'jkmccfw-checkout-block',
                )
            );
        }

        /**
         * Frontend script handles.
         *
         * @return string[]
         */
        public function get_script_handles() {
            return array( 'jkmccfw-checkout-block-frontend' );
        }

        /**
         * Editor script handles.
         *
         * @return string[]
         */
        public function get_editor_script_handles() {
            return array( 'jkmccfw-checkout-block-editor' );
        }

        /**
         * Data exposed to checkout block scripts.
         *
         * @return array
         */
        public function get_script_data() {
            return array(
                'enabled'        => $this->is_enabled_for_customer(),
                'siteKey'        => esc_attr( get_option( 'jkmccfw_key' ) ),
                'theme'          => esc_attr( get_option( 'jkmccfw_theme', 'light' ) ),
                'namespace'      => self::NAME,
                'responseKey'    => 'g_recaptcha_response',
                'checkoutBlock'  => 'jkm-checkout-captcha/checkout-captcha',
                'checkoutParent' => 'woocommerce/checkout-fields-block',
                'editorLabel'    => __( 'Checkout reCAPTCHA', 'jkm-checkout-captcha-for-woo' ),
            );
        }

        /**
         * Render the block placeholder.
         *
         * @return string
         */
        public function render_checkout_captcha_block() {
            if ( ! $this->is_enabled_for_customer() ) {
                return '';
            }

            return '<div class="wp-block-jkm-checkout-captcha-checkout-captcha jkmccfw-checkout-captcha-block"><div class="jkmccfw-block-recaptcha" data-jkmccfw-captcha="1"></div></div>';
        }

        /**
         * Register Google reCAPTCHA when it has not already been registered.
         */
        private function register_recaptcha_script() {
            if ( wp_script_is( 'recaptcha', 'registered' ) ) {
                return;
            }

            wp_register_script(
                'recaptcha',
                add_query_arg(
                    array(
                        'render' => 'explicit',
                        'hl'     => get_locale(),
                    ),
                    'https://www.google.com/recaptcha/api.js'
                ),
                array(),
                JKMCCFW_VERSION,
                true
            );
        }

        /**
         * Register the Checkout endpoint extension data this block submits.
         */
        private function register_store_api_schema() {
            if (
                ! function_exists( 'woocommerce_store_api_register_endpoint_data' ) ||
                ! class_exists( '\Automattic\WooCommerce\StoreApi\Schemas\V1\CheckoutSchema' )
            ) {
                return;
            }

            woocommerce_store_api_register_endpoint_data(
                array(
                    'endpoint'        => \Automattic\WooCommerce\StoreApi\Schemas\V1\CheckoutSchema::IDENTIFIER,
                    'namespace'       => self::NAME,
                    'schema_callback' => function() {
                        return array(
                            'g_recaptcha_response' => array(
                                'description' => __( 'Google reCAPTCHA response token.', 'jkm-checkout-captcha-for-woo' ),
                                'type'        => 'string',
                                'context'     => array( 'view', 'edit' ),
                                'readonly'    => false,
                            ),
                        );
                    },
                    'data_callback'   => function() {
                        return array(
                            'g_recaptcha_response' => '',
                        );
                    },
                    'schema_type'     => ARRAY_A,
                )
            );
        }

        /**
         * Whether the current customer should see the checkout block CAPTCHA.
         *
         * @return bool
         */
        private function is_enabled_for_customer() {
            $guest_only = get_option( 'jkmccfw_guest_only' );

            return ! $guest_only || ! is_user_logged_in();
        }
    }
endif;
