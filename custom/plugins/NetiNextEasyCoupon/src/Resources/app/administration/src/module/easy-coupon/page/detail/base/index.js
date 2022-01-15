import template from './template.html.twig';
import './style.scss';
import copyTextToClipboard from '../../../../../component/copy-to-clipboard';

const { Component, Mixin }         = Shopware;
const { mapPropertyErrors, mapGetters } = Shopware.Component.getComponentHelper();

Component.register('neti-easy-coupon-detail-base', {
    template,

    mixins: [
        Mixin.getByName('placeholder'),
        Mixin.getByName('notification')
    ],

    props: {
        model: {
            type: Object,
            required: true
        },
        isCreateMode: {
            type: Boolean,
            required: true,
        }
    },

    data() {
        return {
            isGeneratingCode: false,
            isGeneratingCodeSuccessful: false
        };
    },

    computed: {
        ...mapPropertyErrors(
            'model',
            [
                'code',
                'currencyId',
                'orderPositionNumber',
                'title',
                'value',
                'valueType',
                'voucherType',
                'comment'
            ]
        ),
        ...mapGetters(
            'netiEasyCoupon',
            [
                'customApiErrors'
            ]
        ),
        valueContainerColumns() {
            if (this.model.valueType === 10020) {
                return '1fr 1fr';
            }

            return '1fr 1fr 1fr auto';
        },
        valueTypeHelpText() {
            return this.$t('neti-easy-coupon.voucher-type-select.helpTexts.' + this.model.voucherType);
        }
    },

    watch: {
        valueContainerColumns() {
            this.$nextTick(() => this.$refs.valueContainer.updateCssGrid());
        },
        'model.valueType'(valueType) {
            if (valueType === 10020 && true === this.model.discardRemaining) {
                this.model.discardRemaining = false;
            }
        }
    },

    methods: {
        onGenerateCode() {
            let httpClient = Shopware.Application.getContainer('init').httpClient;
            let headers    = {
                Accept: 'application/vnd.api+json',
                Authorization: `Bearer ${ Shopware.Context.api.authToken.access }`,
                'Content-Type': 'application/json'
            };

            this.isGeneratingCode           = true;
            this.isGeneratingCodeSuccessful = false;

            httpClient.get('_action/neti-easy-coupon/generate-code', { headers }).then(({ data: response }) => {
                this.isGeneratingCode = false;

                if (typeof response.code === 'string') {
                    this.model.code = response.code;

                    this.isGeneratingCodeSuccessful = true;
                } else {
                    this.createNotificationError({
                        title: this.$t('neti-easy-coupon.detail.generateCode.errorTitle'),
                        message: this.$t('neti-easy-coupon.detail.generateCode.errorMessage')
                    });
                }
            });
        },
        onCopyCode() {
            copyTextToClipboard(this.model.code).then(() => {
                this.createNotificationSuccess({
                    title: this.$t('neti-easy-coupon.detail.copyCode.successTitle'),
                    message: this.$t('neti-easy-coupon.detail.copyCode.successMessage')
                });
            }).catch(() => {
                this.createNotificationError({
                    title: this.$t('neti-easy-coupon.detail.copyCode.errorTitle'),
                    message: this.$t('neti-easy-coupon.detail.copyCode.errorMessage')
                });
            })
        }
    }

});
