import './store';
import './snippet';
import './service';
import './scss/style.scss';

import './component/value-type-select';
import './component/voucher-type-select';
import './component/product-value-type-select';
import './component/open-easy-coupon-product-link';
import './component/discard-changes-modal';
import './component/payment-status-select';
import './component/neti-config-icon';
import './component/neti-powered-by';
import './component/neti-easy-coupon-cart-processor-priority-config'
import './component/tabs-item';
import './component/sidebar-menu';

import './module/easy-coupon';
import './module/easy-coupon-product';
import './module/sw-product';
import './module/sw-customer';
import './module/sw-order';

import './app/component/rule/condition-type/neti-easy-coupon-uses-per-customer';
import './app/component/rule/condition-type/neti-easy-coupon-line-item-of-manufacturer';
import './app/component/rule/condition-type/neti-easy-coupon-customer';
import './app/component/rule/condition-type/neti-easy-coupon-total-uses';
import './app/component/rule/condition-type/neti-easy-coupon-mail-address';

// https://docs.shopware.com/en/shopware-platform-dev-en/how-to/new-tab-admin?category=shopware-platform-dev-en/how-to
Shopware.Module.register('neti-easy-coupon-tab-attribute', {
    routeMiddleware(next, currentRoute) {
        if ('sw.customer.detail' === currentRoute.name) {
            currentRoute.children.push({
                name: 'neti.easy-coupon.customer.detail.vouchers',
                path: '/sw/customer/detail/:id/vouchers',
                component: 'neti-easy-coupon-customer-tab',
                meta: {
                    parentPath: 'sw.customer.index'
                }
            });
        }
        next(currentRoute);
    }
});