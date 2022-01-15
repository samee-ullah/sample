import template from './template.html.twig';
import './style.scss';

const { Component }         = Shopware;
const { mapPropertyErrors } = Component.getComponentHelper();

/**
 * @public
 * @description Condition for the NetiEasyCouponTotalUses. This component must a be child of sw-condition-tree.
 * @status prototype
 * @example-type code-only
 * @component-example
 */
Component.extend('sw-condition-neti-easy-coupon-total-uses', 'sw-condition-base', {
    template,

    computed: {
        value: {
            get() {
                this.ensureValueExist();

                return this.condition.value.value || 1;
            },
            set(value) {
                this.ensureValueExist();

                this.condition.value = { ...this.condition.value, value };
            }
        },

        ...mapPropertyErrors('condition', ['value.value']),

        currentError() {
            return this.conditionValueValueError;
        }
    }
});
