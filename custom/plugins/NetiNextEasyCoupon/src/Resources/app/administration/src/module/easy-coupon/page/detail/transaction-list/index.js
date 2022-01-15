import template from './template.html.twig';

const { Component, Mixin } = Shopware;
const { Criteria }         = Shopware.Data;
const { mapGetters }       = Shopware.Component.getComponentHelper();

Component.register('neti-easy-coupon-detail-transaction-list', {
    template,

    props: {
        model: {
            type: Object,
            required: true
        }
    },

    inject: [
        'repositoryFactory',
    ],

    mixins: [
        Mixin.getByName('notification'),
        Mixin.getByName('listing')
    ],

    data() {
        return {
            data: null,
            sortBy: 'createdAt',
            sortDirection: 'DESC',
            naturalSorting: true,
            isLoading: false,

            createTransactionModal: false
        };
    },

    computed: {
        ...mapGetters('netiEasyCoupon', [
            'defaultCurrency'
        ]),

        repository() {
            return this.repositoryFactory.create('neti_easy_coupon_transaction');
        },

        columns() {
            return this.getColumns();
        },

        defaultCriteria() {
            const criteria      = new Criteria(this.page, this.limit);
            this.naturalSorting = this.sortBy === 'createdAt';

            criteria.setTerm(this.term);
            criteria.addSorting(Criteria.sort(this.sortBy, this.sortDirection, this.naturalSorting));
            criteria.addFilter(Criteria.equals('easyCouponId', this.model.id));

            criteria.addAssociation('currency');
            criteria.addAssociation('customer');
            criteria.addAssociation('order');
            criteria.addAssociation('user');

            return criteria;
        }
    },

    methods: {
        onRefresh() {
            this.$emit('refresh');
            this.getList();
        },

        getList() {
            this.isLoading = true;

            this.repository.search(this.defaultCriteria, Shopware.Context.api).then((items) => {
                this.total     = items.total;
                this.data      = items;
                this.isLoading = false;

                return items;
            }).catch(() => {
                this.isLoading = false;
            });
        },

        getColumns() {
            return [
                {
                    property: 'createdAt',
                    dataIndex: 'createdAt',
                    label: this.$t('neti-easy-coupon.transaction-list.column.createdAt')
                },
                {
                    property: 'id',
                    dataIndex: 'id',
                    label: this.$t('neti-easy-coupon.transaction-list.column.id'),
                    allowResize: true,
                    visible: false
                },
                {
                    property: 'value',
                    dataIndex: 'value',
                    label: this.$t('neti-easy-coupon.transaction-list.column.value'),
                },
                {
                    property: 'currency.isoCode',
                    dataIndex: 'currency.isoCode',
                    label: this.$t('neti-easy-coupon.transaction-list.column.currency'),
                },
                {
                    property: 'internComment',
                    dataIndex: 'internComment',
                    label: this.$t('neti-easy-coupon.transaction-list.column.internComment'),
                },
                {
                    property: 'order',
                    dataIndex: 'order',
                    label: this.$t('neti-easy-coupon.transaction-list.column.orderNumber'),
                },
                {
                    property: 'user.username',
                    dataIndex: 'user.username',
                    label: this.$t('neti-easy-coupon.transaction-list.column.username'),
                },
                {
                    property: 'customer',
                    dataIndex: 'customer',
                    label: this.$t('neti-easy-coupon.transaction-list.column.customer'),
                }
            ];
        },

        getValue (transaction) {
            return Math.round((transaction.value * transaction.currencyFactor / this.defaultCurrency.factor) * 100) / 100;
        },

        onCreateTransactionButtonClicked() {
            this.createTransactionModal = true;
        }
    }

});
