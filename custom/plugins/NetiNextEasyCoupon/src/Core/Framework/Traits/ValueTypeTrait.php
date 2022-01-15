<?php

/**
 * @copyright  Copyright (c) 2020, Net Inventors GmbH
 * @category   Shopware
 * @author     sbrueggenolte
 */

declare(strict_types=1);

namespace NetInventors\NetiNextEasyCoupon\Core\Framework\Traits;

use NetInventors\NetiNextEasyCoupon\Core\Framework\Exception\InvalidTypeException;

trait ValueTypeTrait
{
    use TypeValidationTrait;

    /**
     * @var int
     */
    protected $valueType;

    public function getValueType(): int
    {
        return $this->valueType;
    }

    /**
     * @param int $valueType
     *
     * @throws InvalidTypeException
     */
    public function setValueType(int $valueType): void
    {
        $this->validateType($valueType, self::PREFIX_VALUE_TYPE);

        $this->valueType = $valueType;
    }
}
