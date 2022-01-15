<?php

namespace NetInventors\NetiNextEasyCoupon\Service\VoucherRedemption\Validator;

use NetInventors\NetiNextEasyCoupon\Service\VoucherRedemption\ValidationResult;

interface ValidatorInterface
{
    public function validate(
        ValidationContext $validationContext,
        ValidationResult $validationResult
    ): bool;
}
