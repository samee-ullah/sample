import './page/shop-finder-list';
import './page/shop-finder-create';
import './page/shop-finder-detail';

import deDE from './snippet/de-DE.json';
import enGB from './snippet/en-GB.json';

const {Module} = Shopware;

Module.register('shop-finder', {
    type: 'plugin',
    name: 'shop-finder',
    title: 'shop-finder.general.mainMenuItemGeneral',
    description: 'shop-finder.general.descriptionTextModule',
    color: '#ff3d58',
    icon: 'default-shopping-paper-bag-product',

    snippets: {
        'de-DE': deDE,
        'en-GB': enGB
    },

    routes: {
        index: {
            component: 'shop-finder-list',
            path: 'index'
        },
        detail: {
            component: 'shop-finder-detail',
            path: 'detail/:id',
            meta: {
                parentPath: 'shop.finder.index'
            }
        },
        create: {
            component: 'shop-finder-create',
            path: 'create',
            meta: {
                parentPath: 'shop.finder.index'
            }
        }
    },

    navigation: [{
        label: 'shop-finder.general.mainMenuItemGeneral',
        color: '#ff3d58',
        path: 'shop.finder.index',
        icon: 'default-shopping-paper-bag-product',
        position: 100,
        parent: 'sw-marketing'
    }]
});
