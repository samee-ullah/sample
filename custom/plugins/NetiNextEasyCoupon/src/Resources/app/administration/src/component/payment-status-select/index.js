import template from './template.html.twig';

const { Criteria } = Shopware.Data;

Shopware.Component.register('neti-easy-coupon-payment-status-select', {
    template,

    inject: ['repositoryFactory'],

    props: {
        value: {
            required: true,
            type: Array
        },

        label: {
            type: String
        },

        helpText: {
            type: String
        }
    },

    computed: {
        repository() {
            return this.repositoryFactory.create('state_machine_state');
        },
        criteria() {
            const criteria = new Criteria();

            criteria.addFilter(Criteria.equals('stateMachine.technicalName', 'order_transaction.state'));

            return criteria;
        }
    },

    data() {
        return {
            currentValue: []
        };
    },

    watch: {
        value: {
            immediate: true,
            handler() {
                if (this.currentValue.length === 0) {
                    this.currentValue = this.value;
                }
            }
        }
    },

    methods: {
        onChange(value) {
            this.currentValue = value;
            this.$emit('input', value);
        }
    }

});