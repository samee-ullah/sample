<?php

/**
 * @copyright  Copyright (c) 2020, Net Inventors GmbH
 * @category   Shopware
 * @author     sbrueggenolte
 */

declare(strict_types=1);

namespace NetInventors\NetiNextEasyCoupon\Core\Framework\Traits;

use NetInventors\NetiNextEasyCoupon\Core\Framework\Exception\InvalidTypeException;

trait VoucherTypeTrait
{
    use TypeValidationTrait;

    /**
     * @var int
     */
    protected $voucherType;

    public function getVoucherType(): int
    {
        return $this->voucherType;
    }

    /**
     * @param int $voucherType
     *
     * @throws InvalidTypeException
     */
    public function setVoucherType(int $voucherType): void
    {
        $this->validateType($voucherType, self::PREFIX_VOUCHER_TYPE);

        $this->voucherType = $voucherType;
    }
}
