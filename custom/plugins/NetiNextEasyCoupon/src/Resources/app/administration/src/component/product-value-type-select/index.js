import template from './template.html.twig';

const { Component } = Shopware;

Component.register('neti-easy-coupon-product-value-type-select', {
    template,

    props: {
        value: {
            type: Number,
            required: false
        },
        disabled: {
            type: Boolean,
            required: false,
            default: false
        }
    },

    data() {
        return {
            currentValue: ''
        };
    },

    computed: {
        options() {
            return Shopware.State.getters['netiEasyCoupon/productValueTypes'];
        }
    },

    watch: {
        value: {
            immediate: true,
            handler() {
                this.currentValue = '' + this.value;

                if (
                    null === this.value
                    && this.options.length > 0
                ) {
                    this.onChange(this.options[0]);
                }
            }
        },
        options (value, previousValue) {
            if (
                value.length > 0
                && previousValue.length === 0
                && null === this.value
            ) {
                this.onChange(this.options[0]);
            }
        }
    },

    methods: {
        onChange(newValue) {
            newValue = parseInt(newValue, 10);

            this.$emit('input', newValue);
        }
    }

});
