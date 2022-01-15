import template from './neti-powered-by.twig';
import './neti-powered-by.scss'

Shopware.Component.register('neti-easy-coupon-powered-by', {
    template,

    props: {
        pluginName: {
            type: String,
            required: true
        }
    },

    computed: {
        pluginLink() {
            return 'https://store.shopware.com/search?sSearch=' + this.pluginName
        }
    }
});
