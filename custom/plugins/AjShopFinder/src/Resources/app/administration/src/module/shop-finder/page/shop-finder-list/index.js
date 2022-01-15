import template from './shop-finder-list.html.twig';
import './shop-finder-list.scss';

const {Component} = Shopware;
const {Criteria} = Shopware.Data;

Component.register('shop-finder-list', {
    template,

    inject: ['repositoryFactory'],

    data() {
        return {
            repository: null,
            shops: null,
            shopEntityProductsModal: false
        };
    },

    metaInfo() {
        return {
            title: this.$createTitle()
        }
    },

    computed: {
        columns() {
            return this.getColumns();
        },

        showShopProductsModal() {
            return !!this.shopEntityProductsModal;
        },
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            this.repository = this.repositoryFactory.create('aj_shop_finder');
            this.getShops();
        },

        getShops() {
            const criteria = new Criteria();
            criteria.setPage(1);
            criteria.setLimit(10);
            criteria.addAssociation('country');

            this.repository.search(criteria, Shopware.Context.api).then((result) => {
                this.shops = result;
            });
        },

        openMapModal(item) {
            this.shopEntityProductsModal = item;
            console.log(this.shopEntityProductsModal.name)
        },

        closeShopProductsModal(url) {
            this.shopEntityProductsModal = false;
        },

        getColumns() {
            return [{
                property: 'name',
                label: this.$tc('shop-finder.list.columnName'),
                routerLink: 'shop.finder.detail',
                inlineEdit: 'string',
                allowResize: true,
                primary: true
            }, {
                property: 'active',
                label: this.$tc('sw-product.list.columnActive'),
                inlineEdit: 'boolean',
                allowResize: true,
                align: 'center',
            }, {
                property: 'city',
                label: this.$tc('shop-finder.list.columnCity'),
                inlineEdit: 'string',
                allowResize: true
            }, {
                property: 'telephone',
                label: this.$tc('shop-finder.list.columnTelephone'),
                inlineEdit: 'string',
                allowResize: true
            }, {
                property: 'country.name',
                label: this.$tc('shop-finder.list.columnCountry'),
                allowResize: true
            }];
        }
    }
})
