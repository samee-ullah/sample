import template from './template.html.twig';

Shopware.Component.register('neti-easy-coupon-cart-processor-priority-config', {
    template,

    props: {
        coupon: {
            type: Object,
            required: true
        }
    },

    computed: {
        viewModeStore() {
            return [
                {
                    label: this.$t('neti-easy-coupon.viewMode.cartProcessor.default'),
                    value: 0
                },
                {
                    label: this.$t('neti-easy-coupon.viewMode.cartProcessor.before'),
                    value: 1
                },
                {
                    label: this.$t('neti-easy-coupon.viewMode.cartProcessor.after'),
                    value: 2
                }
            ]
        }
    }
})