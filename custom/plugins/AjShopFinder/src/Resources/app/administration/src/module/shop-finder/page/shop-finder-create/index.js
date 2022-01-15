const {Component} = Shopware;

Component.extend('shop-finder-create', 'shop-finder-detail', {
    methods: {
        getShop() {
            this.shop = this.repository.create(Shopware.Context.api);
        },

        onClickSave() {
            this.isLoading = true;

            this.repository.save(this.shop, Shopware.Context.api).then(() => {
                this.isLoading = false;
                this.$router.push({name: "shop.finder.detail", params: {id: this.shop.id}});
            }).catch((exception) => {
                this.isLoading = false;
                this.createNotificationError({
                    title: this.$tc('shop-finder.detail.errorTitle'),
                    message: exception
                });
            });
        }
    }
})
