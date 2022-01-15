import template from './shop-finder-detail.html.twig'

const {Component, Mixin} = Shopware;

Component.register('shop-finder-detail', {
    template,

    inject: ['repositoryFactory'],

    mixins: [
        Mixin.getByName('notification')
    ],

    metaInfo() {
        return {
            title: this.$createTitle()
        };
    },

    data() {
        return {
            shop: null,
            isLoading: false,
            processSuccess: false,
            repository: null
        }
    },

    computed: {
        options() {
            return [];
        }
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            this.repository = this.repositoryFactory.create('aj_shop_finder');
            this.getShop();
        },

        getShop() {
            this.repository.get(this.$route.params.id).then((entity) => {
                this.shop = entity;
            });
        },

        onClickSave() {
            this.isLoading = true;

            this.repository.save(this.shop, Shopware.Context.api).then(() => {
                this.getShop();
                this.isLoading = false;
                this.processSuccess = true;
            }).catch((exception) => {
                this.isLoading = false;
                this.createNotificationError({
                    title: this.$tc('shop-finder.detail.errorTitle'),
                    message: exception
                });
            });
        },

        saveFinish() {
            this.processSuccess = false;
        }
    }
});
