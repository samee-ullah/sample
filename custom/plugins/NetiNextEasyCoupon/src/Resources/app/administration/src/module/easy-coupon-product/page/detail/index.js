import template from './template.html.twig';

import './base';

const { Component, Mixin } = Shopware;
const { Criteria }         = Shopware.Data;
const { mapGetters }       = Shopware.Component.getComponentHelper();

Component.register('neti-easy-coupon-product-detail', {
    template,

    inject: [
        'repositoryFactory',
        'numberRangeService'
    ],

    mixins: [
        Mixin.getByName('notification'),
        Mixin.getByName('placeholder'),
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
            productNumberPreview: null
        };
    },

    metaInfo() {
        return {
            title: this.$createTitle(this.identifier)
        };
    },

    computed: {
        ...mapGetters('netiEasyCoupon', [
            'defaultCurrencyId',
            'priceField',
            'freeTaxId',
            'easyCouponProductConditions'
        ]),
        repository() {
            return this.repositoryFactory.create('neti_easy_coupon_product');
        },
        productRepository() {
            return this.repositoryFactory.create('product');
        },
        taxRepository() {
            return this.repositoryFactory.create('tax');
        },
        isCreateMode() {
            return this.$route.name.includes('neti.easy_coupon_product.create');
        },
        defaultCriteria() {
            const criteria = new Criteria();

            criteria.addAssociation('product.categories');
            criteria.addAssociation('product.visibilities.salesChannel');
            criteria.addAssociation('product.tags');

            return criteria;
        },
        productNotSaved() {
            return !this.model
                || !this.model.product
                || this.model.product._isNew;
        },
        conditionRepository() {
            if (!this.model) {
                return null;
            }

            return this.repositoryFactory.create(
                this.model.conditions.entity,
                this.model.conditions.source
            );
        }
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

        'defaultCurrencyId'(currencyId) {
            if (
                null !== this.model
                && true === this.isCreateMode
            ) {
                this.model.value[0].currencyId = currencyId;
            }
        },

        'model.product.productNumber'() {
            Shopware.State.dispatch('netiEasyCoupon/setError', {
                entity: 'product',
                field: 'productNumber',
                error: null
            });
        },

        'freeTaxId'(freeTaxId) {
            if (
                this.model
                && this.model.product
                && null === this.model.product.taxId
                && freeTaxId
            ) {
                this.model.product.taxId = freeTaxId;
            }
        }
    },

    methods: {
        createdComponent() {
            this.isLoading = true;

            if (this.isCreateMode) {
                if (!Shopware.State.getters['context/isSystemDefaultLanguage']) {
                    Shopware.State.commit('context/resetLanguageToDefault');
                }

                this.model = this.repository.create(Shopware.Context.api);
                this.model = Object.assign(this.model, {
                    title: '',
                    valueType: null,
                    value: [
                        {
                            currencyId: this.defaultCurrencyId,
                            gross: 0,
                            net: 0,
                            linked: false,
                            from: 0,
                            to: 0,
                            selectableValues: []
                        }
                    ],
                    taxId: null,
                    orderPositionNumber: '',
                    postal: false,
                    shippingCharge: false,
                    excludeFromShippingCosts: false,
                    noDeliveryCharge: false,
                    customerGroupCharge: false,
                    combineVouchers: false,
                    validityTime: 0,
                    validityByEndOfYear: false
                });

                this.isLoading = false;
            } else {
                this.repository.get(
                    this.$route.params.id,
                    Shopware.Context.api,
                    this.defaultCriteria
                ).then((model) => {
                    this.model     = model;
                    this.isLoading = false;

                    this.createProductIfNotExists();

                    return this.loadConditions();
                });
            }
        },

        async onSave() {
            this.isSaveSuccessful = false;
            this.isLoading        = true;

            try {
                if (true === this.isCreateMode) {
                    this.model.conditions = this.easyCouponProductConditions.conditionTree;

                    await this.repository.save(this.model, Shopware.Context.api);

                    this.onSaveSuccess();
                } else {
                    await this.repository.save(this.model, Shopware.Context.api);
                    await this.syncConditions();

                    if (
                        true === this.model.product._isNew
                        && this.productNumberPreview === this.model.product.productNumber
                    ) {
                        const response = await this.numberRangeService.reserve('product');

                        this.productNumberPreview        = 'reserved';
                        this.model.product.productNumber = response.number;
                    }

                    await this.productRepository.save(this.model.product, Shopware.Context.api);

                    if (this.model.productId !== this.model.product.id) {
                        const product = await this.productRepository.get(this.model.product.id, Shopware.Context.api);

                        this.model.productId        = product.id;
                        this.model.productVersionId = product.versionId;

                        await this.repository.save(this.model, Shopware.Context.api);
                    }

                    this.onSaveSuccess();
                    this.createdComponent();
                }
            } catch (error) {
                this.handleProductSaveErrors(error);
                this.onSaveError();
            }
        },

        onSaveFinish() {
            this.isSaveSuccessful = false;

            if (this.isCreateMode) {
                this.$router.push({
                    name: 'neti.easy_coupon_product.detail',
                    params: {
                        id: this.model.id
                    }
                });
            }
        },

        onSaveSuccess() {
            this.createNotificationSuccess({
                title: this.$t('neti-easy-coupon-product.detail.successTitle'),
                message: this.$t('neti-easy-coupon-product.detail.successMessage')
            });

            this.isLoading        = false;
            this.isSaveSuccessful = true;
        },

        onSaveError() {
            this.createNotificationError({
                title: this.$t('neti-easy-coupon-product.detail.errorTitle'),
                message: this.$t('neti-easy-coupon-product.detail.errorMessage')
            });

            this.isLoading = false;
        },

        onAbortButtonClick() {
            this.$router.push({ name: 'neti.easy_coupon_product.overview' });
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

        createProductIfNotExists() {
            if (this.model.product) {
                return;
            }

            this.model.product = this.productRepository.create(Shopware.Context.api);
            this.model.product = Object.assign(this.model.product, {
                name: '',
                active: true,
                taxId: this.freeTaxId || null,
                price: [
                    {
                        currencyId: this.defaultCurrencyId,
                        net: 0,
                        linked: true,
                        gross: 0
                    }
                ],
                stock: null,
                productNumber: null
            });

            this.numberRangeService.reserve('product', '', true).then(response => {
                this.model.product.productNumber = response.number;
                this.productNumberPreview        = response.number;
            });
        },

        handleProductSaveErrors(error) {
            let errors = [];

            if (
                error
                && error.response
                && error.response.data
                && error.response.data.errors
            ) {
                errors = error.response.data.errors;
            }

            errors.forEach(error => {
                if ('CONTENT__DUPLICATE_PRODUCT_NUMBER' === error.code) {
                    Shopware.State.dispatch('netiEasyCoupon/setError', {
                        entity: 'product',
                        field: 'productNumber',
                        error: {
                            code: 'neti-easy-coupon.productNumberAlreadyInUse'
                        }
                    });
                }
            });
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
            const { conditionTree, deletedIds } = this.easyCouponProductConditions;

            await this.conditionRepository.sync(conditionTree, Shopware.Context.api);

            if (deletedIds.length > 0) {
                await this.conditionRepository.syncDeleted(deletedIds, Shopware.Context.api);
            }
        }
    }
});
