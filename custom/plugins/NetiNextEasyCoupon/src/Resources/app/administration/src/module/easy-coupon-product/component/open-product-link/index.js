import template from './template.html.twig';

Shopware.Component.register('neti-easy-coupon-product-open-link', {
    template,

    inject: [
        'repositoryFactory'
    ],

    props: {
        model: {
            type: Object,
            required: true
        },
        product: {
            type: Object,
            required: true
        }
    },

    data() {
        return {
            confirmOpen: false
        };
    },

    computed: {
        repository() {
            return this.repositoryFactory.create('neti_easy_coupon_product');
        },
        productRepository() {
            return this.repositoryFactory.create('product');
        }
    },

    methods: {
        onClick() {
            if (false === this.hasChanges()) {
                this.openProduct();
                return;
            }

            this.confirmOpen = true;
        },

        openProduct() {
            this.$router.push({
                name: 'sw.product.detail',
                params: {
                    id:
                    this.product.id
                }
            });
        },

        hasChanges() {
            return this.repository.hasChanges(this.model)
                || this.productRepository.hasChanges(this.product);
        }
    }

});