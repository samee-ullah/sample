<?php

declare(strict_types=1);

namespace NetInventors\NetiNextEasyCoupon\Core\Checkout;

use Shopware\Core\Framework\ShopwareHttpException;
use Symfony\Component\HttpFoundation\Response;

class DiscountCalculatorNotFoundException extends ShopwareHttpException
{
    public function __construct(string $type)
    {
        parent::__construct(
            'Discount Calculator "{{ type }}" has not been found!',
            [ 'type' => $type ]
        );
    }

    public function getErrorCode(): string
    {
        return 'CHECKOUT__DISCOUNT_CALCULATOR_NOT_FOUND';
    }

    public function getStatusCode(): int
    {
        return Response::HTTP_BAD_REQUEST;
    }
}
