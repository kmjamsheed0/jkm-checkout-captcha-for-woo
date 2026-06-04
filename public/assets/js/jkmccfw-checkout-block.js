( function () {
    var settings = window.wc && window.wc.wcSettings
        ? window.wc.wcSettings.getSetting( 'jkm-checkout-captcha-for-woo_data', {} )
        : {};
    var namespace = settings.namespace || 'jkm-checkout-captcha-for-woo';
    var responseKey = settings.responseKey || 'g_recaptcha_response';
    var blockName = settings.checkoutBlock || 'jkm-checkout-captcha/checkout-captcha';
    var widgetIds = [];
    var renderAttempts = 0;

    function updateExtensionData( token ) {
        var data = {};
        data[ responseKey ] = token || '';

        if (
            window.wp &&
            window.wp.data &&
            window.wc &&
            window.wc.wcBlocksData &&
            window.wp.data.dispatch
        ) {
            window.wp.data
                .dispatch( window.wc.wcBlocksData.CHECKOUT_STORE_KEY )
                .setExtensionData( namespace, data, true );
        }
    }

    function getCaptchaContainers() {
        var containers = Array.prototype.slice.call(
            document.querySelectorAll( '.jkmccfw-block-recaptcha[data-jkmccfw-captcha="1"]' )
        );
        var checkoutActions = document.querySelector( '.wp-block-woocommerce-checkout-actions-block' );

        if ( containers.length && checkoutActions ) {
            containers.forEach( function ( container ) {
                var wrapper = container.closest( '.jkmccfw-checkout-captcha-block' ) || container;

                if ( wrapper.parentNode !== checkoutActions || wrapper !== checkoutActions.firstElementChild ) {
                    checkoutActions.insertBefore( wrapper, checkoutActions.firstChild );
                }
            } );

            return containers;
        }

        if ( ! checkoutActions ) {
            return containers;
        }

        var fallbackWrapper = document.createElement( 'div' );
        fallbackWrapper.className = 'jkmccfw-checkout-captcha-block';

        var fallbackCaptcha = document.createElement( 'div' );
        fallbackCaptcha.className = 'jkmccfw-block-recaptcha';
        fallbackCaptcha.setAttribute( 'data-jkmccfw-captcha', '1' );

        fallbackWrapper.appendChild( fallbackCaptcha );
        checkoutActions.insertBefore( fallbackWrapper, checkoutActions.firstChild );

        return [ fallbackCaptcha ];
    }

    function renderCaptcha() {
        if ( ! settings.enabled || ! settings.siteKey ) {
            updateExtensionData( '' );
            return;
        }

        if ( typeof window.grecaptcha === 'undefined' || typeof window.grecaptcha.render !== 'function' ) {
            window.setTimeout( renderCaptcha, 250 );
            return;
        }

        var containers = getCaptchaContainers();

        if ( ! containers.length ) {
            renderAttempts++;
            if ( renderAttempts < 40 ) {
                window.setTimeout( renderCaptcha, 250 );
            }
            return;
        }

        renderAttempts = 0;

        containers.forEach( function ( container ) {
            if ( container.getAttribute( 'data-jkmccfw-widget-id' ) ) {
                return;
            }

            var widgetId = window.grecaptcha.render( container, {
                sitekey: settings.siteKey,
                theme: settings.theme || 'light',
                callback: function ( token ) {
                    updateExtensionData( token );
                },
                'expired-callback': function () {
                    updateExtensionData( '' );
                },
                'error-callback': function () {
                    updateExtensionData( '' );
                }
            } );

            container.setAttribute( 'data-jkmccfw-widget-id', String( widgetId ) );
            widgetIds.push( widgetId );
        } );
    }

    function resetCaptcha() {
        updateExtensionData( '' );

        if ( typeof window.grecaptcha === 'undefined' || typeof window.grecaptcha.reset !== 'function' ) {
            return;
        }

        widgetIds.forEach( function ( widgetId ) {
            window.grecaptcha.reset( widgetId );
        } );
    }

    function CaptchaBlock( props ) {
        var element = window.wp.element;

        element.useEffect( function () {
            renderCaptcha();
        }, [] );

        return element.createElement(
            'div',
            { className: [ props.className, 'jkmccfw-checkout-captcha-block' ].filter( Boolean ).join( ' ' ) },
            element.createElement( 'div', {
                className: 'jkmccfw-block-recaptcha',
                'data-jkmccfw-captcha': '1'
            } )
        );
    }

    function registerCheckoutBlock() {
        if ( window.wc && window.wc.blocksCheckout && window.wc.blocksCheckout.registerCheckoutBlock ) {
            window.wc.blocksCheckout.registerCheckoutBlock( {
                metadata: {
                    name: blockName,
                    parent: [ settings.checkoutParent || 'woocommerce/checkout-fields-block' ],
                    attributes: {
                        lock: {
                            type: 'object',
                            default: {
                                remove: true,
                                move: true
                            }
                        }
                    }
                },
                component: CaptchaBlock
            } );
        }
    }

    registerCheckoutBlock();

    if ( document.readyState === 'loading' ) {
        document.addEventListener( 'DOMContentLoaded', renderCaptcha );
    } else {
        renderCaptcha();
    }

    if ( window.jQuery ) {
        window.jQuery( document.body ).on( 'checkout_error', resetCaptcha );
    }

    if ( document.body ) {
        document.body.addEventListener( 'wc-blocks_checkout_error', resetCaptcha );
    }
    window.jkmccfwRenderCheckoutBlockCaptcha = renderCaptcha;
}() );
