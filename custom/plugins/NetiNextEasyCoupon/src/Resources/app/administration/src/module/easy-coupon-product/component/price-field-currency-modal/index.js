import template from './template.html.twig';
import './style.scss';

const { mapGetters } = Shopware.Component.getComponentHelper();

Shopware.Component.register('neti-easy-coupon-product-price-field-currency-modal', {
    template,

    inject: [
        'repositoryFactory'
    ],

    props: {
        prices: {
            type: Array,
            required: true
        },
        valueType: {
            type: Number,
            required: true
        }
    },

    computed: {
        ...mapGetters('netiEasyCoupon', [
            'currencies'
        ])
    },

    methods: {
        onClose() {
            this.$emit('modal-close');
        }
    }
});