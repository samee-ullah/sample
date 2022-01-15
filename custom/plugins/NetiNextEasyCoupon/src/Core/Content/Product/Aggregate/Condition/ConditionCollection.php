<?php

declare(strict_types=1);

namespace NetInventors\NetiNextEasyCoupon\Core\Content\Product\Aggregate\Condition;

use Shopware\Core\Content\Rule\Aggregate\RuleCondition\RuleConditionCollection;

class ConditionCollection extends RuleConditionCollection
{
    protected function getExpectedClass(): string
    {
        return ConditionEntity::class;
    }
}
