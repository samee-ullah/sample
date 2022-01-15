import template from './template.html.twig';

const { Component, Mixin } = Shopware;
const { Criteria }         = Shopware.Data;
const { mapGetters }       = Shopware.Component.getComponentHelper();

Component.register('neti-easy-coupon-list', {
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
            return this.repositoryFactory.create('neti_easy_coupon');
        },

        columns() {
            return this.getColumns();
        },

        defaultCriteria() {
            const criteria      = new Criteria(this.page, this.limit);
            this.naturalSorting = this.sortBy === 'createdAt';

            criteria.setTerm(this.term);
            criteria.addSorting(Criteria.sort(this.sortBy, this.sortDirection, this.naturalSorting));
            criteria.addFilter(Criteria.equals('deleted', false));

            return criteria;
        },

        ...mapGetters('netiEasyCoupon', [
            'currencies',
        ]),
    },

    created() {
        Shopware.State.dispatch('netiEasyCoupon/loadCurrencies');
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

        onUpdateRecords(result) {
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

            item.deleted     = true;
            item.deletedDate = new Date();

            return this.repository.save(item, Shopware.Context.api).then(() => {
                this.getList();
            });
        },

        getColumns() {
            return [
                {
                    property: 'id',
                    dataIndex: 'id',
                    label: 'ID',
                    allowResize: true,
                    visible: false
                },
                {
                    property: 'active',
                    dataIndex: 'active',
                    label: this.$t('neti-easy-coupon.list.column.active')
                },
                {
                    property: 'title',
                    dataIndex: 'title',
                    label: this.$t('neti-easy-coupon.list.column.title'),
                    primary: true
                },
                {
                    property: 'code',
                    dataIndex: 'code',
                    label: this.$t('neti-easy-coupon.list.column.code')
                },
                {
                    property: 'value',
                    dataIndex: 'value',
                    label: this.$t('neti-easy-coupon.list.column.value')
                },
                {
                    property: 'createdAt',
                    dataIndex: 'createdAt',
                    label: this.$t('neti-easy-coupon.list.column.createdAt')
                },
                {
                    property: 'updatedAt',
                    dataIndex: 'updatedAt',
                    label: this.$t('neti-easy-coupon.list.column.updatedAt')
                }
            ];
        },

        renderColumnValue(item) {
            if (item.valueType === 10020) {
                return item.value + ' %';
            }

            if (this.currencies instanceof Array) {
                let currency = this.currencies.find(c => c.id === item.currencyId);

                if (currency) {
                    return (
                        Math.round(item.value * item.currencyFactor * 100) / 100
                    ) + ' ' + currency.isoCode;
                }
            }

            return '-';
        }
    }
});