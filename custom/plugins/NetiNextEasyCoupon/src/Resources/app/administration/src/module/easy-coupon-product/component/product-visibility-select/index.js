Shopware.Component.extend('neti-easy-coupon-product-visibility-select', 'sw-product-visibility-select', {

    props: {
        mainProduct: {
            required: true,
            type: Object
        }
    },

    computed: {
        product() {
            return this.mainProduct;
        }
    }

});