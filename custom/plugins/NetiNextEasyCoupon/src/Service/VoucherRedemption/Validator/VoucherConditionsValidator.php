<?php

declare(strict_types=1);

namespace NetInventors\NetiNextEasyCoupon\Service\VoucherRedemption\Validator;

use NetInventors\NetiNextEasyCoupon\Service\ConditionService;
use NetInventors\NetiNextEasyCoupon\Service\VoucherRedemption\ValidationResult;
use NetInventors\NetiNextEasyCoupon\Service\VoucherRedemption\Validator\Error\ConditionsError;

class VoucherConditionsValidator implements ValidatorInterface
{
    /**
     * @var ConditionService
     */
    private $conditionService;

    public function __construct(ConditionService $conditionService)
    {
        $this->conditionService = $conditionService;
    }

    public function validate(
        ValidationContext $validationContext,
        ValidationResult $validationResult
    ): bool {
        $easyCoupon          = $validationContext->getEasyCoupon();
        $cart                = $validationContext->getCart();
        $salesChannelContext = $validationContext->getSalesChannelContext();

        $conditionsValid = $this->conditionService->validateConditions(
            $easyCoupon,
            $cart,
            $salesChannelContext
        );

        if (!$conditionsValid) {
            $validationResult->addErrors(
                new ConditionsError($easyCoupon->getCode())
            );
        }

        return $conditionsValid;
    }
}
