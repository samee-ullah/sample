<?php

declare(strict_types=1);

namespace NetInventors\NetiNextEasyCoupon\Service\Repository;

use NetInventors\NetiNextEasyCoupon\Core\Content\Transaction\TransactionEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;

class TransactionRepository
{
    protected EntityRepositoryInterface $transactionRepo;

    public function __construct(EntityRepositoryInterface $transactionRepo)
    {
        $this->transactionRepo = $transactionRepo;
    }

    public function getLatestTransactionIdOfCoupon(string $couponId, Context $context): ?TransactionEntity
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('easyCouponId', $couponId))
            ->addSorting(new FieldSorting('createdAt', FieldSorting::DESCENDING));

        return $this->transactionRepo->search($criteria, $context)->first();
    }
}
