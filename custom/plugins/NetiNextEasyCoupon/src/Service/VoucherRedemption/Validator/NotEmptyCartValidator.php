<?php

declare(strict_types=1);

namespace NetInventors\NetiNextEasyCoupon\Service\VoucherRedemption\Validator;

use NetInventors\NetiNextEasyCoupon\Service\VoucherRedemption\ValidationResult;
use NetInventors\NetiNextEasyCoupon\Service\VoucherRedemption\Validator\Error\EmptyCartError;

class NotEmptyCartValidator implements ValidatorInterface
{
    public function validate(
        ValidationContext $validationContext,
        ValidationResult $validationResult
    ): bool {
        $cart = $validationContext->getCart();

        if ($cart->getLineItems()->filterGoods()->count() > 0) {
            return true;
        }

        $validationResult->addErrors(
            new EmptyCartError()
        );

        return false;
    }
}
