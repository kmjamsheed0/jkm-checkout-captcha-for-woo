const { registerBlockType } = wp.blocks;
const { __ } = wp.i18n;

registerBlockType('jkm-checkout-captcha/checkout-captcha', {
  edit: () => {
    return (
      <div className="jkm-checkout-captcha-block">
        <p>{__('Checkout Captcha will appear here on the frontend', 'jkm-checkout-captcha-for-woo')}</p>
      </div>
    );
  },

  save: () => {
    return null; // Dynamic block - rendered via PHP
  }
});