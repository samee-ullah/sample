<?php

/**
 * @copyright Copyright (c) 2020, Net Inventors GmbH
 * @category  Shopware
 * @author    mpeters
 */

declare(strict_types=1);

namespace NetInventors\NetiNextEasyCoupon\Service;

use NetInventors\NetiNextEasyCoupon\Core\Checkout\Cart\AbstractCartProcessor;
use NetInventors\NetiNextEasyCoupon\Core\Content\EasyCoupon\EasyCouponEntity;
use NetInventors\NetiNextEasyCoupon\Core\Content\Product\Aggregate\EasyCouponProductEntity;
use NetInventors\NetiNextEasyCoupon\Core\Content\Product\Aggregate\EasyCouponProductTranslation\EasyCouponProductTranslationCollection;
use NetInventors\NetiNextEasyCoupon\Core\Content\Transaction\TransactionEntity;
use NetInventors\NetiNextEasyCoupon\Events\BusinessEvent\CouponActivationEvent;
use NetInventors\NetiNextEasyCoupon\Service\Account\VoucherTransactionsService;
use NetInventors\NetiNextEasyCoupon\Service\Repository\VoucherProductRepository;
use NetInventors\NetiNextEasyCoupon\Service\Repository\VoucherRepository;
use NetInventors\NetiNextEasyCoupon\Service\VoucherRedemption\Validator\PaymentActivationStateValidator;
use NetInventors\NetiNextEasyCoupon\Struct\LineItemStruct;
use NetInventors\NetiNextEasyCoupon\Struct\VoucherCodeGeneratorConfig;
use NetInventors\NetiNextEasyCoupon\Struct\VoucherCollection;
use Psr\Container\ContainerInterface;
use Shopware\Core\Checkout\Cart\LineItem\LineItemCollection;
use Shopware\Core\Checkout\Cart\SalesChannel\CartService;
use Shopware\Core\Checkout\Order\Aggregate\OrderCustomer\OrderCustomerEntity;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemCollection;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemEntity;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Page\Checkout\Finish\CheckoutFinishPage;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class OrderVoucherService
{
    /**
     * @var ?EntitySearchResult
     */
    protected $orderedPurchaseVouchers;

    /**
     * @var ?OrderEntity
     */
    protected $orderEntity;

    /**
     * @var VoucherService
     */
    protected $voucherService;

    /**
     * @var VoucherProductRepository
     */
    protected $voucherProductRepository;

    /**
     * @var EntityRepositoryInterface
     */
    protected $voucherRepository;

    /**
     * @var EntityRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var PluginConfig
     */
    protected $pluginConfig;

    /**
     * @var VoucherRepository
     */
    protected $voucherRepositoryService;

    /**
     * @var CartService
     */
    protected $cartService;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var EntityRepositoryInterface
     */
    private $transactionRepository;

    /**
     * @var PaymentActivationStateValidator
     */
    private $paymentActivationStateValidator;

    /**
     * @var VoucherTransactionsService
     */
    private $voucherTransactionsService;

    /**
     * @var ConditionService
     */
    private $conditionService;

    /**
     * @var EntityRepositoryInterface
     */
    private $orderLineItemRepository;

    /**
     * @var CurrencyService
     */
    private                            $currencyService;

    protected EventDispatcherInterface $eventDispatcher;

    public function __construct(
        VoucherService $voucherService,
        VoucherProductRepository $voucherProductRepository,
        EntityRepositoryInterface $voucherRepository,
        EntityRepositoryInterface $orderRepository,
        PluginConfig $pluginConfig,
        VoucherRepository $voucherRepositoryService,
        CartService $cartService,
        ContainerInterface $container,
        EntityRepositoryInterface $transactionRepository,
        PaymentActivationStateValidator $paymentActivationStateValidator,
        VoucherTransactionsService $voucherTransactionsService,
        ConditionService $conditionService,
        EntityRepositoryInterface $orderLineItemRepository,
        CurrencyService $currencyService,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->voucherService                  = $voucherService;
        $this->voucherProductRepository        = $voucherProductRepository;
        $this->voucherRepository               = $voucherRepository;
        $this->orderRepository                 = $orderRepository;
        $this->pluginConfig                    = $pluginConfig;
        $this->voucherRepositoryService        = $voucherRepositoryService;
        $this->cartService                     = $cartService;
        $this->container                       = $container;
        $this->transactionRepository           = $transactionRepository;
        $this->paymentActivationStateValidator = $paymentActivationStateValidator;
        $this->voucherTransactionsService      = $voucherTransactionsService;
        $this->conditionService                = $conditionService;
        $this->orderLineItemRepository         = $orderLineItemRepository;
        $this->currencyService                 = $currencyService;
        $this->eventDispatcher                 = $eventDispatcher;
    }

    public function createVoucherByLineItems(OrderLineItemCollection $lineItems, Context $context): void
    {
        $filteredLineItems = $this->filterLineItemsByEnteredValued($lineItems);
        if ([] === $filteredLineItems->getElements()) {
            return;
        }

        $productAttributes = $this->voucherProductRepository->getProductAttributeWithTranslations(
            $this->collectProductIdsFromOrderLineItems($filteredLineItems),
            $context
        );
        $voucherData       = $this->collectCouponData($filteredLineItems, $productAttributes, $context);

        $this->voucherRepository->create($voucherData, $context);
        $this->updateLineItemsForCreatedVouchers($filteredLineItems, $voucherData, $context);
    }

    /**
     * This method adds more information to line items about the created voucher (such as the voucherId ans voucherCode)
     *
     * @param OrderLineItemCollection $lineItems
     * @param array                   $vouchers
     * @param Context                 $context
     */
    private function updateLineItemsForCreatedVouchers(
        OrderLineItemCollection $lineItems,
        array                   $vouchers,
        Context                 $context
    ): void {
        $updates = [];

        foreach ($vouchers as $voucher) {
            $orderLineItemId = $voucher['transactions'][0]['orderLineItemId'];
            $lineItem        = $lineItems->get($orderLineItemId);

            if (!$lineItem instanceof OrderLineItemEntity) {
                continue;
            }

            $payload = $lineItem->getPayload();

            if (
                !isset($payload['netiNextEasyCoupon']['vouchers'])
                || false === is_array($payload['netiNextEasyCoupon']['vouchers'])
            ) {
                $payload['netiNextEasyCoupon']['vouchers'] = [];
            }

            $payload['netiNextEasyCoupon']['vouchers'][] = [
                'id'   => $voucher['id'],
                'code' => $voucher['code'],
            ];

            $lineItem->setPayload($payload);

            $updates[] = [
                'id'           => $lineItem->getId(),
                'payload'      => $payload,

                // required updates?
                'productId'    => $lineItem->getProductId(),
                'referencedId' => $lineItem->getReferencedId(),
            ];
        }

        if ([] !== $updates) {
            $this->orderLineItemRepository->update(
                $updates,
                $context
            );
        }
    }

    public function sendVoucherActivateMailByOrderTransaction(string $orderTransactionId, string $paymentStateId, Context $context): void
    {
        if (!\in_array($paymentStateId, $this->pluginConfig->getVoucherActivatePaymentStatus(), true)) {
            return;
        }

        $vouchers = $this->voucherRepositoryService->collectOrderedVouchersWithNoMailSending($orderTransactionId, $context);
        if ([] === $vouchers) {
            return;
        }

        [ $voucherCodes, $order ] = $this->collectVoucherCodes($vouchers);

        $this->eventDispatcher->dispatch(new CouponActivationEvent($context, $order, $vouchers, $voucherCodes), CouponActivationEvent::NAME);
        $this->voucherRepositoryService->activateMailSent($vouchers, $context);
    }

    public function removePurchaseVoucherFromCart(SalesChannelContext $context): void
    {
        $cart       = $this->cartService->getCart($context->getToken(), $context);
        $items      = $cart->getLineItems();
        $attributes = $this->voucherProductRepository->getProductAttribute(
            $this->collectVoucherProductIdsFromLineItems($items),
            $context->getContext()
        );

        foreach ($attributes as $attribute) {
            $valueType = $attribute->getValueType();
            if (
                EasyCouponProductEntity::VALUE_TYPE_RANGE === $valueType
                || EasyCouponProductEntity::VALUE_TYPE_SELECTION === $valueType
            ) {
                foreach ($items as $item) {
                    if ($item->getReferencedId() === $attribute->getProductId()) {
                        $cart->remove($item->getId());
                        $this->addFlash(
                            'info',
                            $this->trans(
                                'neti-easy-coupon.messages.purchase-voucher.currency-change',
                                [
                                    'name' => $item->getLabel(),
                                ]
                            )
                        );
                    }
                }
            }
        }

        $this->cartService->recalculate($cart, $context);
    }

    public function createTransactionsForRedeemedVouchers(OrderEntity $order, Context $context): void
    {
        if (!$order->getLineItems() instanceof OrderLineItemCollection) {
            return;
        }

        $voucherLineItems = $order->getLineItems()->filterByType(AbstractCartProcessor::EASY_COUPON_LINE_ITEM_TYPE);
        if ([] === $voucherLineItems->getElements()) {
            return;
        }

        $transactions         = [];
        $orderLineItemUpdates = [];

        foreach ($voucherLineItems as $item) {
            $customerId    = $this->getCustomerId($order);
            $transactionId = Uuid::randomHex();

            $transactions[] = [
                'id'              => $transactionId,
                'value'           => $item->getTotalPrice() / $order->getCurrencyFactor(),
                'currencyFactor'  => $order->getCurrencyFactor(),
                'transactionType' => TransactionEntity::TYPE_REDEMPTION_IN_STOREFRONT,
                'currencyId'      => $order->getCurrencyId(),
                'easyCouponId'    => $item->getIdentifier(),
                'customerId'      => $customerId,
                'orderId'         => $order->getId(),
                'salesChannelId'  => $order->getSalesChannelId(),
                'orderLineItemId' => $item->getId(),
            ];

            $payload                            = $item->getPayload();
            $payload['easyCouponTransactionId'] = $transactionId;

            $orderLineItemUpdates[] = [
                'id'      => $item->getId(),
                'payload' => $payload,
            ];
        }

        $this->transactionRepository->create($transactions, $context);
        $this->orderLineItemRepository->update($orderLineItemUpdates, $context);
    }

    public function updateTransactionValueFromOrderLineItem(string $orderLineItemId, Context $context): void
    {
        $criteria = new Criteria([ $orderLineItemId ]);
        $criteria->addFilter(new EqualsFilter('versionId', Defaults::LIVE_VERSION));
        $criteria->addAssociation('order');

        $result        = $this->orderLineItemRepository->search($criteria, $context);
        $orderLineItem = $result->first();

        if (!$orderLineItem instanceof OrderLineItemEntity) {
            return; // for whatever reason the passed orderLineItemId was invalid
        }

        $payload       = $orderLineItem->getPayload() ?? [];
        $transactionId = $payload['easyCouponTransactionId'] ?? null;
        $order         = $orderLineItem->getOrder();

        if (null === $transactionId || null === $order) {
            return; // no transaction id available yet
        }

        $defaultCurrency       = $this->currencyService->getDefaultCurrency($context);
        $defaultCurrencyFactor = $defaultCurrency->getFactor();

        $this->transactionRepository->update(
            [
                [
                    'id'    => $transactionId,
                    'value' => $orderLineItem->getTotalPrice() / $order->getCurrencyFactor() * $defaultCurrencyFactor
                ]
            ],
            $context
        );
    }

    public function addPurchaseVouchersToCheckoutConfirmPage(CheckoutFinishPage $page, Context $context): void
    {
        $purchaseVouchers = $this->getOrderedPurchaseVouchers($page->getOrder()->getId(), $context);
        if (0 === $purchaseVouchers->count()) {
            return;
        }

        $purchaseVouchers = $purchaseVouchers->getElements();
        if ($this->pluginConfig->isShowCodeAfterPayment()) {
            /** @var EasyCouponEntity $purchasedVoucher */
            foreach ($purchaseVouchers as $purchasedVoucher) {
                $notPurchasedOrMatchesPaymentActivationState =
                    $this->paymentActivationStateValidator->notPurchasedOrMatchesPaymentActivationState(
                        $this->pluginConfig->getVoucherActivatePaymentStatus(),
                        $purchasedVoucher,
                        $context
                    );

                $purchasedVoucher->setActive($notPurchasedOrMatchesPaymentActivationState);
            }
        }

        $struct = new VoucherCollection($purchaseVouchers);
        $page->addExtension('netiEasyCouponPurchaseVouchers', $struct);
    }

    public function addPurchaseVouchersToOrder(OrderEntity $order, Context $context): void
    {
        $purchaseVouchers = $this->getOrderedPurchaseVouchers($order->getId(), $context);
        if (0 === $purchaseVouchers->count()) {
            return;
        }

        $struct = new VoucherCollection($purchaseVouchers->getElements());
        $order->addExtension('netiEasyCouponPurchaseVouchers', $struct);
    }

    protected function getOrderedPurchaseVouchers(string $orderId, Context $context): EntitySearchResult
    {
        if (!$this->orderedPurchaseVouchers instanceof EntitySearchResult) {
            $this->orderedPurchaseVouchers = $this->voucherRepositoryService->getPurchaseVoucherOfOrder($orderId, $context);
        }

        return $this->orderedPurchaseVouchers;
    }

    protected function addFlash(string $type, string $message): void
    {
        $this->container->get('session')->getFlashBag()->add($type, $message);
    }

    protected function trans(string $snippet, array $parameters = []): string
    {
        return $this->container
            ->get('translator')
            ->trans($snippet, $parameters);
    }

    /**
     * @param LineItemCollection $lineItems
     *
     * @return string[]
     */
    protected function collectVoucherProductIdsFromLineItems(LineItemCollection $lineItems): array
    {
        $productIds = [];
        foreach ($lineItems as $item) {
            $voucherPayload = $item->getPayloadValue(LineItemStruct::PAYLOAD_NAME);
            if (!\is_array($voucherPayload) || !\is_float($voucherPayload['voucherValue'])) {
                continue;
            }

            $productIds[] = $item->getReferencedId();
        }

        return $productIds;
    }

    /**
     * @param EasyCouponEntity[] $vouchers
     *
     * @return array
     */
    protected function collectVoucherCodes(array $vouchers): array
    {
        $collection = new VoucherCollection();
        $order      = null;
        foreach ($vouchers as $voucher) {
            $collection->add($voucher->getCode());

            if (null === $order) {
                $order = $voucher->getTransactions()->first()->getOrder();
            }
        }

        return [$collection, $order];
    }

    /**
     * @param OrderLineItemCollection   $lineItems
     * @param EasyCouponProductEntity[] $productAttributes
     * @param Context                   $context
     *
     * @return array
     */
    protected function collectCouponData(OrderLineItemCollection $lineItems, array $productAttributes, Context $context): array
    {
        $voucherConfig = (new VoucherCodeGeneratorConfig())->setNumOfVoucherCodes(1);
        $voucherData   = [];

        $voucherConfig->setPattern($this->pluginConfig->getDefaultCodePattern());

        foreach ($lineItems as $item) {
            $order      = $this->getOrder($item->getOrderId(), $context);
            $customerId = $this->getCustomerId($order);

            foreach ($productAttributes as $attribute) {
                if ($item->getProductId() === $attribute->getProductId()) {
                    $payload      = $item->getPayload();
                    $voucherValue = $this->calculateVoucherValue(
                        (float) $payload[LineItemStruct::PAYLOAD_NAME]['voucherValue'],
                        $context
                    );

                    $productVersionId = null;
                    if ($attribute->getProduct() instanceof ProductEntity) {
                        $productVersionId = $attribute->getProduct()->getVersionId();
                    }

                    for ($i = 0; $i < $item->getQuantity(); ++$i) {
                        $couponId      = Uuid::randomHex();
                        $voucherData[] = [
                            'id'                       => $couponId,
                            'active'                   => true,
                            'voucherType'              => EasyCouponEntity::VOUCHER_TYPE_INDIVIDUAL,
                            'code'                     => $this->voucherService->generateVoucherCodes($voucherConfig)->first(),
                            'value'                    => $voucherValue,
                            'discardRemaining'         => false,
                            'shippingCharge'           => $attribute->isShippingCharge(),
                            'excludeFromShippingCosts' => $attribute->isExcludeFromShippingCosts(),
                            'noDeliveryCharge'         => $attribute->isNoDeliveryCharge(),
                            'customerGroupCharge'      => $attribute->isCustomerGroupCharge(),
                            'mailSent'                 => false,
                            'comment'                  => $attribute->getComment(),
                            'currencyFactor'           => $context->getCurrencyFactor(),
                            'orderPositionNumber'      => $attribute->getOrderPositionNumber(),
                            'combineVouchers'          => $attribute->isCombineVouchers(),
                            'customerId'               => $customerId,
                            'tagId'                    => null,
                            'valueType'                => EasyCouponEntity::VALUE_TYPE_ABSOLUTE,
                            'ruleId'                   => null,
                            'currencyId'               => $context->getCurrencyId(),
                            'productId'                => $attribute->getProductId(),
                            'productVersionId'         => $productVersionId,
                            'taxId'                    => $attribute->getTaxId(),
                            'validUntil'               => $this->calculateValidUntil($attribute),
                            'translations'             => $this->collectTranslations($attribute->getTranslations()),
                            'transactions'             => [
                                [
                                    'value'           => $voucherValue,
                                    'currencyFactor'  => $context->getCurrencyFactor(),
                                    'currencyId'      => $context->getCurrencyId(),
                                    'customerId'      => $customerId,
                                    'easyCouponId'    => $couponId,
                                    'orderId'         => $item->getOrderId(),
                                    'orderVersionId'  => $order->getVersionId(),
                                    'salesChannelId'  => $order->getSalesChannelId(),
                                    'transactionType' => TransactionEntity::TYPE_CREATED_BY_PURCHASE,
                                    'orderLineItemId' => $item->getId()
                                ],
                            ],
                            'conditions'               => $this->conditionService->mapConditions(
                                $attribute->getConditions()
                            ),
                        ];
                    }

                    continue 2;
                }
            }
        }

        return $voucherData;
    }

    protected function calculateValidUntil (EasyCouponProductEntity $product): ?\DateTimeInterface
    {
        if ($product->getValidityTime() > 0) {
            $toDate = new \DateTime();

            if ($product->isValidityByEndOfYear()) {
                $toDate = new \DateTime();
                $toDate->setDate(
                    (int)$toDate->format('Y') + 1,
                    1,
                    1
                );

            }

            $toDate->setTime(0, 0);
            $toDate->modify(sprintf('+%d days', $product->getValidityTime()));

            return $toDate;
        }

        return null;
    }

    protected function calculateVoucherValue(float $voucherValue, Context $context): float
    {
        return $voucherValue / $context->getCurrencyFactor();
    }

    protected function getOrder(string $orderId, Context $context): OrderEntity
    {
        if (!($this->orderEntity instanceof OrderEntity && $this->orderEntity->getId() === $orderId)) {
            $criteria = new Criteria([$orderId]);
            $criteria->addAssociations(
                [
                    'orderCustomer',
                    'orderCustomer.customer',
                    'orderCustomer.customer.group',
                ]
            );

            $this->orderEntity = $this->orderRepository->search($criteria, $context)->first();
        }

        return $this->orderEntity;
    }

    protected function collectTranslations(?EasyCouponProductTranslationCollection $collection): array
    {
        if (null === $collection) {
            return [];
        }

        $collectKeys = [
            'netiEasyCouponProductId',
            'title',
            'languageId',
        ];
        $elements    = $collection->getElements();
        $languages   = [];
        foreach ($elements as $elementObject) {
            $elementArray = $elementObject->getVars();
            $language     = [];
            foreach ($collectKeys as $key) {
                $language[$key] = $elementArray[$key];
            }
            $languages[] = $language;
        }

        return $languages;
    }

    protected function filterLineItemsByEnteredValued(OrderLineItemCollection $lineItems): OrderLineItemCollection
    {
        $lineItems = clone $lineItems;
        foreach ($lineItems as $key => $item) {
            $payload = $item->getPayload();
            if (!isset($payload['netiNextEasyCoupon'])) {
                $lineItems->remove($key);
            }
        }

        return $lineItems;
    }

    /**
     * @param OrderLineItemCollection $lineItems
     *
     * @return string[]
     */
    protected function collectProductIdsFromOrderLineItems(OrderLineItemCollection $lineItems): array
    {
        $productIds = [];
        foreach ($lineItems as $item) {
            $productIds[] = $item->getProductId();
        }

        return $productIds;
    }

    private function getCustomerId(OrderEntity $order): ?string
    {
        $customerId = null;
        if ($order->getOrderCustomer() instanceof OrderCustomerEntity) {
            $customerId = $order->getOrderCustomer()->getCustomerId();
        }

        return $customerId;
    }

    public function displayVoucherRestValueAtCheckoutConfirmPage(CheckoutFinishPage $page, Context $context): void
    {
        $orderId  = $page->getOrder()->getId();
        $vouchers = $this->getRedeemedVouchersFromOrder($orderId, $context);

        if (empty($vouchers)) {
            return;
        }

        $cachedValues = $this->voucherTransactionsService->getRestValueForVouchers($vouchers);

        /** @var EasyCouponEntity $voucher */
        foreach ($vouchers as $voucher) {
            $voucher->setValue($cachedValues[$voucher->getId()]);
        }

        $struct = new VoucherCollection($vouchers);
        $page->addExtension('EasyCouponVoucherRestValues', $struct);
    }

    private function getRedeemedVouchersFromOrder(string $orderId, Context $context): array
    {
        $transactions = $this->getRedeemTransactionsFromOrder($orderId, $context);
        $vouchers     = [];

        if (0 >= $transactions->getTotal()) {
            return $vouchers;
        }

        /** @var TransactionEntity $transaction */
        foreach ($transactions->getElements() as $transaction) {
            $vouchers[$transaction->getEasyCouponId()] = $transaction->getEasyCoupon();
        }

        return $vouchers;
    }

    /**
     * @param string  $orderId
     * @param Context $context
     *
     * @return EntitySearchResult
     */
    private function getRedeemTransactionsFromOrder(string $orderId, Context $context): EntitySearchResult
    {
        $criteria = new Criteria();
        $criteria->addAssociation('easyCoupon')
            ->addFilter(
                new MultiFilter(
                    MultiFilter::CONNECTION_AND, [
                        new EqualsFilter('orderId', $orderId),
                        new EqualsFilter('transactionType', TransactionEntity::TYPE_REDEMPTION_IN_STOREFRONT),
                        new EqualsFilter('easyCoupon.valueType', EasyCouponEntity::VALUE_TYPE_ABSOLUTE),
                    ]
                )
            );

        return $this->transactionRepository->search($criteria, $context);
    }
}
