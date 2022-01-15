<?php

namespace NetInventors\NetiNextEasyCoupon\Service\VoucherCodeGenerator\Validator;

interface ValidatorInterface
{
    public function validate(string $voucherCode): bool;
}
