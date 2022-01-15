const { Component, Utils } = Shopware;
const { get }              = Utils;

Component.override('sw-order-create-base', {
    computed: {
        promotionCodeLineItems() {
            return this.cartLineItems.filter(item => (
                item.type === 'promotion' || item.type === 'easy-coupon'
            ) && get(item, 'payload.code'));
        }
    }
});