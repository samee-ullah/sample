<?php

declare(strict_types=1);

namespace NetInventors\NetiNextEasyCoupon\Core\Content\Transaction;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

class TransactionCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return TransactionEntity::class;
    }

    public function sum(): float
    {
        $values = $this->map(function (TransactionEntity $transaction) {
            return $transaction->getValue();
        });

        return \array_sum($values);
    }
}
