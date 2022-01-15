<?php

declare(strict_types=1);

namespace NetInventors\NetiNextEasyCoupon\Service\VoucherRedemption;

use Shopware\Core\Checkout\Cart\Error\Error;
use Shopware\Core\Checkout\Cart\Error\ErrorCollection;

class ValidationResult
{
    /**
     * @var ErrorCollection
     */
    private $errors;

    public function __construct()
    {
        $this->errors = new ErrorCollection();
    }

    public function getErrors(): ErrorCollection
    {
        return $this->errors;
    }

    public function setErrors(ErrorCollection $errors): self
    {
        $this->errors = $errors;

        return $this;
    }

    public function addErrors(Error ...$errors): self
    {
        foreach ($errors as $error) {
            $this->errors->add($error);
        }

        return $this;
    }

    public function hasErrors(): bool
    {
        return $this->errors->count() > 0;
    }
}
