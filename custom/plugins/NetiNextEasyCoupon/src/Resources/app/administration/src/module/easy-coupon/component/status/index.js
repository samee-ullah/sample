import template from './template.html.twig';
import './style.scss';

const { mapGetters } = Shopware.Component.getComponentHelper();
const { currency } = Shopware.Utils.format;

Shopware.Component.register('neti-easy-coupon-status', {
    template,

    computed: {
        ...mapGetters('netiEasyCoupon', {
            status: 'couponStatus'
        })
    },

    methods: {
        currency (value, format, decimalPlaces) {
            decimalPlaces = decimalPlaces || 2;

            if (value === null) {
                return '-';
            }

            return currency(value, format, decimalPlaces);
        }
    }
});