<?php

namespace NetInventors\NetiNextEasyCoupon\Service\VoucherRedemption;

use NetInventors\NetiNextEasyCoupon\Service\VoucherRedemption\Validator\ValidationContext;
use NetInventors\NetiNextEasyCoupon\Service\VoucherRedemption\Validator\ValidatorInterface;

class ValidationProcessor
{
    /**
     * @var ValidatorInterface[]
     */
    private $validators;

    public function __construct()
    {
        $this->validators = [];
    }

    public function validate(ValidationContext $validationContext): ValidationResult
    {
        $validationResult = new ValidationResult();

        foreach ($this->validators as $validator) {
            if (false === $validator->validate($validationContext, $validationResult)) {
                // TODO Evaluate if it is better to exit after the first failed validation or run all validators
                return $validationResult;
            }
        }

        return $validationResult;
    }

    public function addValidator(ValidatorInterface $validator): void
    {
        $this->validators[] = $validator;
    }
}
