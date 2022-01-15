<?php

namespace AjCustomEasyCoupon\Service;

use AjCustomEasyCoupon\Events\BusinessEvent\CouponActivationEvent;
//use NetInventors\NetiNextEasyCoupon\Events\BusinessEvent\CouponActivationEvent;
use NetInventors\NetiNextEasyCoupon\Core\Content\EasyCoupon\EasyCouponEntity;
use NetInventors\NetiNextEasyCoupon\Core\Content\Transaction\TransactionEntity;
use NetInventors\NetiNextEasyCoupon\Service\Account\VoucherTransactionsService;
use NetInventors\NetiNextEasyCoupon\Service\ConditionService;
use NetInventors\NetiNextEasyCoupon\Service\CurrencyService;
use NetInventors\NetiNextEasyCoupon\Service\OrderVoucherService as OrderVoucherServiceParent;
use NetInventors\NetiNextEasyCoupon\Service\PluginConfig;
use NetInventors\NetiNextEasyCoupon\Service\Repository\VoucherProductRepository;
use NetInventors\NetiNextEasyCoupon\Service\Repository\VoucherRepository;
use NetInventors\NetiNextEasyCoupon\Service\VoucherRedemption\Validator\PaymentActivationStateValidator;
use NetInventors\NetiNextEasyCoupon\Service\VoucherService;
use Psr\Container\ContainerInterface;
use Shopware\Core\Checkout\Cart\SalesChannel\CartService;
use Shopware\Core\Checkout\Order\Aggregate\OrderCustomer\OrderCustomerEntity;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemCollection;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemEntity;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class OrderVoucherService extends OrderVoucherServiceParent
{
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
    private $currencyService;

    public function __construct(
        VoucherService                  $voucherService,
        VoucherProductRepository        $voucherProductRepository,
        EntityRepositoryInterface       $voucherRepository,
        EntityRepositoryInterface       $orderRepository,
        PluginConfig                    $pluginConfig,
        VoucherRepository               $voucherRepositoryService,
        CartService                     $cartService,
        ContainerInterface              $container,
        EntityRepositoryInterface       $transactionRepository,
        PaymentActivationStateValidator $paymentActivationStateValidator,
        VoucherTransactionsService      $voucherTransactionsService,
        ConditionService                $conditionService,
        EntityRepositoryInterface       $orderLineItemRepository,
        CurrencyService                 $currencyService,
        EventDispatcherInterface        $eventDispatcher
    )
    {
        parent::__construct(
            $voucherService,
            $voucherProductRepository,
            $voucherRepository,
            $orderRepository,
            $pluginConfig,
            $voucherRepositoryService,
            $cartService,
            $container,
            $transactionRepository,
            $paymentActivationStateValidator,
            $voucherTransactionsService,
            $conditionService,
            $orderLineItemRepository,
            $currencyService,
            $eventDispatcher
        );
        $this->voucherService = $voucherService;
        $this->voucherProductRepository = $voucherProductRepository;
        $this->voucherRepository = $voucherRepository;
        $this->orderRepository = $orderRepository;
        $this->pluginConfig = $pluginConfig;
        $this->voucherRepositoryService = $voucherRepositoryService;
        $this->cartService = $cartService;
        $this->container = $container;
        $this->transactionRepository = $transactionRepository;
        $this->paymentActivationStateValidator = $paymentActivationStateValidator;
        $this->voucherTransactionsService = $voucherTransactionsService;
        $this->conditionService = $conditionService;
        $this->orderLineItemRepository = $orderLineItemRepository;
        $this->currencyService = $currencyService;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function sendVoucherActivateMailByOrderTransaction(string $orderTransactionId, string $paymentStateId, Context $context): void
    {
        if ($paymentStateId !== 'paid') {
            return;
        }

        $vouchers = $this->voucherRepositoryService->collectOrderedVouchersWithNoMailSending($orderTransactionId, $context);
        if ([] === $vouchers) {
            return;
        }

        [$voucherCodes, $order] = $this->collectVoucherCodes($vouchers);

        $context->addExtension('order', $order);

        $this->eventDispatcher->dispatch(new CouponActivationEvent($context, $order, $vouchers, $voucherCodes), CouponActivationEvent::NAME);
        $this->voucherRepositoryService->activateMailSent($vouchers, $context);
    }

    /**
     * This method adds more information to line items about the created voucher (such as the voucherId ans voucherCode)
     *
     * @param OrderLineItemCollection $lineItems
     * @param array $vouchers
     * @param Context $context
     */
    private function updateLineItemsForCreatedVouchers(
        OrderLineItemCollection $lineItems,
        array                   $vouchers,
        Context                 $context
    ): void
    {
        $updates = [];

        foreach ($vouchers as $voucher) {
            $orderLineItemId = $voucher['transactions'][0]['orderLineItemId'];
            $lineItem = $lineItems->get($orderLineItemId);

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
                'id' => $voucher['id'],
                'code' => $voucher['code'],
            ];

            $lineItem->setPayload($payload);

            $updates[] = [
                'id' => $lineItem->getId(),
                'payload' => $payload,

                // required updates?
                'productId' => $lineItem->getProductId(),
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

    private function getCustomerId(OrderEntity $order): ?string
    {
        $customerId = null;
        if ($order->getOrderCustomer() instanceof OrderCustomerEntity) {
            $customerId = $order->getOrderCustomer()->getCustomerId();
        }

        return $customerId;
    }

    private function getRedeemedVouchersFromOrder(string $orderId, Context $context): array
    {
        $transactions = $this->getRedeemTransactionsFromOrder($orderId, $context);
        $vouchers = [];

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
     * @param string $orderId
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
