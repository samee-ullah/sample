import template from './template.html.twig';
import './style.scss';

const { Component } = Shopware;

Component.override('sw-order-detail-base', {
    template,

    computed: {
        hasEasyCouponVouchers() {
            return this.easyCouponVouchers.length > 0;
        },

        easyCouponVouchers() {
            if (!this.order) {
                return [];
            }

            return this.order.lineItems.filter(lineItem => {
                return lineItem.payload.discountScope === 'netiEasyCoupon'
                    || 'netiNextEasyCoupon' in lineItem.payload;
            });
        }
    }
});