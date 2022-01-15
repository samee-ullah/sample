import template from './template.html.twig';

const { Component, Mixin } = Shopware;
const { Criteria }         = Shopware.Data;

Component.register('neti-easy-coupon-product-list', {
    template,

    inject: [
        'repositoryFactory',
        'searchTypeService'
    ],

    mixins: [
        Mixin.getByName('listing'),
        Mixin.getByName('placeholder')
    ],

    data() {
        return {
            data: null,
            customers: 0,
            sortBy: 'createdAt',
            sortDirection: 'DESC',
            naturalSorting: true,
            isLoading: false,
            showDeleteModal: false
        };
    },

    metaInfo() {
        return {
            title: this.$createTitle()
        };
    },

    computed: {
        repository() {
            return this.repositoryFactory.create('neti_easy_coupon_product');
        },

        productRepository() {
            return this.repositoryFactory.create('product');
        },

        columns() {
            return this.getColumns();
        },

        defaultCriteria() {
            const criteria      = new Criteria(this.page, this.limit);
            this.naturalSorting = this.sortBy === 'createdAt';

            criteria.setTerm(this.term);
            criteria.addSorting(Criteria.sort(this.sortBy, this.sortDirection, this.naturalSorting));

            return criteria;
        }
    },

    methods: {
        getList() {
            this.isLoading = true;

            this.repository.search(this.defaultCriteria, Shopware.Context.api).then(result => {
                this.onUpdateRecords(result);

                this.isLoading = false;
            }).catch(() => {
                this.isLoading = false;
            });
        },

        onUpdateRecords (result) {
            this.total = result.total;
            this.data  = result;
        },

        onDelete(id) {
            this.showDeleteModal = id;
        },

        onCloseDeleteModal() {
            this.showDeleteModal = false;
        },

        onConfirmDelete(item) {
            this.showDeleteModal = false;

            if (typeof item.productId === 'string') {
                return Promise.all(
                    [
                        this.productRepository.delete(item.productId, Shopware.Context.api),
                        this.repository.delete(item.id, Shopware.Context.api)
                    ]
                ).then(this.getList);
            }

            return this.repository.delete(item.id, Shopware.Context.api).then(this.getList);
        },

        getColumns() {
            return [
                {
                    property: 'id',
                    dataIndex: 'id',
                    label: this.$t('neti-easy-coupon-product.list.column.id'),
                    allowResize: true,
                    visible: false
                },
                {
                    property: 'title',
                    dataIndex: 'title',
                    label: this.$t('neti-easy-coupon-product.list.column.title'),
                    primary: true
                },
                {
                    property: 'valueType',
                    dataIndex: 'valueType',
                    label: this.$t('neti-easy-coupon-product.list.column.valueType'),
                    primary: true
                },
                {
                    property: 'createdAt',
                    dataIndex: 'createdAt',
                    label: this.$t('neti-easy-coupon-product.list.column.createdAt')
                },
                {
                    property: 'updatedAt',
                    dataIndex: 'updatedAt',
                    label: this.$t('neti-easy-coupon-product.list.column.updatedAt')
                }
            ];
        }
    }
});