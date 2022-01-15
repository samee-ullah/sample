import template from './template.html.twig';
import './style.scss';

Shopware.Component.register('neti-easy-coupon-open-easy-coupon-product-link', {
    template,

    inject: [
        'repositoryFactory'
    ],

    props: {
        product: {
            required: true,
            type: Object
        }
    },

    data() {
        return {
            confirmOpen: false
        };
    },

    computed: {
        visible() {
            return this.product
                && this.product.extensions
                && this.product.extensions.netiEasyCouponProduct;
        },
        productRepository() {
            return this.repositoryFactory.create('product');
        }
    },

    methods: {
        onClick() {
            if (true === this.hasChanges()) {
                this.confirmOpen = true;

                return;
            }

            this.openCoupon();
        },

        openCoupon() {
            this.$router.push({
                name: 'neti.easy_coupon_product.detail',
                params: {
                    id:
                    this.product.extensions.netiEasyCouponProduct.id
                }
            });
        },

        hasChanges() {
            return this.productRepository.hasChanges(this.product);
        }
    }
});