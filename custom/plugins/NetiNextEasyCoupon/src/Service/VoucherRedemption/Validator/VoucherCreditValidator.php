<?php

declare(strict_types=1);

namespace NetInventors\NetiNextEasyCoupon\Service\VoucherRedemption\Validator;

use NetInventors\NetiNextEasyCoupon\Core\Content\EasyCoupon\EasyCouponEntity;
use NetInventors\NetiNextEasyCoupon\Core\Content\Transaction\TransactionEntity;
use NetInventors\NetiNextEasyCoupon\Service\Account\VoucherTransactionsService;
use NetInventors\NetiNextEasyCoupon\Service\VoucherRedemption\ValidationResult;
use NetInventors\NetiNextEasyCoupon\Service\VoucherRedemption\Validator\Error\NoMoreCreditError;
use Shopware\Core\Checkout\Cart\Price\CashRounding;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Util\FloatComparator;

class VoucherCreditValidator implements ValidatorInterface
{
    /**
     * @var VoucherTransactionsService
     */
    private $transactionsService;

    /**
     * @var CashRounding
     */
    private $priceRounding;

    public function __construct(
        VoucherTransactionsService $transactionsService,
        CashRounding $priceRounding
    ) {
        $this->transactionsService = $transactionsService;
        $this->priceRounding       = $priceRounding;
    }

    public function validate(
        ValidationContext $validationContext,
        ValidationResult $validationResult
    ): bool {
        $easyCoupon          = $validationContext->getEasyCoupon();
        $salesChannelContext = $validationContext->getSalesChannelContext();
        $context             = Context::createDefaultContext();
        $rounding            = $context->getRounding();

        if (EasyCouponEntity::VALUE_TYPE_PERCENTAL === $easyCoupon->getValueType()) {
            return true;
        }

        $customer = EasyCouponEntity::VOUCHER_TYPE_GENERAL === $easyCoupon->getVoucherType()
            ? $salesChannelContext->getCustomer()
            : null;

        $transactions = $this->transactionsService->getTypeBasedVoucherTransactions($easyCoupon, $customer);
        $creditValue  = $this->transactionsService->buildSumFromTransactions($transactions);
        $creditValue  = $this->priceRounding->mathRound($creditValue ?? 0.0, $rounding);

        $hasCredit = FloatComparator::greaterThan($creditValue, $this->priceRounding->mathRound(0.0, $rounding));

        if ($hasCredit && !$easyCoupon->isDiscardRemaining()) {
            return true;
        }

        /** @var TransactionEntity $initTransaction */
        $initTransaction = $transactions->filter(function (TransactionEntity $transaction) {
            return TransactionEntity::TYPE_CREATED_BY_ADMIN === $transaction->getTransactionType()
                || TransactionEntity::TYPE_CREATED_BY_PURCHASE === $transaction->getTransactionType();
        })->first();

        if (
            $easyCoupon->isDiscardRemaining()
            && FloatComparator::equals($initTransaction->getValue(), $creditValue)
        ) {
            return true;
        }

        $validationResult->addErrors(
            new NoMoreCreditError($easyCoupon->getCode())
        );

        return false;
    }
}
