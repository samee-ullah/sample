<?php

declare(strict_types=1);

namespace NetInventors\NetiNextEasyCoupon\Core\Content\Product\Aggregate\Condition;

use Shopware\Core\Content\Rule\Aggregate\RuleCondition\RuleConditionEntity;

class ConditionEntity extends RuleConditionEntity
{
    /**
     * @var string
     */
    protected $couponId;

    public function getCouponId(): string
    {
        return $this->couponId;
    }

    public function setCouponId(string $couponId): void
    {
        $this->couponId = $couponId;
    }

    /**
     * This is used when the condition is duplicated for another voucher.
     *
     * Shopware is saving the type is value NULL sometimes, which is not allowed by the RuleConditionEntity so I have
     * created this method for make that work without any complications.
     *
     * @return string|null
     */
    public function getLegacyType (): ?string
    {
        return $this->type;
    }
}
