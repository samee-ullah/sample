export default class CheckoutPageObject {
    constructor() {
        this.elements = {
            // General cart selectors
            cartItem: '.cart-item',
            cartItemFeatureList: '.product-feature-list-list',
            cartItemFeatureListItem: '.product-feature-list-item',
            cartItemFeatureContainer: '.product-feature-feature',
            cartItemFeatureLabel: '.product-feature-label',
            cartItemFeatureValue: '.product-feature-value',

            // Offcanvas cart
            offCanvasCart: '.offcanvas',
            offCanvasCartActions: '.offcanvas-cart-actions',
            cartActions: '.cart-actions',

            // payment method
            paymentMethodsContainer: '.payment-methods',
            paymentMethodsCollapseContainer: '.payment-methods > .collapse',
            paymentMethodsCollapseTrigger: '.payment-methods > .confirm-checkout-collapse-trigger',
            paymentFormConfirm: '#changePaymentForm',

            // shipping method
            shippingMethodsContainer: '.shipping-methods',
            shippingFormConfirm: '#changeShippingForm'
        };
    }
}
