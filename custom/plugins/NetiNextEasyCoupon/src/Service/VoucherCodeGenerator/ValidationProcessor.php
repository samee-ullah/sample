<?php

namespace NetInventors\NetiNextEasyCoupon\Service\VoucherCodeGenerator;

use NetInventors\NetiNextEasyCoupon\Service\VoucherCodeGenerator\Validator\ValidatorInterface;

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

    public function validate(string $voucherCode): bool
    {
        foreach ($this->validators as $validator) {
            if (false === $validator->validate($voucherCode)) {
                return false;
            }
        }

        return true;
    }

    public function addValidator(ValidatorInterface $validator): void
    {
        $this->validators[] = $validator;
    }
}
