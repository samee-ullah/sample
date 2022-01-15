import template from './template.html.twig';

const { mapGetters } = Shopware.Component.getComponentHelper();

Shopware.Component.register('neti-easy-coupon-value-field', {
    template,

    props: {
        value: {
            required: true,
            type: Number
        },

        error: {
            type: Object,
            required: false
        },

        disabled: {
            type: Boolean,
            required: false
        },

        currencyFactor: {
            required: true,
            type: Number
        },

        valueType: {
            required: false,
            type: Number
        }
    },

    data() {
        return {
            currentValue: 0
        };
    },

    computed: {
        ...mapGetters('netiEasyCoupon', [
            'defaultCurrency'
        ]),

        isDisabled() {
            return this.disabled;
        }
    },

    watch: {
        currencyFactor() {
            this.onChange();
        },
        currentValue() {
            this.onChange();
        },
        valueType() {
            this.onChange();
        },
        defaultCurrency(currency, previousCurrency) {
            if (!previousCurrency) {
                this.setValue();
            }
        }
    },

    mounted() {
        this.setValue();
    },

    methods: {
        setValue() {
            if (this.valueType === 10020) {
                this.currentValue = this.value;

                return;
            }

            if (this.currencyFactor <= 0 || !this.defaultCurrency) {
                return;
            }

            this.currentValue = this.value * this.currencyFactor / this.defaultCurrency.factor;
        },
        onChange() {
            if (this.valueType === 10020) {
                this.$emit('input', this.currentValue);

                return;
            }

            if (this.currencyFactor <= 0) {
                return;
            }

            this.$emit('input', this.currentValue / this.currencyFactor * this.defaultCurrency.factor);
        }
    }
});