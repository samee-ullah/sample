import template from './sw-customer-detail.html.twig';

Shopware.Component.override('sw-customer-detail', {
    template,

    computed: {
        ecCustomerVoucherRoute() {
            return {
                name: 'neti.easy-coupon.customer.detail.vouchers',
                params: { id: this.customerId },
            };
        }
    }
});
