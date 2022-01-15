let isLoadingConfig = false;
const { Criteria } = Shopware.Data;

export default {
    namespaced: true,

    state: {
        defaultCurrency: null,
        priceField: null,
        valueTypes: [],
        voucherTypes: [],
        productValueTypes: [],
        transactionTypes: {},
        currencies: null,
        customApiErrors: {
            neti_easy_coupon: {
                code: null,
            },
            product: {
                productNumber: null
            }
        },
        freeTaxId: null,
        detail: {
            neti_easy_coupon: {
                conditions: {
                    conditionTree: null,
                    deletedIds: []
                }
            },
            neti_easy_coupon_product: {
                conditions: {
                    conditionTree: null,
                    deletedIds: []
                }
            }
        },
        couponStatus: null
    },

    mutations: {
        setDefaultCurrency(state, payload) {
            state.defaultCurrency = payload;
        },
        setPriceField(state, payload) {
            state.priceField = payload;
        },
        setValueTypes(state, payload) {
            state.valueTypes = payload;
        },
        setVoucherTypes(state, payload) {
            state.voucherTypes = payload;
        },
        setProductValueTypes(state, payload) {
            state.productValueTypes = payload;
        },
        setTransactionTypes (state, payload) {
            state.transactionTypes = payload;
        },
        setCurrencies(state, payload) {
            state.currencies = payload;
        },
        setError(state, { entity, field, error }) {
            state.customApiErrors[entity][field] = error;
        },
        setFreeTaxId (state, payload) {
            state.freeTaxId = payload;
        },
        setEasyCouponConditions(state, { conditionTree, deletedIds }) {
            state.detail.neti_easy_coupon.conditions.conditionTree = conditionTree;
            state.detail.neti_easy_coupon.conditions.deletedIds    = deletedIds;
        },
        setEasyCouponProductConditions(state, { conditionTree, deletedIds }) {
            state.detail.neti_easy_coupon_product.conditions.conditionTree = conditionTree;
            state.detail.neti_easy_coupon_product.conditions.deletedIds    = deletedIds;
        },
        setCouponStatus (state, payload) {
            state.couponStatus = payload;
        }
    },

    actions: {
        loadConfig(store) {
            if (isLoadingConfig) {
                return;
            }

            let httpClient = Shopware.Application.getContainer('init').httpClient;
            let headers    = {
                Accept: 'application/vnd.api+json',
                Authorization: `Bearer ${ Shopware.Context.api.authToken.access }`,
                'Content-Type': 'application/json'
            };

            isLoadingConfig = true;
            httpClient.get('_action/neti-easy-coupon/config', { headers }).then(({ data: response }) => {
                isLoadingConfig = false;

                store.commit('setValueTypes', response.valueTypes);
                store.commit('setVoucherTypes', response.voucherTypes);
                store.commit('setProductValueTypes', response.productValueTypes);
                store.commit('setTransactionTypes', response.transactionTypes);

                store.commit('setDefaultCurrency', response.defaultCurrency);
                store.commit('setPriceField', response.priceField);
                store.commit('setFreeTaxId', response.freeTaxId);
            });
        },
        loadCurrencies (store) {
            if (store.state.currencies && store.state.currencies.length > 0) {
                return;
            }

            const criteria           = new Criteria();
            const repositoryFactory  = Shopware.Service('repositoryFactory');
            const currencyRepository = repositoryFactory.create('currency');

            criteria.addSorting({ field: 'createdAt', order: 'ASC' });

            currencyRepository.search(criteria, Shopware.Context.api).then(models => {
                store.commit('setCurrencies', models);
            });
        },
        setError(store, { entity, field, error }) {
            store.commit(
                'setError',
                {
                    entity,
                    field,
                    error
                }
            );
        }
    },

    getters: {
        valueTypes: state => state.valueTypes,
        voucherTypes: state => state.voucherTypes,
        productValueTypes: state => state.productValueTypes,
        transactionTypes: state => state.transactionTypes,
        defaultCurrencyId: state => state.defaultCurrency ? state.defaultCurrency.id : null,
        priceField: state => state.priceField,
        currencies: state => state.currencies,
        customApiErrors: state => state.customApiErrors,
        freeTaxId: state => state.freeTaxId,
        defaultCurrency: state => state.defaultCurrency,
        easyCouponConditions: state => state.detail.neti_easy_coupon.conditions,
        easyCouponProductConditions: state => state.detail.neti_easy_coupon_product.conditions,
        couponStatus: state => state.couponStatus
    }
};