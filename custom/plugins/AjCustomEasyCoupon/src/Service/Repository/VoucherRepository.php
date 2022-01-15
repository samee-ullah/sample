<?php

namespace AjCustomEasyCoupon\Service\Repository;

use NetInventors\NetiNextEasyCoupon\Core\Content\EasyCoupon\EasyCouponEntity;
use NetInventors\NetiNextEasyCoupon\Core\Content\Transaction\TransactionEntity;
use NetInventors\NetiNextEasyCoupon\Service\Repository\VoucherRepository as VoucherRepositoryParent;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenContainerEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;

class VoucherRepository extends VoucherRepositoryParent
{
    /**
     * @var EntityRepositoryInterface
     */
    private $voucherRepository;

    /**
     * @var EntityRepositoryInterface
     */
    private $transactionRepository;

    public function __construct(
        EntityRepositoryInterface $voucherRepository,
        EntityRepositoryInterface $transactionRepository
    )
    {
        parent::__construct($voucherRepository);
        $this->voucherRepository = $voucherRepository;
        $this->transactionRepository = $transactionRepository;
    }

    /**
     * @param $orderNumber
     * @param EasyCouponEntity[] $vouchers
     * @param Context $context
     *
     * @return EntityWrittenContainerEvent
     */
    public function activateMailSent(array $vouchers, Context $context): EntityWrittenContainerEvent
    {
        $this->transactionRepository->update($this->collectTransactionData($vouchers, $context), $context);
        return $this->voucherRepository->update($this->collectActivateMailSentData($vouchers), $context);
    }

    private function collectTransactionData(array $vouchers, $context): array
    {

        $orderId = $context->getExtension('order')->getId();

        $criteria = new Criteria();
        $criteria->addFilter(
            new MultiFilter(
                MultiFilter::CONNECTION_AND, [
                    new EqualsFilter('orderId', $orderId),
                ]
            )
        );

        $transactions = $this->transactionRepository->search($criteria, $context)->getElements();

        $data = [];
        foreach ($transactions as $transaction) {
            $data[] = [
                'id' => $transaction->getId(),
                'transactionType' => TransactionEntity::TYPE_CREATED_BY_ADMIN,
            ];
        }

        return $data;
    }

    private function buildCriteriaForVoucherCode(string $voucherCode): Criteria
    {
        $criteria = new Criteria();

        return $criteria->addFilter(new EqualsFilter('code', $voucherCode));
    }
}
