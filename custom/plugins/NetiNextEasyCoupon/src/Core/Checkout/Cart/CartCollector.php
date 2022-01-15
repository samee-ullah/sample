<?php

declare(strict_types=1);

namespace NetInventors\NetiNextEasyCoupon\Core\Checkout\Cart;

use NetInventors\NetiNextEasyCoupon\Core\Checkout\Cart\Extension\CartExtension;
use NetInventors\NetiNextEasyCoupon\Core\Checkout\Gateway\EasyCouponGateway;
use NetInventors\NetiNextEasyCoupon\Core\Content\EasyCoupon\EasyCouponEntity;
use NetInventors\NetiNextEasyCoupon\Core\Content\Product\Cart\EasyCouponError;
use NetInventors\NetiNextEasyCoupon\Service\Account\VoucherTransactionsService;
use NetInventors\NetiNextEasyCoupon\Service\PluginConfig;
use NetInventors\NetiNextEasyCoupon\Service\VoucherRedemption\ValidationProcessor;
use NetInventors\NetiNextEasyCoupon\Service\VoucherRedemption\Validator\ValidationContext;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\CartBehavior;
use Shopware\Core\Checkout\Cart\CartDataCollectorInterface;
use Shopware\Core\Checkout\Cart\LineItem\CartDataCollection;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\LineItem\LineItemCollection;
use Shopware\Core\Checkout\Cart\Price\Struct\PercentagePriceDefinition;
use Shopware\Core\Checkout\Promotion\Cart\PromotionCollector;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Session\Session;

// @see \Shopware\Core\Checkout\Promotion\Cart\PromotionCollector
class CartCollector implements CartDataCollectorInterface
{
    public const CACHE_KEY = 'easy-coupon-codes';

    /**
     * @var VoucherTransactionsService
     */
    private $transactionsService;

    /**
     * @var ValidationProcessor
     */
    private $validationProcessor;

    /**
     * @var EasyCouponGateway
     */
    private $gateway;

    /**
     * @var ItemBuilder
     */
    private $itemBuilder;

    private PluginConfig $pluginConfig;

    /**
     * @var Session
     */
    private $session;

    public function __construct(
        VoucherTransactionsService $transactionsService,
        ValidationProcessor $validationProcessor,
        EasyCouponGateway $gateway,
        PluginConfig $pluginConfig,
        ItemBuilder $itemBuilder,
        Session $session
    ) {
        $this->transactionsService = $transactionsService;
        $this->validationProcessor = $validationProcessor;
        $this->gateway             = $gateway;
        $this->pluginConfig        = $pluginConfig;
        $this->itemBuilder         = $itemBuilder;
        $this->session             = $session;
    }

