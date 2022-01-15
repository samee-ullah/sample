import template from './template.html.twig';
import './style.scss';

const { mapGetters } = Shopware.Component.getComponentHelper();
const { Criteria }   = Shopware.Data;

Shopware.Component.register('neti-easy-coupon-product-product-price-field', {
    template,

    inject: [
        'repositoryFactory'
    ],

    props: {
        value: {
            type: Array,
            required: true
        },
        taxId: {
            type: String
        },
        disabled: {
            type: Boolean,
            required: false,
            default: false
        }
    },

    data() {
        return {
            taxRates: [],
            displayMaintainCurrencies: false
        };
    },

    computed: {
        ...mapGetters('netiEasyCoupon', [
            'defaultCurrencyId',
            'currencies'
        ]),

        currency() {
            return this.currencies.find(currency => currency.id === this.defaultCurrencyId);
        },

        defaultPrice() {
            return this.value.find(price => price.currencyId === this.currency.id);
        },

        taxRepository() {
            return this.repositoryFactory.create('tax');
        },

        taxRate() {
            return this.taxRates.find(taxRate => taxRate.id === this.taxId) || {};
        }
    },

    mounted() {
        this.loadTaxes();
    },

    methods: {
        loadTaxes() {
            const criteria = new Criteria();

            this.taxRepository.search(criteria, Shopware.Context.api).then(models => {
                this.taxRates = models;
            });
        },

        onCloseCurrenciesModal(prices) {
            this.$emit('input', prices);

            this.displayMaintainCurrencies = false;
        }
    }

});