import template from './template.html.twig';
import './style.scss';

const { Component }         = Shopware;
const { mapPropertyErrors } = Component.getComponentHelper();

Component.extend('sw-condition-neti-easy-coupon-mail-address', 'sw-condition-base', {
    template,

    inject: ['repositoryFactory'],

    data() {
        return {};
    },

    computed: {
        operators() {
            return this.conditionDataProviderService.getOperatorSet('multiStore');
        },

        emails: {
            get() {
                this.ensureValueExist();
                return this.condition.value.emails || [];
            },
            set(emails) {
                this.ensureValueExist();
                this.condition.value = { ...this.condition.value, emails };
            }
        },

        ...mapPropertyErrors('condition', ['value.operator', 'value.emails']),

        currentError() {
            return this.conditionValueOperatorError || this.conditionValueEmailsError;
        }
    }
});
