import template from './template.html.twig';
import './style.scss';

const { mapGetters } = Shopware.Component.getComponentHelper();

Shopware.Component.register('neti-easy-coupon-price-field', {
    template,

    inject: [
        'repositoryFactory'
    ],

    props: {
        prices: {
            type: Array,
            required: true
        },
        currencyId: {
            type: String,
            required: false
        },
        main: {
            type: Boolean,
            required: false,
            default() {
                return false;
            }
        },
        label: {
            type: String,
            required: false
        },
        helpText: {
            type: String,
            required: false
        }
    },

    data() {
        return {
            currencyModal: false
        };
    },

    computed: {
        ...mapGetters('netiEasyCoupon', [
            'defaultCurrencyId',
            'priceField'
        ]),
        price() {
            return this.prices.find(price => price.currencyId === (
                this.currencyId || this.defaultCurrencyId
            ));
        }
    },

    mounted() {
        if (typeof this.price !== 'object' && false === this.main) {
            const price = Object.assign({}, {
                currencyId: this.defaultCurrencyId,
                net: 0,
                linked: true,
                gross: 0
            });

            price.currencyId = this.currencyId;
            this.prices.push(price);
        }
    },

    methods: {
        onCurrencyModalClose() {
            this.currencyModal = false;
        }
    }

});