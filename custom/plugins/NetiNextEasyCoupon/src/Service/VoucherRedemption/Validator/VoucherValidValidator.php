<?php

declare(strict_types=1);

namespace NetInventors\NetiNextEasyCoupon\Service\VoucherRedemption\Validator;

use NetInventors\NetiNextEasyCoupon\Service\VoucherRedemption\ValidationResult;
use NetInventors\NetiNextEasyCoupon\Service\VoucherRedemption\Validator\Error\NotValidError;

class VoucherValidValidator implements ValidatorInterface
{
    public function validate(
        ValidationContext $validationContext,
        ValidationResult $validationResult
    ): bool {
        $easyCoupon = $validationContext->getEasyCoupon();
        $validUntil = $easyCoupon->getValidUntil();

        if (
            $validUntil !== null
            && ($validUntil->getTimestamp() - (new \DateTime())->getTimestamp()) < 0
        ) {
            $validationResult->addErrors(
                new NotValidError($easyCoupon->getCode())
            );

            return false;
        }

        return true;
    }
}
