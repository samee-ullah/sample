<?php

declare(strict_types=1);

namespace NetInventors\NetiNextEasyCoupon\Service\VoucherRedemption\Validator;

use NetInventors\NetiNextEasyCoupon\Service\VoucherRedemption\ValidationResult;
use NetInventors\NetiNextEasyCoupon\Service\VoucherRedemption\Validator\Error\NotActiveOrDeletedError;

class VoucherActiveValidator implements ValidatorInterface
{
    public function validate(
        ValidationContext $validationContext,
        ValidationResult $validationResult
    ): bool {
        $easyCoupon = $validationContext->getEasyCoupon();

        if (!$easyCoupon->isActive() || $easyCoupon->isDeleted()) {
            $validationResult->addErrors(
                new NotActiveOrDeletedError($easyCoupon->getCode())
            );

            return false;
        }

        return true;
    }
}
