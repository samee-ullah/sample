<?php

/**
 * @copyright  Copyright (c) 2020, Net Inventors GmbH
 * @category   Shopware
 * @author     sbrueggenolte
 */

declare(strict_types=1);

namespace NetInventors\NetiNextEasyCoupon\Core\Framework\Traits;

use NetInventors\NetiNextEasyCoupon\Core\Framework\Exception\InvalidTypeException;

trait TypeValidationTrait
{
    /**
     * @param int    $type
     * @param string $constantPrefix
     * @param bool   $thowException
     *
     * @return bool
     * @throws InvalidTypeException
     */
    public function validateType(int $type, string $constantPrefix, bool $thowException = true): bool
    {
        $valid = \in_array($type, self::getValidTypes($constantPrefix), true);

        if (!$valid && $thowException) {
            throw new InvalidTypeException(
                \sprintf('Invalid %s "%d".', \strtolower(str_replace('_', ' ', $constantPrefix)), $type)
            );
        }

        return $valid;
    }
}
