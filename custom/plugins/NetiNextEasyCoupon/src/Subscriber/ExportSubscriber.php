<?php

declare(strict_types=1);

namespace NetInventors\NetiNextEasyCoupon\Subscriber;

use NetInventors\NetiNextEasyCoupon\Core\Content\Condition\ConditionEntity;
use NetInventors\NetiNextEasyCoupon\Core\Content\Transaction\TransactionEntity;
use NetInventors\NetiNextEasyCoupon\Service\Account\VoucherTransactionsService;
use Shopware\Core\Content\ImportExport\Event\ImportExportBeforeExportRecordEvent;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ExportSubscriber implements EventSubscriberInterface
{
    /**
     * @var EntityRepositoryInterface
     */
    private $conditionRepository;

    /**
     * @var VoucherTransactionsService
     */
    private $voucherTransactionsService;

    /**
     * ExportSubscriber constructor.
     *
     * @param EntityRepositoryInterface $conditionRepository
     */
    public function __construct(EntityRepositoryInterface $conditionRepository, VoucherTransactionsService $voucherTransactionsService)
    {
        $this->conditionRepository        = $conditionRepository;
        $this->voucherTransactionsService = $voucherTransactionsService;
    }

    public static function getSubscribedEvents()
    {
        return [
            ImportExportBeforeExportRecordEvent::class => 'beforeExport',
        ];
    }

    public function beforeExport(ImportExportBeforeExportRecordEvent $event)
    {
        if ('neti_easy_coupon' !== $event->getConfig()->get('sourceEntity')) {
            return;
        }

        $record          = $event->getRecord();
        $conditions      = $this->getCouponConditions($record['id']);
        $conditionsArray = [];
        $virtualImport   = [];

        $voucherTransactions = $this->voucherTransactionsService->getTransactionsForVouchers([$record['id']]);
        /** @var TransactionEntity $lastTransaction */
        $lastTransaction = $voucherTransactions->last();

        if (null !== $lastTransaction->getCustomerId() && null !== $lastTransaction->getOrderId()) {
            $virtualImport['customerNumber'] = $lastTransaction->getOrder()->getOrderCustomer()->getCustomerNumber();
        }

        if ($conditions->getElements() !== []) {
            /** @var ConditionEntity $condition */
            foreach ($conditions->getElements() as $condition) {
                $conditionArray             = [];
                $conditionArray['id']       = $condition->getId();
                $conditionArray['couponId'] = $condition->getCouponId();
                $conditionArray['parentId'] = $condition->getParentId();
                $conditionArray['value']    = $condition->getValue();
                $conditionArray['position'] = $condition->getPosition();

                //getType crashes if the return value not a string, for some reason we also save a null value
                // there for a dirty solution is needed
                try {
                    $conditionArray['type'] = $condition->getType();
                } catch (\TypeError $exception) {
                    $conditionArray['type'] = null;
                }
                $conditionsArray[] = $conditionArray;
            }
        }

        $record['value']              = $voucherTransactions->getEntities()->sum();
        $virtualImport['conditions']  = $conditionsArray;
        $record['virtual_import']     = json_encode($virtualImport);

        $event->setRecord($record);
    }

    /**
     * @param string $couponId
     *
     * @return EntitySearchResult
     */
    private function getCouponConditions(string $couponId): EntitySearchResult
    {
        $criteria = new Criteria();

        $criteria->addFilter(new EqualsFilter('couponId', $couponId));
        $criteria->addSorting(new FieldSorting('createdAt', FieldSorting::ASCENDING));

        return $this->conditionRepository->search($criteria, Context::createDefaultContext());
    }
}
