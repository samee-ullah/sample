import template from './template.html.twig';
import './style.scss';

const { mapGetters } = Shopware.Component.getComponentHelper();

Shopware.Component.register('neti-easy-coupon-product-price-field', {
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
            type: Number
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

    watch: {
        'price.selectableValues'() {
            this.price.selectableValues.forEach((value, key) => {
                if (typeof value !== 'number') {
                    this.price.selectableValues[key] = parseFloat(value);
                }
            });
        }
    },

    mounted() {
        if (typeof this.price !== 'object' && false === this.main) {
            const price = Object.assign({}, this.priceField);

            price.currencyId = this.currencyId;
            this.prices.push(price);
        }
    },

    methods: {
        onValidateValue(value) {
            const normalizedValue = value.replace(/,/g, '.');

            return parseFloat(normalizedValue).toString() === normalizedValue
                && this.price.selectableValues.indexOf(value) === -1;
        },

        onCurrencyModalClose() {
            this.currencyModal = false;
        }
    }

});