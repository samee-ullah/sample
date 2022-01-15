import template from './template.html.twig';
import './style.scss';

const { Component } = Shopware;

Component.register('neti-easy-coupon-order-detail-vouchers', {
    template,

    props: {
        order: {
            type: Object,
            required: true
        },
        data: {
            type: Array,
            required: true
        },
        isLoading: {
            type: Boolean,
            required: false
        }
    },

    computed: {
        columns() {
            return this.getColumns();
        },

        mappedData() {
            const data = [];

            this.data.forEach(lineItem => {
                const type = this.getVoucherType(lineItem);

                if ('purchasableVoucher' === type) {
                    const vouchers = lineItem.payload.netiNextEasyCoupon.vouchers || [];

                    if (!vouchers.length) {
                        data.push(lineItem);
                    }

                    vouchers.map(voucher => {
                        return {
                            ...lineItem,
                            /**
                             * This is only used to prevent the following warning from Vue
                             * Duplicate keys detected: 'd5dd7c20b9d24ce0890435818278ca6a'. This may cause an update error.
                             */
                            id: voucher.id,
                            // The payload need to be created like this because of reference hell.
                            payload: {
                                ...lineItem.payload,
                                netiNextEasyCoupon: {
                                    ...lineItem.payload.netiNextEasyCoupon,
                                    voucherId: voucher.id,
                                    code: voucher.code
                                }
                            }
                        };
                    }).forEach(lineItem => data.push(lineItem));
                } else {
                    data.push(lineItem);
                }
            });

            return data;
        }
    },

    methods: {
        getColumns() {
            return [
                {
                    property: 'type',
                    dataIndex: 'type',
                    label: this.$t('neti-easy-coupon.order-detail.voucherList.column.type'),
                },
                {
                    property: 'name',
                    dataIndex: 'name',
                    label: this.$t('neti-easy-coupon.order-detail.voucherList.column.name'),
                },
                {
                    property: 'value',
                    dataIndex: 'value',
                    label: this.$t('neti-easy-coupon.order-detail.voucherList.column.value')
                },
                {
                    property: 'code',
                    dataIndex: 'code',
                    label: this.$t('neti-easy-coupon.order-detail.voucherList.column.code'),
                }
            ];
        },

        getVoucherType(lineItem) {
            if (lineItem.payload.discountScope === 'netiEasyCoupon') {
                return 'voucher';
            }

            if ('netiNextEasyCoupon' in lineItem.payload) {
                return 'purchasableVoucher';
            }

            return 'invalid';
        },

        getCode (lineItem) {
            return lineItem.payload.code || lineItem.payload.netiNextEasyCoupon.code
        },

        onOpenVoucher(lineItem) {
            const type    = this.getVoucherType(lineItem);
            let voucherId = null;

            if (type === 'voucher') {
                voucherId = lineItem.payload.discountId;
            } else if (type === 'purchasableVoucher' || 'netiNextEasyCoupon' in lineItem.payload) {
                voucherId = lineItem.payload.netiNextEasyCoupon.voucherId;
            }

            if (null !== voucherId && undefined !== voucherId) {
                this.$router.push(
                    {
                        name: 'neti.easy_coupon.detail',
                        params: {
                            id: voucherId
                        }
                    }
                );
            }
        }
    }
});