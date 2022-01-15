import deDE from './snippet/de-DE.json';
import enGB from './snippet/en-GB.json';

import './component/price-field';
import './component/price-field-currency-modal';
import './component/product-visibility-select';
import './component/product-price-field';
import './component/open-product-link';
import './component/entity-listing';
import './component/condition-tree';

import './page/list';
import './page/detail';

Shopware.Module.register('neti-easy_coupon_product', {
    type: 'plugin',
    name: 'NetiNextEasyCoupon',
    title: 'neti-easy-coupon-product.general.mainMenuItemGeneral',
    description: 'neti-easy-coupon-product.general.descriptionTextModule',
    icon: 'default-device-database',
    entity: 'neti_easy_coupon_product',
    snippets: {
        'de-DE': deDE,
        'en-GB': enGB
    },
    routes: {
        overview: {
            components: {
                default: 'neti-easy-coupon-product-list'
            },
            path: 'overview'
        },
        create: {
            component: 'neti-easy-coupon-product-detail',
            path: 'create',
            meta: {
                parentPath: 'neti.easy_coupon_product.overview'
            },
            redirect: {
                name: 'neti.easy_coupon_product.create.base'
            },
            children: {
                base: {
                    component: 'neti-easy-coupon-product-detail-base',
                    path: 'base',
                    meta: {
                        parentPath: 'neti.easy_coupon_product.overview'
                    },
                }
            }
        },
        detail: {
            component: 'neti-easy-coupon-product-detail',
            path: 'detail/:id',
            meta: {
                parentPath: 'neti.easy_coupon_product.overview'
            },
            redirect: {
                name: 'neti.easy_coupon_product.detail.base'
            },
            children: {
                base: {
                    component: 'neti-easy-coupon-product-detail-base',
                    path: 'base',
                    meta: {
                        parentPath: 'neti.easy_coupon_product.overview'
                    },
                },
            }
        }
    }
});
