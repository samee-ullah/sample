import RuleConditionService from './rule-condition.service';
import './condition-type-data-provider.decorator';

const { Application } = Shopware;

Application.addServiceProvider('easyCouponRuleConditionService', () => {
    return new RuleConditionService();
});
