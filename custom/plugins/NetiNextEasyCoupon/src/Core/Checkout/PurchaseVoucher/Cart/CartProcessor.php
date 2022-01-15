<?php

/**
 * @copyright Copyright (c) 2020, Net Inventors GmbH
 * @category  Shopware
 * @author    mpeters
 */

declare(strict_types=1);

namespace NetInventors\NetiNextEasyCoupon\Core\Checkout\PurchaseVoucher\Cart;

use NetInventors\NetiNextEasyCoupon\Core\Checkout\Cart\Error\ProductPriceError;
use NetInventors\NetiNextEasyCoupon\Core\Checkout\Cart\Error\PurchaseVoucherWithoutValueError;
use NetInventors\NetiNextEasyCoupon\Core\Content\Product\Aggregate\EasyCouponProductEntity;
use NetInventors\NetiNextEasyCoupon\Core\Framework\Pricing\ProductValuePrice;
use NetInventors\NetiNextEasyCoupon\Core\Framework\Pricing\ProductValuePriceCollection;
use NetInventors\NetiNextEasyCoupon\Service\LineItemExtension\LineItemExtensionBuilder;
use NetInventors\NetiNextEasyCoupon\Service\PluginConfig;
use NetInventors\NetiNextEasyCoupon\Struct\LineItemStruct;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\CartBehavior;
use Shopware\Core\Checkout\Cart\CartProcessorInterface;
use Shopware\Core\Checkout\Cart\LineItem\CartDataCollection;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\Price\CashRounding;
use Shopware\Core\Checkout\Cart\Price\QuantityPriceCalculator;
use Shopware\Core\Checkout\Cart\Price\Struct\QuantityPriceDefinition;
use Shopware\Core\System\Currency\CurrencyEntity;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\RequestStack;

class CartProcessor implements CartProcessorInterface
{
    /**
     * @var PluginConfig
     */
    protected $pluginConfig;

    /**
     * @var RequestStack
     */
    protected $requestStack;

    /**
     * @var QuantityPriceCalculator
     */
    protected $calculator;

    /**
     * @var CashRounding
     */
    protected $priceRounding;

    /**
     * @var LineItemExtensionBuilder
     */
    private $lineItemExtensionBuilder;

    public function __construct(
        PluginConfig $pluginConfig,
        RequestStack $requestStack,
        QuantityPriceCalculator $calculator,
        CashRounding $priceRounding,
        LineItemExtensionBuilder $lineItemExtensionBuilder
    ) {
        $this->pluginConfig             = $pluginConfig;
        $this->requestStack             = $requestStack;
        $this->calculator               = $calculator;
        $this->priceRounding            = $priceRounding;
        $this->lineItemExtensionBuilder = $lineItemExtensionBuilder;
    }

    public function process(
        CartDataCollection $data,
        Cart $original,
        Cart $toCalculate,
        SalesChannelContext $context,
        CartBehavior $behavior
    ): void {
        if (!$this->pluginConfig->isActive()) {
            return;
        }

        $lineItems = $toCalculate->getLineItems()->filterType(LineItem::PRODUCT_LINE_ITEM_TYPE);
        if ([] === $lineItems->getElements()) {
            return;
        }

        foreach ($lineItems as $lineItem) {
            $key = $this->buildKey($lineItem->getReferencedId());
            if (!$data->has($key) || !$data->get($key) instanceof EasyCouponProductEntity) {
                continue;
            }

            /** @var EasyCouponProductEntity $attribute */
            $attribute = $data->get($key);
            if (!$attribute->getValue() instanceof ProductValuePriceCollection) {
                return;
            }

            /** @var ProductValuePrice $priceObject */
            $priceObject = $attribute->getValue()->getCurrencyPrice(
                $context->getCurrency()->getId(),
                true,
                $context->getContext()
            );

            $valueType    = $attribute->getValueType();
            $payloadValue = $lineItem->getPayloadValue(LineItemStruct::PAYLOAD_NAME);

            if (
                (
                    EasyCouponProductEntity::VALUE_TYPE_RANGE === $valueType
                    || EasyCouponProductEntity::VALUE_TYPE_SELECTION === $valueType
                ) && (
                    !\is_array($payloadValue)
                    || !isset($payloadValue['voucherValue'])
                )
            ) {
                $toCalculate->remove($lineItem->getId());
                $toCalculate->addErrors(new PurchaseVoucherWithoutValueError($lineItem));

                continue;
            }

            if (EasyCouponProductEntity::VALUE_TYPE_FIXED === $valueType) {
                $this->lineItemExtensionBuilder->addExtension($lineItem, [
                    'voucherValue' => $priceObject->getGross(),
                ]);
            }

            if (!(
                EasyCouponProductEntity::VALUE_TYPE_SELECTION === $valueType
                || EasyCouponProductEntity::VALUE_TYPE_RANGE === $valueType
            )) {
                continue;
            }

            $price = $payloadValue['voucherValue'];
            if (
                (
                    EasyCouponProductEntity::VALUE_TYPE_SELECTION === $valueType
                    && !$this->isPriceSelectable($price, $priceObject->getSelectableValues(), $context->getCurrency())
                ) || (
                    EasyCouponProductEntity::VALUE_TYPE_RANGE === $valueType
                    && (
                        $priceObject->getFrom() > $price
                        || $priceObject->getTo() < $price
                    )
                )
            ) {
                $toCalculate->addErrors(new ProductPriceError($lineItem));
            }

            $definition = new QuantityPriceDefinition(
                $price,
                $lineItem->getPrice()->getTaxRules(),
                $lineItem->getPrice()->getQuantity()
            );

            $calculated = $this->calculator->calculate($definition, $context);

            $lineItem->setPrice($calculated);
            $lineItem->setPriceDefinition($definition);
        }
    }

    /**
     * @param float          $price
     * @param float[]        $selectableValues
     * @param CurrencyEntity $currency
     *
     * @return bool
     */
    protected function isPriceSelectable(float $price, array $selectableValues, CurrencyEntity $currency): bool
    {
        $price = $this->priceRounding->mathRound($price, $currency->getItemRounding());
        foreach ($selectableValues as $value) {
            if ($this->priceRounding->mathRound($value, $currency->getItemRounding()) === $price) {
                return true;
            }
        }

        return false;
    }

    private function buildKey(string $id): string
    {
        return CartCollector::PRODUCT_ATTRIBUTE_KEY . $id;
    }
}
