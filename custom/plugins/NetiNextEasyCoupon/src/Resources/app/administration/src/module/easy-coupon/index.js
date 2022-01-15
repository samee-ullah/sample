import deDE from './snippet/de-DE.json';
import enGB from './snippet/en-GB.json';

import './component/entity-listing';
import './component/condition-tree';
import './component/value-field';
import './component/price-field-currency-modal';
import './component/price-field';
import './component/status';
import './component/create-transaction-modal';

import './page/list';
import './page/detail';

Shopware.Module.register('neti-easy_coupon', {
    type: 'plugin',
    name: 'NetiNextEasyCoupon',
    title: 'neti-easy-coupon.general.mainMenuItemGeneral',
    description: 'neti-easy-coupon.general.descriptionTextModule',
    icon: 'default-package-gift',
    entity: 'neti_easy_coupon',
    snippets: {
        'de-DE': deDE,
        'en-GB': enGB
    },
    routes: {
        overview: {
            components: {
                default: 'neti-easy-coupon-list'
            },
            path: 'overview'
        },
        create: {
            component: 'neti-easy-coupon-detail',
            path: 'create',
            meta: {
                parentPath: 'neti.easy_coupon.overview'
            },
            redirect: {
                name: 'neti.easy_coupon.create.base'
            },
            children: {
                base: {
                    component: 'neti-easy-coupon-detail-base',
                    path: 'base',
                    meta: {
                        parentPath: 'neti.easy_coupon.overview'
                    },
                }
            }
        },
        detail: {
            component: 'neti-easy-coupon-detail',
            path: 'detail/:id',
            meta: {
                parentPath: 'neti.easy_coupon.overview'
            },
            redirect: {
                name: 'neti.easy_coupon.detail.base'
            },
            children: {
                base: {
                    component: 'neti-easy-coupon-detail-base',
                    path: 'base',
                    meta: {
                        parentPath: 'neti.easy_coupon.overview'
                    },
                },
                transactions: {
                    component: 'neti-easy-coupon-detail-transaction-list',
                    path: 'transactions',
                    meta: {
                        parentPath: 'neti.easy_coupon.overview'
                    },
                }
            }
        }
    },
    navigation: [
        {
            id: 'neti-easy-coupon-list',
            path: 'neti.easy_coupon.overview',
            label: 'neti-easy-coupon.general.mainMenuItemGeneral',
            position: -1000,
            parent: 'sw-marketing'
        }
    ]
});
