import template from './template.html.twig';

Shopware.Component.extend('neti-easy-coupon-product-entity-listing', 'sw-entity-listing', {
    template,

    inject: [
        'repositoryFactory'
    ],

    computed: {
        productRepository() {
            return this.repositoryFactory.create('product');
        }
    },

    methods: {
        deleteItem(item) {
            this.deleteId = null;

            // send delete request to the server, immediately
            return this.deleteCoupon(item).then(() => {
                this.resetSelection();
                return this.doSearch();
            }).catch((errorResponse) => {
                this.$emit('delete-item-failed', { id, errorResponse });
            });
        },

        deleteItems() {
            this.isBulkLoading = true;
            const promises     = [];

            Object.values(this.selection).forEach((selectedProxy) => {
                promises.push(this.deleteCoupon(selectedProxy));
            });

            return Promise.all(promises).then(() => {
                return this.deleteItemsFinish();
            }).catch(() => {
                return this.deleteItemsFinish();
            });
        },

        async deleteCoupon(item) {
            if (typeof item.productId === 'string') {
                // We don't need to delete the coupon here itself because
                // by deleting its product the coupon is deleted too

                return await this.productRepository.delete(item.productId, Shopware.Context.api);
            }

            return await this.repository.delete(item.id, this.items.context);
        }
    }

});