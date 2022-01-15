import template from './template.html.twig';

Shopware.Component.extend('neti-easy-coupon-entity-listing', 'sw-entity-listing', {
    template,

    methods: {

        deleteItem(item) {
            this.deleteId = null;

            // send delete request to the server, immediately
            return this.safeDeleteItem(item).then(() => {
                this.resetSelection();
                return this.doSearch();
            }).catch((errorResponse) => {
                this.$emit('delete-item-failed', { id: item.id, errorResponse });
            });
        },

        deleteItems() {
            this.isBulkLoading = true;
            const promises     = [];

            Object.values(this.selection).forEach((selectedProxy) => {
                promises.push(this.safeDeleteItem(selectedProxy));
            });

            return Promise.all(promises).then(() => {
                return this.deleteItemsFinish();
            }).catch(() => {
                return this.deleteItemsFinish();
            });
        },

        safeDeleteItem(item) {
            item.deleted     = true;
            item.deletedDate = new Date();

            return this.repository.save(item, this.items.context);
        }

    }

});