import template from './template.html.twig';

const { Component, Context } = Shopware;
const { mapPropertyErrors } = Component.getComponentHelper();
const { EntityCollection, Criteria } = Shopware.Data;

/**
 * @public
 * @description Condition for the NetiEasyCouponCustomerRule. This component must a be child of sw-condition-tree.
 * @status prototype
 */
Component.extend('sw-condition-neti-easy-coupon-customer', 'sw-condition-base', {
    template,

    inject: ['repositoryFactory'],

    data() {
        return {
            customers: null
        };
    },

    computed: {
        operators() {
            return this.conditionDataProviderService.getOperatorSet('multiStore');
        },

        customerRepository() {
            return this.repositoryFactory.create('customer');
        },

        customerIds: {
            get() {
                this.ensureValueExist();
                return this.condition.value.customerIds || [];
            },
            set(customerIds) {
                this.ensureValueExist();
                this.condition.value = { ...this.condition.value, customerIds };
            }
        },

        ...mapPropertyErrors('condition', ['value.operator', 'value.customerIds']),

        currentError() {
            return this.conditionValueOperatorError || this.conditionValueCustomerIdsError;
        }
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            this.customers = new EntityCollection(
                this.customerRepository.route,
                this.customerRepository.entityName,
                Context.api
            );

            if (this.customerIds.length <= 0) {
                return Promise.resolve();
            }

            const criteria = new Criteria();
            criteria.setIds(this.customerIds);

            return this.customerRepository.search(criteria, Context.api).then((customers) => {
                this.customers = customers;
            });
        },

        setCustomerIds(customers) {
            this.customerIds = customers.getIds();
            this.customers = customers;
        }
    }
});
