<?php

declare(strict_types=1);

namespace NetInventors\NetiNextEasyCoupon\Core\Content\EasyCoupon\Rule;

use Shopware\Core\Framework\Rule\RuleScope;

class DateRangeRule extends \Shopware\Core\Framework\Rule\DateRangeRule
{
    public function getName(): string
    {
        return 'netiEasyCouponDateRange';
    }

    public function match(RuleScope $scope): bool
    {
        if (is_string($this->fromDate)) {
            $this->fromDate = new \DateTime($this->fromDate);
        }

        if (is_string($this->toDate)) {
            $this->toDate = new \DateTime($this->toDate);
        }

        $now = new \DateTime('now');

        if (!$this->useTime) {
            $now->setTime(0, 0);
        }

        if ($this->fromDate && $this->fromDate >= $now) {
            return false;
        }

        if ($this->toDate && $this->toDate < $now) {
            return false;
        }

        return true;
    }
}