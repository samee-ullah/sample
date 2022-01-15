<?php

declare(strict_types=1);

namespace NetInventors\NetiNextEasyCoupon\Core\Checkout\Cart;

use NetInventors\NetiNextEasyCoupon\Core\Checkout\Cart\Error\CartAddedInformationError;
use NetInventors\NetiNextEasyCoupon\Core\Checkout\Cart\Error\CartDeletedInformationError;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;

trait CartInformationTrait
{
    private function addEasyCouponAddedNotice(
        Cart $original,
        Cart $calculated,
        LineItem $discountLineItem
    ): void {
        if ($original->has($discountLineItem->getId())) {
            return;
        }

        $calculated->addErrors(
            new CartAddedInformationError($discountLineItem)
        );
    }

    private function addPromotionDeletedNotice(
        Cart $original,
        Cart $calculated,
        LineItem $discountLineItem
    ): void {
        if (!$original->has($discountLineItem->getId())) {
            return;
        }

        $calculated->addErrors(
            new CartDeletedInformationError($discountLineItem)
        );
    }
}
