( function () {
    var settings = window.wc && window.wc.wcSettings
        ? window.wc.wcSettings.getSetting( 'jkm-checkout-captcha-for-woo_data', {} )
        : {};
    var blockName = settings.checkoutBlock || 'jkm-checkout-captcha/checkout-captcha';
    var parentBlock = settings.checkoutParent || 'woocommerce/checkout-fields-block';
    var label = settings.editorLabel || 'Checkout reCAPTCHA';
    var blockRegistered = false;
    var filtersRegistered = false;
    var orderingLocked = false;
    var orderingUnsubscribe = null;

    function registerBlock() {
        if ( ! window.wp || ! window.wp.blocks || ! window.wp.element || ! window.wp.i18n ) {
            return;
        }

        if ( blockRegistered || window.wp.blocks.getBlockType( blockName ) ) {
            blockRegistered = true;
            return;
        }

        window.wp.blocks.registerBlockType( blockName, {
            title: label,
            category: 'woocommerce',
            parent: [ parentBlock ],
            supports: {
                html: false,
                multiple: false,
                reusable: false
            },
            attributes: {
                lock: {
                    type: 'object',
                    default: {
                        remove: true,
                        move: true
                    }
                }
            },
            edit: function () {
                return window.wp.element.createElement(
                    'div',
                    { className: 'jkmccfw-checkout-captcha-block' },
                    window.wp.element.createElement(
                        'p',
                        {},
                        window.wp.i18n.__( 'Checkout reCAPTCHA will appear here.', 'jkm-checkout-captcha-for-woo' )
                    )
                );
            },
            save: function () {
                return null;
            }
        } );
        blockRegistered = true;
    }

    function allowBlockInCheckoutFields() {
        if ( ! window.wc || ! window.wc.blocksCheckout || ! window.wc.blocksCheckout.registerCheckoutFilters ) {
            return;
        }

        if ( filtersRegistered ) {
            return;
        }

        window.wc.blocksCheckout.registerCheckoutFilters( 'jkm-checkout-captcha-for-woo', {
            additionalCartCheckoutInnerBlockTypes: function ( defaultValue, extensions, args ) {
                if ( args && args.block === parentBlock ) {
                    defaultValue.push( blockName );
                }

                return defaultValue;
            }
        } );
        filtersRegistered = true;
    }

    function keepCaptchaAbovePlaceOrderButton() {
        var blockEditorSelect;
        var blockEditorDispatch;
        var captchaClientIds;
        var actionsClientIds;
        var matchedCaptchaClientId;
        var matchedActionsClientId;
        var matchedRootClientId;
        var siblingBlocks;
        var captchaIndex;
        var actionsIndex;
        var reorderedBlocks;

        if ( orderingLocked || ! window.wp || ! window.wp.data ) {
            return;
        }

        blockEditorSelect = window.wp.data.select( 'core/block-editor' );
        blockEditorDispatch = window.wp.data.dispatch( 'core/block-editor' );

        if (
            ! blockEditorSelect ||
            ! blockEditorDispatch ||
            ! blockEditorSelect.getBlocksByName ||
            ! blockEditorSelect.getBlockRootClientId ||
            ! blockEditorSelect.getBlocks ||
            ! blockEditorDispatch.replaceInnerBlocks
        ) {
            return;
        }

        captchaClientIds = blockEditorSelect.getBlocksByName( blockName ) || [];
        actionsClientIds = blockEditorSelect.getBlocksByName( 'woocommerce/checkout-actions-block' ) || [];

        captchaClientIds.some( function ( captchaClientId ) {
            var captchaRootClientId = blockEditorSelect.getBlockRootClientId( captchaClientId ) || '';

            return actionsClientIds.some( function ( actionsClientId ) {
                var actionsRootClientId = blockEditorSelect.getBlockRootClientId( actionsClientId ) || '';

                if ( captchaRootClientId !== actionsRootClientId ) {
                    return false;
                }

                matchedCaptchaClientId = captchaClientId;
                matchedActionsClientId = actionsClientId;
                matchedRootClientId = actionsRootClientId;
                return true;
            } );
        } );

        if ( ! matchedCaptchaClientId || ! matchedActionsClientId ) {
            return;
        }

        siblingBlocks = blockEditorSelect.getBlocks( matchedRootClientId ) || [];
        captchaIndex = siblingBlocks.findIndex( function ( block ) {
            return block.clientId === matchedCaptchaClientId;
        } );
        actionsIndex = siblingBlocks.findIndex( function ( block ) {
            return block.clientId === matchedActionsClientId;
        } );

        if ( captchaIndex < 0 || actionsIndex < 0 || captchaIndex < actionsIndex ) {
            return;
        }

        reorderedBlocks = siblingBlocks.filter( function ( block ) {
            return block.clientId !== matchedCaptchaClientId;
        } );
        reorderedBlocks.splice( actionsIndex, 0, siblingBlocks[ captchaIndex ] );

        orderingLocked = true;
        if ( blockEditorDispatch.__unstableMarkNextChangeAsNotPersistent ) {
            blockEditorDispatch.__unstableMarkNextChangeAsNotPersistent();
        }
        blockEditorDispatch.replaceInnerBlocks( matchedRootClientId, reorderedBlocks, false );
        window.setTimeout( function () {
            orderingLocked = false;
        }, 0 );
    }

    function watchCheckoutCaptchaEditorPosition() {
        if ( ! window.wp || ! window.wp.data || orderingUnsubscribe ) {
            return;
        }

        keepCaptchaAbovePlaceOrderButton();
        orderingUnsubscribe = window.wp.data.subscribe( keepCaptchaAbovePlaceOrderButton );
    }

    registerBlock();
    allowBlockInCheckoutFields();
    watchCheckoutCaptchaEditorPosition();

    if ( window.wp && window.wp.domReady ) {
        window.wp.domReady( function () {
            registerBlock();
            allowBlockInCheckoutFields();
            watchCheckoutCaptchaEditorPosition();
        } );
    }
}() );
