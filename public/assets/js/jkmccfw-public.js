/* Checkout */
jQuery(document).ready(function () {
    // Function to initialize reCAPTCHA
    function initializeCaptcha() {
        if (typeof grecaptcha !== "undefined") {
            jQuery(".g-recaptcha").each(function () {
                // Check if the CAPTCHA is already initialized
                if (!jQuery(this).hasClass("captcha-initialized")) {
                    grecaptcha.render(this, {
                        sitekey: jQuery(this).attr("data-sitekey"),
                        theme: jQuery(this).attr("data-theme") || "light",
                    });
                    jQuery(this).addClass("captcha-initialized"); // Mark as initialized
                }
            });
        }
    }

    // Add event listener for WooCommerce events
    jQuery(document.body).on(
        "update_checkout updated_checkout applied_coupon_in_checkout removed_coupon_in_checkout checkout_error",
        function () {
            if (jQuery(".g-recaptcha").length > 0) {
                if (typeof grecaptcha !== "undefined" && typeof grecaptcha.reset === "function") {
                    // Reset all initialized CAPTCHA widgets
                    var count = 0;
                    jQuery(".g-recaptcha").each(function () {
                        grecaptcha.reset(count);
                        count++;
                    });
                }
                // Reinitialize CAPTCHA widgets
                initializeCaptcha();
            }
        }
    );

    // Call initializeCaptcha on page load
    initializeCaptcha();
});
