<?php

declare(strict_types=1);

namespace NetInventors\NetiNextEasyCoupon\Service\VoucherRedemption\Validator;

use NetInventors\NetiNextEasyCoupon\Core\Content\EasyCoupon\EasyCouponEntity;
use NetInventors\NetiNextEasyCoupon\Core\Content\Transaction\TransactionEntity;
use NetInventors\NetiNextEasyCoupon\Service\PluginConfig;
use NetInventors\NetiNextEasyCoupon\Service\VoucherRedemption\ValidationResult;
use NetInventors\NetiNextEasyCoupon\Service\VoucherRedemption\Validator\Error\PaymentActivationStateError;
use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;

class PaymentActivationStateValidator implements ValidatorInterface
{
    /**
     * @var PluginConfig
     */
    private $pluginConfig;

    /**
     * @var EntityRepositoryInterface
     */
    private $transactionRepository;

    /**
     * @var string[]
     */
    private $requiredDALAssociations;

    public function __construct(
        PluginConfig $pluginConfig,
        EntityRepositoryInterface $transactionRepository
    ) {
        $this->pluginConfig            = $pluginConfig;
        $this->transactionRepository   = $transactionRepository;
        $this->requiredDALAssociations = [
            'easyCoupon',
            'order',
            'order.transactions',
        ];
    }

    public function validate(
        ValidationContext $validationContext,
        ValidationResult $validationResult
    ): bool {
        $paymentActivationStates = $this->pluginConfig->getVoucherActivatePaymentStatus();
        $easyCoupon              = $validationContext->getEasyCoupon();
        $salesChannelContext     = $validationContext->getSalesChannelContext();

        $state = $this->notPurchasedOrMatchesPaymentActivationState(
            $paymentActivationStates,
            $easyCoupon,
            $salesChannelContext->getContext()
        );

        if (!$state) {
            $validationResult->addErrors(
                new PaymentActivationStateError($easyCoupon->getCode())
            );

            return false;
        }

        return true;
    }

    public function notPurchasedOrMatchesPaymentActivationState(
        array $paymentActivationStates,
        EasyCouponEntity $easyCoupon,
        Context $context
    ): bool {
        $transactionCriteria = new Criteria();
        $transactionCriteria->addFilter(
            new EqualsFilter('easyCouponId', $easyCoupon->getId()),
            new EqualsFilter('transactionType', TransactionEntity::TYPE_CREATED_BY_PURCHASE)
        );
        $transactionCriteria->addAssociations($this->requiredDALAssociations);
        $transactionCriteria->addSorting(new FieldSorting('createdAt', FieldSorting::ASCENDING));
        $transactionCriteria->setOffset(0);
        $transactionCriteria->setLimit(1);

        $transaction = $this->transactionRepository->search($transactionCriteria, $context)->first();

        if (null === $transaction) {
            return true;
        }

        $order = $transaction instanceof TransactionEntity ? $transaction->getOrder() : null;

        if (null === $order) {
            return true;
        }

        $orderTransactionCollection = $order->getTransactions();

        if (null === $orderTransactionCollection || 0 === $orderTransactionCollection->count()) {
            return true;
        }

        // Sort order transactions by updatedAt field
        // to ensure the latest transaction is the first one in the collection
        $orderTransactionCollection->sort(function (OrderTransactionEntity $a, OrderTransactionEntity $b) {
            $aCreatedAt = $a->getCreatedAt() ?? (new \DateTime())->setTimestamp(0);
            $aUpdatedAt = $a->getUpdatedAt() ?? (new \DateTime())->setTimestamp(0);
            $bCreatedAt = $b->getCreatedAt() ?? (new \DateTime())->setTimestamp(0);
            $bUpdatedAt = $b->getUpdatedAt() ?? (new \DateTime())->setTimestamp(0);

            $dateTimeA = $aUpdatedAt > $aCreatedAt ? $aUpdatedAt : $aCreatedAt;
            $dateTimeB = $bUpdatedAt > $bCreatedAt ? $bUpdatedAt : $bCreatedAt;

            return $dateTimeB <=> $dateTimeA;
        });

        $orderTransaction = $orderTransactionCollection->first();

        if (null === $orderTransaction) {
            return true;
        }

        $stateMachineState = $orderTransaction->getStateMachineState();

        if (null === $stateMachineState) {
            return true;
        }

        foreach ($paymentActivationStates as $paymentActivationState) {
            if ($paymentActivationState === $stateMachineState->getId()) {
                return true;
            }
        }

        return false;
    }
}
