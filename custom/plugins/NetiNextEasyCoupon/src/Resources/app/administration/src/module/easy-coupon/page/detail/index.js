import template from './template.html.twig';

import './base';
import './transaction-list';

const { Component, Mixin } = Shopware;
const { Criteria }         = Shopware.Data;
const { mapGetters }       = Shopware.Component.getComponentHelper();

Component.register('neti-easy-coupon-detail', {
    template,

    inject: [
        'repositoryFactory'
    ],

    mixins: [
        Mixin.getByName('notification'),
        Mixin.getByName('placeholder')
    ],

    shortcuts: {
        'SYSTEMKEY+S': 'onSave',
        ESCAPE: 'onAbortButtonClick'
    },

    data() {
        return {
            isLoading: false,
            isSaveSuccessful: false,
            model: null,
            validateCodeTimeout: null
        };
    },

    metaInfo() {
        return {
            title: this.$createTitle(this.identifier)
        };
    },

    computed: {
        repository() {
            return this.repositoryFactory.create('neti_easy_coupon');
        },
        transactionRepository() {
            return this.repositoryFactory.create('neti_easy_coupon_transaction');
        },
        isCreateMode() {
            return this.$route.name.includes('neti.easy_coupon.create');
        },
        conditionRepository() {
            if (!this.model) {
                return null;
            }

            return this.repositoryFactory.create(
                this.model.conditions.entity,
                this.model.conditions.source
            );
        },
        ...mapGetters('netiEasyCoupon', [
            'transactionTypes',
            'currencies',
            'defaultCurrencyId',
            'easyCouponConditions'
        ]),
    },

    created() {
        Shopware.State.dispatch('netiEasyCoupon/loadConfig');
        Shopware.State.dispatch('netiEasyCoupon/loadCurrencies');

        this.createdComponent();
    },

    watch: {
        '$route.params.id'() {
            this.createdComponent();
        },

        'model.currencyId'() {
            this.setCurrencyFactor();
        },

        'currencies'() {
            this.setCurrencyFactor();
        },

        'model.code'() {
            clearTimeout(this.validateCodeTimeout);

            this.validateCodeTimeout = setTimeout(
                this.validateCode.bind(this),
                250
            );
        },

        'model.voucherType'() {
            if (this.model.voucherType === 40010) {
                this.model.discardRemaining = false;
            }
        },

        'defaultCurrencyId'(currencyId) {
            if (
                null !== this.model
                && true === this.isCreateMode
            ) {
                this.model.maxRedemptionValue[0].currencyId = currencyId;
            }
        },
    },

    methods: {
        createdComponent() {
            this.isLoading = true;

            Shopware.State.commit('netiEasyCoupon/setCouponStatus', null);

            if (this.isCreateMode) {
                if (!Shopware.State.getters['context/isSystemDefaultLanguage']) {
                    Shopware.State.commit('context/resetLanguageToDefault');
                }

                this.model = this.repository.create(Shopware.Context.api);

                this.model.active                   = true;
                this.model.discardRemaining         = false;
                this.model.shippingCharge           = false;
                this.model.excludeFromShippingCosts = false;
                this.model.noDeliveryCharge         = false;
                this.model.customerGroupCharge      = false;
                this.model.mailSent                 = false;
                this.model.currencyFactor           = 0;
                this.model.individualVoucher        = false;
                this.model.valueType                = null;
                this.model.voucherType              = null;
                this.model.value                    = 0;
                this.model.maxRedemptionValue       = [
                    {
                        currencyId: this.defaultCurrencyId,
                        net: 0,
                        linked: true,
                        gross: 0
                    }
                ];
                this.model.combineVouchers          = false;
                this.model.redemptionOrder          = 0;

                this.isLoading = false;
            } else {
                this.repository.get(
                    this.$route.params.id,
                    Shopware.Context.api
                ).then(model => {
                    if (!(
                        model.maxRedemptionValue instanceof Array
                    )) {
                        model.maxRedemptionValue = [
                            {
                                currencyId: this.defaultCurrencyId,
                                net: 0,
                                linked: true,
                                gross: 0
                            }
                        ];
                    }

                    this.model     = model;
                    this.isLoading = false;

                    this.loadConditions();
                    this.loadStatus();
                });
            }
        },

        async onSave() {
            this.isSaveSuccessful = false;
            this.isLoading        = true;

            // Set default currencyId if the coupon is "percental"
            if (this.model.valueType === 10020 && !this.model.currencyId) {
                this.model.currencyId = this.currencies.first().id;
            }

            try {
                if (true === this.isCreateMode) {
                    let transaction = this.transactionRepository.create(Shopware.Context.api);

                    transaction = Object.assign(transaction, {
                        transactionType: this.transactionTypes.createdByAdmin,
                        value: this.model.value,
                        internComment: '',
                        currencyFactor: this.model.currencyFactor,
                        currencyId: this.model.currencyId,
                        easyCouponId: this.model.id,
                        customerId: null,
                        userId: null
                    });

                    this.model.conditions = this.easyCouponConditions.conditionTree;

                    await this.validateCode();
                    await this.repository.save(this.model, Shopware.Context.api);
                    await this.transactionRepository.save(transaction, Shopware.Context.api);

                    this.onSaveSuccess();

                    return;
                }

                await this.validateCode();
                await this.repository.save(this.model, Shopware.Context.api);
                await this.syncConditions();

                this.onSaveSuccess();
                this.createdComponent();
            } catch (error) {
                this.onSaveError();
            }
        },

        onSaveFinish() {
            this.isSaveSuccessful = false;

            if (this.isCreateMode) {
                this.$router.push({
                    name: 'neti.easy_coupon.detail',
                    params: {
                        id: this.model.id
                    }
                });
            }
        },

        onSaveSuccess() {
            this.createNotificationSuccess({
                title: this.$t('neti-easy-coupon.detail.successTitle'),
                message: this.$t('neti-easy-coupon.detail.successMessage')
            });

            this.isLoading        = false;
            this.isSaveSuccessful = true;
        },

        onSaveError() {
            this.createNotificationError({
                title: this.$t('neti-easy-coupon.detail.errorTitle'),
                message: this.$t('neti-easy-coupon.detail.errorMessage')
            });

            this.isLoading = false;
        },

        onAbortButtonClick() {
            this.$router.push({ name: 'neti.easy_coupon.overview' });
            this.isLoading = false;
        },

        abortOnLanguageChange() {
            return this.repository.hasChanges(this.model);
        },

        saveOnLanguageChange() {
            return this.onSave();
        },

        onChangeLanguage() {
            this.createdComponent();
        },

        validateCode() {
            return new Promise((resolve, reject) => {
                Shopware.State.dispatch(
                    'netiEasyCoupon/setError',
                    {
                        entity: 'neti_easy_coupon',
                        field: 'code',
                        error: null
                    }
                );

                // If the code is empty we say it's "valid" so the "required validator" will work here
                if (
                    !this.model.code
                ) {
                    resolve();
                    return;
                }

                // If the code was not changed at all, we don't need to check if it's valid
                let { changes } = this.repository.changesetGenerator.generate(this.model);

                if (
                    null === changes
                    || false === changes.hasOwnProperty('code')
                ) {
                    resolve();
                    return;
                }

                let httpClient = Shopware.Application.getContainer('init').httpClient;
                let headers    = {
                    Accept: 'application/vnd.api+json',
                    Authorization: `Bearer ${ Shopware.Context.api.authToken.access }`,
                    'Content-Type': 'application/json'
                };

                httpClient.post(
                    '_action/neti-easy-coupon/validate-code',
                    { code: this.model.code },
                    { headers }
                ).then(response => {
                    let { valid } = response.data;

                    if (false === valid) {
                        Shopware.State.dispatch(
                            'netiEasyCoupon/setError',
                            {
                                entity: 'neti_easy_coupon',
                                field: 'code',
                                error: {
                                    code: 'neti-easy-coupon.codeNotAvailable'
                                }
                            }
                        );

                        reject();
                    } else {
                        resolve();
                    }
                });
            });
        },

        setCurrencyFactor() {
            if (!this.model || !this.currencies || this.model.currencyFactor) {
                return;
            }

            const currency = this.currencies.get(this.model.currencyId);

            if (currency) {
                this.model.currencyFactor = currency.factor;
            }
        },

        loadConditions(conditions = null) {
            const context = { ...Shopware.Context.api, inheritance: true };

            if (conditions === null) {
                return this.conditionRepository.search(new Criteria(), context).then((searchResult) => {
                    return this.loadConditions(searchResult);
                });
            }

            if (conditions.total <= conditions.length) {
                this.model.conditions = conditions;
                return Promise.resolve();
            }

            const criteria = new Criteria(
                conditions.criteria.page + 1,
                conditions.criteria.limit
            );

            if (conditions.entity === 'product') {
                criteria.addAssociation('options.group');
            }

            return this.conditionRepository.search(criteria, conditions.context).then((searchResult) => {
                conditions.push(...searchResult);
                conditions.criteria = searchResult.criteria;
                conditions.total    = searchResult.total;

                return this.loadConditions(conditions);
            });
        },

        async syncConditions() {
            const { conditionTree, deletedIds } = this.easyCouponConditions;

            await this.conditionRepository.sync(conditionTree, Shopware.Context.api);

            if (deletedIds.length > 0) {
                await this.conditionRepository.syncDeleted(deletedIds, Shopware.Context.api);
            }
        },

        loadStatus() {
            Shopware.State.commit('netiEasyCoupon/setCouponStatus', null);

            return new Promise((resolve, reject) => {
                let httpClient = Shopware.Application.getContainer('init').httpClient;
                let headers    = {
                    Accept: 'application/vnd.api+json',
                    Authorization: `Bearer ${ Shopware.Context.api.authToken.access }`,
                    'Content-Type': 'application/json'
                };

                httpClient.get(
                    '_action/neti-easy-coupon/status/' + this.model.id,
                    { headers }
                ).then(response => {
                    let { success, data } = response.data;

                    if (success) {
                        Shopware.State.commit('netiEasyCoupon/setCouponStatus', data);
                    }
                });
            });
        },

        onRefresh() {
            this.loadStatus();
        }
    }
});
