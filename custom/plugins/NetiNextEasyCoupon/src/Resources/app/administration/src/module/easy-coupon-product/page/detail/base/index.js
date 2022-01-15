import template from './template.html.twig';
import './style.scss';

const { Component, Mixin } = Shopware;
const { mapPropertyErrors, mapGetters } = Shopware.Component.getComponentHelper();

Component.register('neti-easy-coupon-product-detail-base', {
    template,

    mixins: [
        Mixin.getByName('placeholder')
    ],

    props: {
        model: {
            type: Object,
            required: true
        },
        isCreateMode: {
            type: Boolean,
            required: true,
        },
        repository: {
            type: Object,
            required: true
        }
    },

    computed: {
        product() {
            return this.model.product
        },
        ...mapPropertyErrors(
            'model',
            [
                'title',
                'orderPositionNumber',
            ]
        ),
        ...mapPropertyErrors(
            'product',
            [
                'name',
                'productNumber',
                'stock',
                'taxId'
            ]
        ),
        ...mapGetters('netiEasyCoupon', [
            'customApiErrors'
        ])
    },

    methods: {
        openProduct() {
            console.log(this.repository.hasChanges(this.model));
        }
    }

});