    /**
     * @param CartDataCollection  $data
     * @param Cart                $original
     * @param SalesChannelContext $context
     * @param CartBehavior        $behavior
     *
     * @throws EasyCouponError
     */
    public function collect(
        CartDataCollection $data,
        Cart $original,
        SalesChannelContext $context,
        CartBehavior $behavior
    ): void {
        /** @var CartExtension $cartExtension */
        $cartExtension = $original->getExtension(CartExtension::KEY);

        if (!($cartExtension instanceof CartExtension)) {
            $cartExtension = new CartExtension();
            $original->addExtension(CartExtension::KEY, $cartExtension);
        }

        // Skip in recalculation
        if (
            $behavior->hasPermission(PromotionCollector::SKIP_PROMOTION)
            && 'recalculation' !== $original->getName()
        ) {
            return;
        }

        $extensionCodes     = $cartExtension->getVoucherCodes();
        $cartCodes          = $original->getLineItems()->filterType(
            AbstractCartProcessor::EASY_COUPON_LINE_ITEM_TYPE
        )->getReferenceIds();
        $allCodes           = \array_unique(\array_merge(\array_values($cartCodes), $extensionCodes));
        $cartDataDefinition = $this->searchEasyCouponsByCodes($data, $allCodes, $context);
        $discountLineItems  = [];
        $validationResult   = null;
        $redemptionOrder    = [];

        foreach ($cartDataDefinition->getEasyCoupons() as $easyCoupon) {
            $validationResult = $this->validationProcessor->validate(
                new ValidationContext($easyCoupon, $original, $context)
            );

            if (
                'recalculation' === $original->getName()
                && $behavior->hasPermission(PromotionCollector::SKIP_PROMOTION)
            ) {
                $lineItem = $original->getLineItems()->filter(function(LineItem $lineItem) use ($easyCoupon) {
                    return $lineItem->getReferencedId() === $easyCoupon->getCode();
                })->first();

                if ($lineItem instanceof LineItem) {
                    $discountLineItems[] = $this->itemBuilder->buildDiscountLineItem(
                        $easyCoupon,
                        $lineItem->getPrice()->getUnitPrice(),
                        $context
                    );
                }

                continue;
            }

            if (!$validationResult->hasErrors()) {
                $discountLineItem = $this->itemBuilder->buildDiscountLineItem(
                    $easyCoupon,
                    $this->resolveVoucherValue($easyCoupon, $context),
                    $context
                );

                $redemptionOrder[$discountLineItem->getId()] =
                    EasyCouponEntity::REDEMPTION_ORDER_INHERIT === $easyCoupon->getRedemptionOrder()
                        ? $this->getRedemptionOrderFromConfig() : $easyCoupon->getRedemptionOrder();

                $discountLineItems[] = $discountLineItem;

                continue;
            }

            foreach ($validationResult->getErrors() as $error) {
                $original->addErrors($error);
            }

            /** @var CartExtension $cartExtension */
            $cartExtension = $original->getExtension(CartExtension::KEY);
            $cartExtension->removeVoucherCode($easyCoupon->getCode());

            $sessionVouchers = $this->session->get('EasyCouponVoucherCodes');
            if (isset($sessionVouchers[$easyCoupon->getId()])) {
                unset($sessionVouchers[$easyCoupon->getId()]);
                $this->session->set('EasyCouponVoucherCodes', $sessionVouchers);
            }
        }

        if (0 === \count($discountLineItems)) {
            $data->remove(AbstractCartProcessor::EASY_COUPON_DATA_KEY);

            return;
        }

        // Percentage vouchers should be redeemed before absolute vouchers.
        usort(
            $discountLineItems,
            static function (LineItem $lineItem) {
                if ($lineItem->getPriceDefinition() instanceof PercentagePriceDefinition) {
                    return -1;
                }

                return 1;
            }
        );

        $data->set(AbstractCartProcessor::EASY_COUPON_DATA_KEY, new LineItemCollection($discountLineItems));
        $data->set(AbstractCartProcessor::EASY_COUPON_DATA_KEY_REDEMPTION_ORDER, $redemptionOrder);
    }

    private function resolveVoucherValue(
        EasyCouponEntity $easyCoupon,
        SalesChannelContext $salesChannelContext
    ): float {
        if (EasyCouponEntity::VALUE_TYPE_PERCENTAL === $easyCoupon->getValueType()) {
            return $easyCoupon->getValue();
        }

        $customer = EasyCouponEntity::VOUCHER_TYPE_GENERAL === $easyCoupon->getVoucherType()
            ? $salesChannelContext->getCustomer()
            : null;

        return $this->transactionsService->getTypeBasedVoucherRestValue($easyCoupon, $customer);
    }

    private function searchEasyCouponsByCodes(
        CartDataCollection $data,
        array $allCodes,
        SalesChannelContext $salesChannelContext
    ): CartDataDefinition {
        if (!$data->has(self::CACHE_KEY)) {
            $data->set(self::CACHE_KEY, new CartDataDefinition());
        }

        /** @var CartDataDefinition $cartDataDefinition */
        $cartDataDefinition = $data->get(self::CACHE_KEY);
        $codes              = \array_keys($cartDataDefinition->getEasyCoupons());

        foreach ($codes as $code) {
            if (!\in_array($code, $allCodes, true)) {
                $cartDataDefinition->removeEasyCoupon((string) $code);
            }
        }

        $codesToFetch = [];

        foreach ($allCodes as $code) {
            if ($cartDataDefinition->hasEasyCoupon($code)) {
                continue;
            }

            $codesToFetch[] = $code;
        }

        if (0 === \count($codesToFetch)) {
            $data->set(self::CACHE_KEY, $cartDataDefinition);

            return $cartDataDefinition;
        }

        $criteria = new Criteria();
        $criteria->addFilter(new MultiFilter(
            MultiFilter::CONNECTION_AND,
            [
                new EqualsAnyFilter('code', $codesToFetch),
            ]
        ));

        $criteria->addAssociations(['conditions', 'tax']);

        $entries = $this->gateway->get($criteria, $salesChannelContext);

        /** @var EasyCouponEntity $entry */
        foreach ($entries as $entry) {
            $cartDataDefinition->addEasyCoupon($entry->getCode(), $entry);
        }

        $data->set(self::CACHE_KEY, $cartDataDefinition);

        return $cartDataDefinition;
    }

    private function getRedemptionOrderFromConfig(): int
    {
        if (PluginConfig::CART_PRIORITY_BEFORE === $this->pluginConfig->getCartProcessorPriority()) {
            return EasyCouponEntity::REDEMPTION_ORDER_BEFORE;
        }

        return EasyCouponEntity::REDEMPTION_ORDER_AFTER;
    }
}
