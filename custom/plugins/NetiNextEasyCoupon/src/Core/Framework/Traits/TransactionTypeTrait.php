<?php

/**
 * @copyright  Copyright (c) 2020, Net Inventors GmbH
 * @category   Shopware
 * @author     sbrueggenolte
 */

declare(strict_types=1);

namespace NetInventors\NetiNextEasyCoupon\Core\Framework\Traits;

use NetInventors\NetiNextEasyCoupon\Core\Framework\Exception\InvalidTypeException;

trait TransactionTypeTrait
{
    use TypeValidationTrait;

    /**
     * @var int
     */
    protected $transactionType;

    public function getTransactionType(): int
    {
        return $this->transactionType;
    }

    /**
     * @param int $transactionType
     *
     * @throws InvalidTypeException
     */
    public function setTransactionType(int $transactionType): void
    {
        $this->validateType($transactionType, self::PREFIX_TRANSACTION_TYPE);

        $this->transactionType = $transactionType;
    }
}
