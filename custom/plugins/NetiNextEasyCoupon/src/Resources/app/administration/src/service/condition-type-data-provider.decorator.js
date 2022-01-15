const { Application } = Shopware;

Application.addServiceProviderDecorator('easyCouponRuleConditionService', (ruleConditionService) => {
    ruleConditionService.addCondition('customerCustomerNumber', {
        component: 'sw-condition-customer-number',
        label: 'global.sw-condition.condition.customerNumberRule',
        scopes: ['checkout']
    });

    ruleConditionService.addCondition('customerCustomerGroup', {
        component: 'sw-condition-customer-group',
        label: 'global.sw-condition.condition.customerGroupRule',
        scopes: ['checkout']
    });

    ruleConditionService.addCondition('netiEasyCouponDateRange', {
        component: 'sw-condition-date-range',
        label: 'global.sw-condition.condition.dateRangeRule.label',
        scopes: ['global']
    });

    ruleConditionService.addCondition('salesChannel', {
        component: 'sw-condition-sales-channel',
        label: 'global.sw-condition.condition.salesChannelRule',
        scopes: ['global']
    });

    ruleConditionService.addCondition('cartLineItemInCategory', {
        component: 'sw-condition-line-item-in-category',
        label: 'global.sw-condition.condition.lineItemInCategoryRule',
        scopes: ['lineItem']
    });

    ruleConditionService.addCondition('cartLineItem', {
        component: 'sw-condition-line-item',
        label: 'global.sw-condition.condition.lineItemRule',
        scopes: ['lineItem']
    });

    ruleConditionService.addCondition('cartLineItemTotalPrice', {
        component: 'sw-condition-line-item-total-price',
        label: 'global.sw-condition.condition.lineItemTotalPriceRule',
        scopes: ['lineItem']
    });

    ruleConditionService.addCondition('netiEasyCouponCartLineItemOfManufacturer', {
        component: 'sw-condition-neti-easy-coupon-line-item-of-manufacturer',
        label: 'global.sw-condition.condition.lineItemOfManufacturerRule',
        scopes: ['lineItem']
    });

    ruleConditionService.addCondition('netiEasyCouponUsesPerCustomer', {
        component: 'sw-condition-neti-easy-coupon-uses-per-customer',
        label: 'global.sw-condition.condition.netiEasyCouponUsesPerCustomerRule',
        scopes: ['global']
    });

    ruleConditionService.addCondition('netiEasyCouponCustomer', {
        component: 'sw-condition-neti-easy-coupon-customer',
        label: 'global.sw-condition.condition.netiEasyCouponCustomerRule',
        scopes: ['global']
    });

    ruleConditionService.addCondition('netiEasyCouponTotalUses', {
        component: 'sw-condition-neti-easy-coupon-total-uses',
        label: 'global.sw-condition.condition.netiEasyCouponTotalUsesRule',
        scopes: ['global']
    });

    ruleConditionService.addCondition('cartCartAmount', {
        component: 'sw-condition-cart-amount',
        label: 'global.sw-condition.condition.cartAmountRule',
        scopes: ['cart']
    });

    ruleConditionService.addCondition('netiEasyCouponMailAddress', {
        component: 'sw-condition-neti-easy-coupon-mail-address',
        label: 'global.sw-condition.condition.netiEasyCouponMailAddressRule',
        scopes: ['global']
    });

    return ruleConditionService;
});
