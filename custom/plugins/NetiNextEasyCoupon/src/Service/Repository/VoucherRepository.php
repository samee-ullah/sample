<?php

/**
 * @copyright Copyright (c) 2020, Net Inventors GmbH
 * @category  Shopware
 * @author    mpeters
 */

declare(strict_types=1);

namespace NetInventors\NetiNextEasyCoupon\Service\Repository;

use NetInventors\NetiNextEasyCoupon\Core\Content\EasyCoupon\EasyCouponEntity;
use NetInventors\NetiNextEasyCoupon\Core\Content\Transaction\TransactionEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenContainerEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;

class VoucherRepository
{
    /**
     * @var EntityRepositoryInterface
     */
    private $voucherRepository;

    public function __construct(
        EntityRepositoryInterface $voucherRepository
    ) {
        $this->voucherRepository = $voucherRepository;
    }

    public function getVoucherByCode(string $voucherCode, Context $context): ?EasyCouponEntity
    {
        $criteria = $this->buildCriteriaForVoucherCode($voucherCode);

        return $this->voucherRepository->search($criteria, $context)->first();
    }

    public function getVoucherByCodeWithAssociations(string $voucherCode, Context $context): ?EasyCouponEntity
    {
        $criteria = $this->buildCriteriaForVoucherCode($voucherCode)
            ->addAssociations([
                'conditions',
                'translations'
            ]);

        return $this->voucherRepository->search($criteria, $context)->first();
    }

    private function buildCriteriaForVoucherCode(string $voucherCode): Criteria
    {
        $criteria = new Criteria();

        return $criteria->addFilter(new EqualsFilter('code', $voucherCode));
    }

    /**
     * @param EasyCouponEntity[] $vouchers
     * @param Context            $context
     *
     * @return EntityWrittenContainerEvent
     */
    public function activateMailSent(array $vouchers, Context $context): EntityWrittenContainerEvent
    {
        return $this->voucherRepository->update($this->collectActivateMailSentData($vouchers), $context);
    }

    /**
     * @param string  $orderTransactionId
     * @param Context $context
     *
     * @return EasyCouponEntity[]
     */
    public function collectOrderedVouchersWithNoMailSending(string $orderTransactionId, Context $context): array
    {
        $criteria = new Criteria();
        $criteria->addAssociations([
            'transactions',
            'transactions.order',
            'transactions.order.transactions',
            'transactions.order.orderCustomer',
            'transactions.order.orderCustomer.salutation',
            'transactions.order.salesChannel',
            'currency',
        ])->addFilter(new MultiFilter(MultiFilter::CONNECTION_AND, [
            new EqualsFilter('transactions.order.transactions.id', $orderTransactionId),
            new EqualsFilter('transactions.transactionType', TransactionEntity::TYPE_CREATED_BY_PURCHASE),
            new EqualsFilter('mailSent', false),
        ]));

        return $this->voucherRepository->search($criteria, $context)->getElements();
    }

    public function getPurchaseVoucherOfOrder(string $orderId, Context $context): EntitySearchResult
    {
        $criteria = new Criteria();
        $criteria->addAssociations([
            'transactions',
            'transactions.order',
            'product',
            'currency',
        ])->addFilter(new MultiFilter(MultiFilter::CONNECTION_AND, [
            new EqualsFilter('transactions.order.id', $orderId),
            new EqualsFilter('transactions.transactionType', TransactionEntity::TYPE_CREATED_BY_PURCHASE),
        ]));

        return $this->voucherRepository->search($criteria, $context);
    }

    /**
     * @param EasyCouponEntity[] $vouchers
     *
     * @return array[]
     */
    protected function collectActivateMailSentData(array $vouchers): array
    {
        $data = [];
        foreach ($vouchers as $voucher) {
            $data[] = [
                'id'       => $voucher->getId(),
                'mailSent' => true,
            ];
        }

        return $data;
    }
}
