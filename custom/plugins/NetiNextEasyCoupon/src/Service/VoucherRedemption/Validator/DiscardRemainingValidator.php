<?php

declare(strict_types=1);

namespace NetInventors\NetiNextEasyCoupon\Service\VoucherRedemption\Validator;

use NetInventors\NetiNextEasyCoupon\Core\Content\EasyCoupon\EasyCouponEntity;
use NetInventors\NetiNextEasyCoupon\Core\Content\Product\Cart\EasyCouponError;
use NetInventors\NetiNextEasyCoupon\Core\Content\Transaction\TransactionEntity;
use NetInventors\NetiNextEasyCoupon\Service\Account\VoucherTransactionsService;
use NetInventors\NetiNextEasyCoupon\Service\VoucherRedemption\ValidationResult;
use NetInventors\NetiNextEasyCoupon\Service\VoucherRedemption\Validator\Error\AlreadyRedeemedError;
use Shopware\Core\Checkout\Cart\Price\CashRounding;
use Shopware\Core\Checkout\Customer\CustomerEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Util\FloatComparator;

class DiscardRemainingValidator implements ValidatorInterface
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

    /**
     * @param ValidationContext $validationContext
     * @param ValidationResult  $validationResult
     *
     * @return bool
     * @throws EasyCouponError
     */
    public function validate(
        ValidationContext $validationContext,
        ValidationResult $validationResult
    ): bool {
        $easyCoupon = $validationContext->getEasyCoupon();

        // If this is a general voucher, we can skip the discard remaining check
        if (EasyCouponEntity::VOUCHER_TYPE_GENERAL === $easyCoupon->getVoucherType()) {
            return true;
        }

        if (!$easyCoupon->isDiscardRemaining()) {
            return true;
        }

        if (!$this->hasDebitTransaction($easyCoupon)) {
            return true;
        }

        $validationResult->addErrors(
            new AlreadyRedeemedError($easyCoupon->getCode())
        );

        return false;
    }

    /**
     * @param EasyCouponEntity    $easyCoupon
     * @param CustomerEntity|null $user
     *
     * @return bool
     * @throws EasyCouponError
     */
    private function hasDebitTransaction(EasyCouponEntity $easyCoupon, ?CustomerEntity $user = null): bool
    {
        $userId       = null === $user ? null : $user->getId();
        $transactions = $this->transactionsService->getTransactionsForVouchers([ $easyCoupon->getId() ], $userId);

        // We have to verify that the transactions are loaded for the collection otherwise throw an exception
        // Due to the application structure the collection count has to be greater than 0
        if (0 === $transactions->count()) {
            throw new EasyCouponError($easyCoupon->getCode(), 'No transactions found.');
        }

        $context       = Context::createDefaultContext();
        $rounding      = $context->getRounding();
        $priceRounding = $this->priceRounding;
        $sum           = $priceRounding->mathRound(0.0, $rounding);

        $transactions->map(
            static function (TransactionEntity $transaction) use ($rounding, $priceRounding, &$sum): void {
                $transactionType = $transaction->getTransactionType();

                if (
                    TransactionEntity::TYPE_CREATED_BY_ADMIN === $transactionType
                    || TransactionEntity::TYPE_CREATED_BY_PURCHASE === $transactionType
                ) {
                    return;
                }

                $sum += $priceRounding->mathRound($transaction->getValue(), $rounding);
            }
        );

        return FloatComparator::notEquals($priceRounding->mathRound(0.0, $rounding), $sum);
    }
}
