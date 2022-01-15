import template from './template.html.twig';

const { Component, Mixin }  = Shopware;
const { Criteria }          = Shopware.Data;
const { mapPropertyErrors } = Shopware.Component.getComponentHelper();

Component.register('neti-easy-coupon-create-transaction-modal', {
    template,

    inject: ['repositoryFactory'],

    mixins: [
        Mixin.getByName('notification')
    ],

    props: {
        /**
         * An neti_easy_coupon entity object
         */
        model: {
            type: Object,
            required: true
        },

        active: {
            type: Boolean,
            required: true
        }
    },

    data() {
        return {
            transaction: null,
            isCreating: false,
            isCreateSuccessful: false
        };
    },

    computed: {
        ...mapPropertyErrors(
            'transaction',
            [
                'value',
                'currencyId',
                'internComment'
            ]
        ),
        transactionRepository() {
            return this.repositoryFactory.create('neti_easy_coupon_transaction');
        },
        currencyRepository() {
            return this.repositoryFactory.create('currency');
        }
    },

    watch: {
        active(value) {
            if (true === value) {
                this.onReset();
            }
        },
        async 'transaction.currencyId'(currencyId) {
            if (typeof this.transaction.currencyId === 'string') {
                const currency = await this.getCurrency(this.transaction.currencyId);

                this.transaction.currencyFactor = currency.factor;
            }
        }
    },

    methods: {
        onReset() {
            this.isCreateSuccessful = false;
            this.isCreating         = false;

            this.transaction                 = this.transactionRepository.create();
            this.transaction.transactionType = 30020; // created by admin
            this.transaction.easyCouponId    = this.model.id;
            this.transaction.value           = 0;
            this.transaction.currencyFactor  = 1;
        },

        onClose() {
            this.onReset();
            this.$emit('close');
        },

        onCloseButtonClicked() {
            this.onClose();
        },

        async onCreateButtonClicked() {
            this.isCreateSuccessful = false;
            this.isCreating         = true;

            try {


                await this.transactionRepository.save(this.transaction, Shopware.Context.api);

                this.isCreating         = false;
                this.isCreateSuccessful = true;

                this.createNotificationSuccess({
                    title: this.$t('neti-easy-coupon.transaction-list.create-transaction.successTitle'),
                    message: this.$t('neti-easy-coupon.transaction-list.create-transaction.successMessage')
                });

                this.onClose();
                this.$emit('refresh');
            } catch (error) {
                this.onSaveError();
            }
        },

        onCreateFinish() {
            this.isCreateSuccessful = false;
        },

        onSaveError() {
            this.createNotificationError({
                title: this.$t('neti-easy-coupon.transaction-list.create-transaction.errorTitle'),
                message: this.$t('neti-easy-coupon.transaction-list.create-transaction.errorMessage')
            });

            this.isCreating = false;
        },

        async getCurrency(currencyId) {
            const criteria = new Criteria();
            criteria.addFilter(Criteria.equals('id', currencyId));

            const result = await this.currencyRepository.search(criteria, Shopware.Context.api);

            return result.first();
        }
    }
});