<?php

declare(strict_types=1);

namespace NetInventors\NetiNextEasyCoupon\Core\Checkout\Cart;

use NetInventors\NetiNextEasyCoupon\Core\Content\EasyCoupon\EasyCouponEntity;
use NetInventors\NetiNextEasyCoupon\Core\Content\Product\Cart\EasyCouponError;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\Price\Struct\AbsolutePriceDefinition;
use Shopware\Core\Checkout\Cart\Price\Struct\PercentagePriceDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Pricing\PriceCollection;
use Shopware\Core\System\Currency\CurrencyEntity;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class ItemBuilder
{
    public const PLACEHOLDER_PREFIX = 'easy-coupon-';

    public function buildPlaceholderItem(string $code): LineItem
    {
        // void duplicate codes with other items
        // that might not be from the promotion scope
        $uniqueKey = self::PLACEHOLDER_PREFIX . $code;

        $item = new LineItem($uniqueKey, AbstractCartProcessor::EASY_COUPON_LINE_ITEM_TYPE);
        $item->setLabel($uniqueKey);
        $item->setGood(false);

        // this is used to pass on the code for later usage
        $item->setReferencedId($code);

        // this is important to avoid any side effects when calculating the cart
        // a percentage of 0,00 will just do nothing
        $item->setPriceDefinition(new PercentagePriceDefinition(0));

        return $item;
    }

    /**
     * @param EasyCouponEntity    $easyCoupon
     * @param float               $value
     * @param SalesChannelContext $salesChannelContext
     *
     * @return LineItem
     * @throws EasyCouponError
     */
    public function buildDiscountLineItem(
        EasyCouponEntity $easyCoupon,
        float $value,
        SalesChannelContext $salesChannelContext
    ): LineItem {
        $type              = $easyCoupon->getValueType();
        $easyCouponId      = $easyCoupon->getId();

        switch ($type) {
            case EasyCouponEntity::VALUE_TYPE_ABSOLUTE:
                $priceDefinition = new AbsolutePriceDefinition(-abs($value));
                break;

            case EasyCouponEntity::VALUE_TYPE_PERCENTAL:
                $priceDefinition = new PercentagePriceDefinition(-abs($value));
                break;

            default:
                $priceDefinition = null;
        }

        if (null === $priceDefinition) {
            throw new EasyCouponError($easyCoupon->getCode(), 'Unknown value type "' . $type . '"');
        }

        $lineItem = new LineItem($easyCouponId, AbstractCartProcessor::EASY_COUPON_LINE_ITEM_TYPE);
        $lineItem->setLabel($easyCoupon->getTranslation('title'));
        $lineItem->setDescription($easyCoupon->getComment());
        $lineItem->setGood(false);
        $lineItem->setRemovable(true);
        $lineItem->setPriceDefinition($priceDefinition);
        $lineItem->setReferencedId($easyCoupon->getCode());
        $lineItem->setPayload($this->buildPayload($easyCoupon, $value, $salesChannelContext->getCurrency()));
        $lineItem->setPayloadValue('customFields', []);

        return $lineItem;
    }

    private function buildPayload(EasyCouponEntity $easyCoupon, float $value, CurrencyEntity $currency): array
    {
        $payload                  = [];
        $payload['discountId']    = $easyCoupon->getId();
        $payload['discountType']  = $easyCoupon->getValueType();
        $payload['code']          = $easyCoupon->getCode();
        $payload['value']         = (string)$value;
        $payload['productId']     = $easyCoupon->getProductId();
        $payload['productNumber'] = $easyCoupon->getOrderPositionNumber();
        $payload['discountScope'] = 'netiEasyCoupon';

        $currencyId         = $currency->getId();
        $maxRedemptionValue = $easyCoupon->getMaxRedemptionValue() ?? new PriceCollection();
        $maxRedemptionPrice = $maxRedemptionValue->getCurrencyPrice($currencyId, true);

        if (null === $maxRedemptionPrice) {
            return $payload;
        }

        // Currency related max redemption value doesn't exist -> multiply with currency factor
        if ($maxRedemptionPrice->getCurrencyId() !== $currencyId) {
            $maxRedemptionPrice->setCurrencyId($currencyId);

            $maxRedemptionPrice->setGross($maxRedemptionPrice->getGross() * $currency->getFactor());
        }

        $payload['maxValue'] = $maxRedemptionPrice->getGross();

        return $payload;
    }
}
