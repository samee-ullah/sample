import template from './template.html.twig';
import './style.scss';

const { mapGetters } = Shopware.Component.getComponentHelper();

Shopware.Component.register('neti-easy-coupon-condition-tree', {
    template,

    inject: [
        'easyCouponRuleConditionService',
        'repositoryFactory',
    ],

    props: {
        model: {
            required: true,
            type: Object
        }
    },

    computed: {
        ...mapGetters('netiEasyCoupon', [
            'easyCouponConditions'
        ]),
        conditionRepository() {
            return this.repositoryFactory.create(
                this.model.conditions.entity,
                this.model.conditions.source
            );
        }
    },

    methods: {
        conditionsChanged({ conditions, deletedIds }) {
            Shopware.State.commit(
                'netiEasyCoupon/setEasyCouponConditions',
                {
                    conditionTree: conditions,
                    deletedIds: [...this.easyCouponConditions.deletedIds, ...deletedIds]
                }
            );
        }
    }
});