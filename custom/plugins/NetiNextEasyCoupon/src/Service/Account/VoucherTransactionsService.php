<?php

/**
 * @copyright  Copyright (c) 2020, Net Inventors GmbH
 * @category   Shopware
 * @author     drebrov
 */

declare(strict_types=1);

namespace NetInventors\NetiNextEasyCoupon\Service\Account;

use NetInventors\NetiNextEasyCoupon\Core\Content\EasyCoupon\EasyCouponEntity;
use NetInventors\NetiNextEasyCoupon\Core\Content\Transaction\TransactionEntity;
use Shopware\Core\Checkout\Cart\Price\CashRounding;
use Shopware\Core\Checkout\Customer\CustomerEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Grouping\FieldGrouping;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;

class VoucherTransactionsService
{
    /**
     * @var EntityRepositoryInterface
     */
    private $easyCouponTransactionRepository;

    /**
     * @var CashRounding
     */
    private $priceRounding;

    public function __construct(
        EntityRepositoryInterface $easyCouponTransactionRepository,
        CashRounding $priceRounding
    ) {
        $this->easyCouponTransactionRepository = $easyCouponTransactionRepository;
        $this->priceRounding                   = $priceRounding;
    }

    public function getVouchersForCustomer(string $customerId, Context $context): array
    {
        $coupons = [];

        $transactionCriteria = new Criteria();
        $transactionCriteria->addFilter(new EqualsFilter('customerId', $customerId));
        $transactionCriteria->addFilter(new EqualsFilter('easyCoupon.deleted', false));
        $transactionCriteria->addAssociation('easyCoupon');
        $transactionCriteria->addAssociation('easyCoupon.currency');
        $transactionCriteria->addGroupField(new FieldGrouping('easyCouponId'));

        $transactions = $this->easyCouponTransactionRepository->search($transactionCriteria, $context)->getElements();

        if (!empty($transactions)) {
            /** @var TransactionEntity $transaction */
            foreach ($transactions as $transaction) {
                $coupons[$transaction->getEasyCouponId()] = $transaction->getEasyCoupon();
            }
        }

        return $coupons;
    }

    public function getTransactionsForVouchers(array $voucherIds, ?string $customerId = null): EntitySearchResult
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsAnyFilter('easyCouponId', $voucherIds));
        $criteria->addSorting(new FieldSorting('createdAt', FieldSorting::ASCENDING));
        $criteria->addAssociations([ 'currency', 'customer', 'user', 'salesChannel', 'order', 'easyCoupon' ]);

        if (null !== $customerId) {
            $criteria->addFilter(new EqualsFilter('customerId', $customerId));
        }

        return $this->easyCouponTransactionRepository->search($criteria, Context::createDefaultContext());
    }

    /**
     * Returns the rest values in the default currency
     *
     * @param EasyCouponEntity[]       $vouchers
     * @param TransactionEntity[]|null $transactions
     *
     * @return float[]
     */
    public function getRestValueForVouchers(array $vouchers, ?array $transactions = null): array
    {
        $restValues = [];

        if (null === $transactions) {
            $voucherIds = array_map(
                static function (EasyCouponEntity $voucher) {
                    return $voucher->getId();
                },
                $vouchers
            );

            $transactions = $this->getTransactionsForVouchers($voucherIds)->getElements();
        }

        foreach ($vouchers as $voucher) {
            foreach ($transactions as $transaction) {
                $transactionType = $transaction->getTransactionType();

                if (
                    TransactionEntity::TYPE_CREATED_BY_PURCHASE === $transactionType
                    || TransactionEntity::TYPE_CREATED_BY_IMPORT === $transactionType
                ) {
                    continue;
                }

                if (($id = $voucher->getId()) === $transaction->getEasyCouponId()) {
                    $restValues[$id] =
                        isset($restValues[$id])
                            ? $restValues[$id] + $transaction->getValue()
                            : $transaction->getValue();
                }
            }
        }

        return $restValues;
    }

    public function getTransactionsForGeneralVoucherByCustomer(
        EasyCouponEntity $easyCouponEntity,
        ?CustomerEntity $customerEntity = null
    ): EntitySearchResult {
        $customerId = null;
        if ($customerEntity instanceof CustomerEntity) {
            $customerId = $customerEntity->getId();
        }

        return $this->getTransactionsForGeneralVoucher($easyCouponEntity, $customerId);
    }

    public function getTransactionsForGeneralVoucher(
        EasyCouponEntity $easyCouponEntity,
        ?string $customerId = null
    ): EntitySearchResult {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('easyCouponId', $easyCouponEntity->getId()));

        $multiFilterConditions = [ new EqualsFilter('customerId', null) ];

        if (\is_string($customerId)) {
            $multiFilterConditions[] = new EqualsFilter('customerId', $customerId);
        }

        $criteria->addFilter(new MultiFilter(
            MultiFilter::CONNECTION_OR,
            $multiFilterConditions
        ));

        return $this->easyCouponTransactionRepository->search(
            $criteria,
            Context::createDefaultContext()
        );
    }

    public function getTransactionsForIndividualVoucher(
        EasyCouponEntity $easyCouponEntity,
        ?CustomerEntity $customerEntity = null
    ): EntitySearchResult {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('easyCouponId', $easyCouponEntity->getId()));

        if ($customerEntity instanceof CustomerEntity) {
            $criteria->addFilter(new EqualsFilter('customerId', $customerEntity->getId()));
        }

        return $this->easyCouponTransactionRepository->search(
            $criteria,
            Context::createDefaultContext()
        );
    }

    public function getTypeBasedVoucherRestValue(
        EasyCouponEntity $easyCouponEntity,
        ?CustomerEntity $customer = null
    ): float {
        $transactions = $this->getTypeBasedVoucherTransactions($easyCouponEntity, $customer);

        return $this->buildSumFromTransactions($transactions);
    }

    public function buildSumFromTransactions(iterable $transactions): float
    {
        $context = Context::createDefaultContext();
        $value   = 0.00;

        foreach ($transactions as $transaction) {
            $value += $this->priceRounding->mathRound($transaction->getValue(), $context->getRounding());
        }

        return $value;
    }

    public function getTypeBasedVoucherTransactions(
        EasyCouponEntity $easyCouponEntity,
        ?CustomerEntity $customer = null
    ): EntitySearchResult {
        if (EasyCouponEntity::VOUCHER_TYPE_GENERAL === $easyCouponEntity->getVoucherType()) {
            return $this->getTransactionsForGeneralVoucherByCustomer($easyCouponEntity, $customer);
        }

        return $this->getTransactionsForIndividualVoucher($easyCouponEntity, $customer);
    }
}
