// File: assets/js/admin.js
jQuery(document).ready(function($) {
    // Handle tab navigation without page reload
    $('.nav-tab-wrapper .nav-tab').on('click', function(e) {
        e.preventDefault();
        
        // Update URL without page reload
        var tab = $(this).attr('href').split('tab=')[1];
        window.history.pushState({}, '', '?page=jkmccfw_settings&tab=' + tab);
        
        // Update active tab
        $('.nav-tab').removeClass('nav-tab-active');
        $(this).addClass('nav-tab-active');
        
        // Show/hide relevant content
        $('.tab-content').hide(); // Hide all tab contents
        $('#tab-' + tab).show();  // Show the current tab content
    });

    // Initialize by showing the correct tab based on the URL parameter
    var initialTab = new URLSearchParams(window.location.search).get('tab') || 'woocommerce';
    $('.nav-tab[href="?page=jkmccfw_settings&tab=' + initialTab + '"]').click();

    // Payment methods section toggle
    $('.payment-methods-wrapper').each(function() {
        var $wrapper = $(this);
        var $checkboxes = $wrapper.find('input[type="checkbox"]');
        
        // Add select all/none buttons
        var $controls = $('<div class="checkbox-controls"></div>').insertBefore($wrapper);
        $('<button type="button" class="select-all">Select All</button>').appendTo($controls);
        $('<button type="button" class="select-none">Select None</button>').appendTo($controls);
        
        // Handle select all
        $controls.on('click', '.select-all', function(e) {
            e.preventDefault();
            $checkboxes.prop('checked', true);
        });
        
        // Handle select none
        $controls.on('click', '.select-none', function(e) {
            e.preventDefault();
            $checkboxes.prop('checked', false);
        });
    });

    // // Handle reCAPTCHA test form submission
    // $('#test_recaptcha').on('click', function(e) {
    //     if (!grecaptcha.getResponse()) {
    //         e.preventDefault();
    //         alert(jkmccfw_admin.verify_human_message);
    //     }
    // });
});

