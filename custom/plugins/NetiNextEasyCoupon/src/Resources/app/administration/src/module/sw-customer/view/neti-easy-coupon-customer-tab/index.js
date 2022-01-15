import template from './template.html.twig';

const { Component, Mixin } = Shopware;
const { Criteria }         = Shopware.Data;
const { mapGetters }       = Shopware.Component.getComponentHelper();

Component.register('neti-easy-coupon-customer-tab', {
    template,

    props: {
        customer: {
            type: Object,
            required: true
        },
    },

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
            isError: false,
            data: null,
            easyCouponIds: null,
            values: [],
            customers: 0,
            sortBy: 'createdAt',
            sortDirection: 'DESC',
            naturalSorting: true,
            isLoading: false,
        };
    },

    computed: {
        repository() {
            return this.repositoryFactory.create('neti_easy_coupon');
        },

        voucherTransactionRepository() {
            return this.repositoryFactory.create('neti_easy_coupon_transaction');
        },

        columns() {
            return this.getColumns();
        },

        voucherTransactionCriteria() {
            const criteria = new Criteria();

            criteria.addFilter(Criteria.equals('customerId', this.customer.id));
            criteria.addFilter(Criteria.equals('easyCoupon.deleted', false));
            criteria.addAssociation('easyCoupon');
            criteria.addAssociation('easyCoupon.currency');
            criteria.addGroupField('easyCouponId');

            return criteria;
        },

        defaultCriteria() {
            const criteria = new Criteria(this.page, this.limit);

            this.naturalSorting = this.sortBy === 'createdAt';

            criteria.setTerm(this.term);
            criteria.addSorting(Criteria.sort(this.sortBy, this.sortDirection, this.naturalSorting));
            criteria.addFilter(Criteria.equals('deleted', false));
            criteria.addFilter(Criteria.equalsAny('id', this.easyCouponIds));

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

            this.loadVouchers().then(result => {
                this.values    = result;
                this.isLoading = false;

                this.getCustomersVoucherIds().then(() => {
                    if (this.easyCouponIds && this.easyCouponIds.length > 0) {
                        this.repository.search(this.defaultCriteria, Shopware.Context.api).then(result => {
                            this.onUpdateRecords(result);
                            this.isLoading = false;
                        }).catch(() => {
                            this.isLoading = false;
                        });
                    }
                });
            }).catch(() => {
                this.isError   = true;
                this.isLoading = false;
            });

        },

        isVoucherPaid(id) {
            const voucher = this.values[id];

            return voucher.active;
        },

        loadVouchers() {
            return new Promise((resolve, reject) => {
                let httpClient = Shopware.Application.getContainer('init').httpClient;
                let headers    = {
                    Accept: 'application/vnd.api+json',
                    Authorization: `Bearer ${ Shopware.Context.api.authToken.access }`,
                    'Content-Type': 'application/json'
                };

                httpClient.get(
                    '_action/neti-easy-coupon/customer-vouchers/' + this.customer.id,
                    { headers }
                ).then(response => {
                    let { success, data } = response.data;

                    if (success === true) {
                        resolve(data);
                    } else {
                        reject('Can not fetch customers vouchers');
                    }
                });
            });
        },

        getCustomersVoucherIds() {
            return this.voucherTransactionRepository.search(this.voucherTransactionCriteria, Shopware.Context.api)
                .then(result => {
                    if (result.total !== null) {
                        this.easyCouponIds = result.map(t => t.easyCouponId);
                    }
                }).catch(() => {
                    this.isLoading = false;
                });
        },

        onUpdateRecords(result) {
            this.total = result.total;
            this.data  = result;
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
                    label: this.$t('neti-easy-coupon.customer.column.paid')
                },
                {
                    property: 'title',
                    dataIndex: 'title',
                    label: this.$t('neti-easy-coupon.customer.column.title'),
                    primary: true
                },
                {
                    property: 'code',
                    dataIndex: 'code',
                    label: this.$t('neti-easy-coupon.customer.column.code')
                },
                {
                    property: 'value',
                    dataIndex: 'value',
                    label: this.$t('neti-easy-coupon.customer.column.value')
                },
                {
                    property: 'cashedValue',
                    dataIndex: 'cashedValue',
                    label: this.$t('neti-easy-coupon.customer.column.cashedValue')
                },
                {
                    property: 'restValue',
                    dataIndex: 'restValue',
                    label: this.$t('neti-easy-coupon.customer.column.restValue')
                },
            ];
        },

        renderColumnValue(item, valueType) {
            const voucher = this.values[item.id];
            const value   = voucher[valueType];

            if (item.valueType === 10020) {
                return value + ' %';
            }

            if (this.currencies instanceof Array && value !== 0) {
                let currency = this.currencies.find(c => c.id === item.currencyId);

                if (currency) {
                    return (
                        Math.round(value * item.currencyFactor * 100) / 100
                    ) + ' ' + currency.isoCode;
                }
            }

            return '-';
        }
    }
});
