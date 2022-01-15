<?php

declare(strict_types=1);

namespace NetInventors\NetiNextEasyCoupon\Decorator;

use NetInventors\NetiNextEasyCoupon\Core\Content\Transaction\TransactionEntity;
use NetInventors\NetiNextEasyCoupon\Service\EasyCouponDecorationService;
use NetInventors\NetiNextEasyCoupon\Service\PluginConfig;
use NetInventors\NetiNextEasyCoupon\Service\VoucherService;
use NetInventors\NetiNextEasyCoupon\Struct\VoucherCodeGeneratorConfig;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenContainerEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Search\AggregationResult\AggregationResultCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\IdSearchResult;
use Shopware\Core\Framework\DataAbstractionLayer\Write\CloneBehavior;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\Currency\CurrencyEntity;

class EasyCouponRepositoryDecorator implements EntityRepositoryInterface
{
    /**
     * @var EntityRepositoryInterface
     */
    private $repository;

    /**
     * @var EasyCouponDecorationService
     */
    private $decorationService;

    /**
     * EntityRepositoryDecorator constructor.
     *
     * @param EntityRepositoryInterface   $repository
     * @param EasyCouponDecorationService $decorationService
     */
    public function __construct(
        EntityRepositoryInterface $repository,
        EasyCouponDecorationService $decorationService
    ) {
        $this->repository        = $repository;
        $this->decorationService = $decorationService;
    }

    public function getDefinition(): EntityDefinition
    {
        return $this->repository->getDefinition();
    }

    public function aggregate(Criteria $criteria, Context $context): AggregationResultCollection
    {
        return $this->repository->aggregate($criteria, $context);
    }

    public function searchIds(Criteria $criteria, Context $context): IdSearchResult
    {
        return $this->repository->searchIds($criteria, $context);
    }

    public function clone(string $id, Context $context, ?string $newId = null, ?CloneBehavior $behavior = null): EntityWrittenContainerEvent
    {
        return $this->repository->clone($id, $context, $newId, $behavior);
    }

    public function search(Criteria $criteria, Context $context): EntitySearchResult
    {
        return $this->repository->search($criteria, $context);
    }

    public function update(array $data, Context $context): EntityWrittenContainerEvent
    {
        return $this->repository->update($data, $context);
    }

    public function upsert(array $data, Context $context): EntityWrittenContainerEvent
    {
        foreach ($data as &$dataRow) {
            if (isset($dataRow['virtualImport']) && $dataRow['virtualImport'] !== '') {
                $newId = Uuid::randomHex();
                $dataRow['id'] = $newId;

                $virtualImportData = json_decode($dataRow['virtualImport'], true);
                $currency          =
                    $this->decorationService->getCurrencyByISO($dataRow['currency']['isoCode'], $context);

                $dataRow['currencyId'] = $currency->getId();

                $criteria = new Criteria();
                $criteria->addFilter(new EqualsFilter('code', $dataRow['code']));

                if (null !== $this->repository->search($criteria, $context)->first()) {
                    $dataRow['code'] = $this->decorationService->generateNewCode();
                }

                if (
                    isset($virtualImportData['conditions'])
                    && !$this->decorationService->importConditions(
                        $virtualImportData['conditions'],
                        $newId,
                        $context
                    )
                ) {
                    throw new \Exception('Can not import conditions');
                }

                $this->decorationService->createImportTransactionEntry($dataRow, $context, $currency);

                $dataRow['virtualImport'] = '';
                unset($dataRow['currency']);
            }
        }

        return $this->repository->upsert($data, $context);
    }

    public function create(array $data, Context $context): EntityWrittenContainerEvent
    {
        return $this->repository->create($data, $context);
    }

    public function delete(array $data, Context $context): EntityWrittenContainerEvent
    {
        return $this->repository->delete($data, $context);
    }

    public function createVersion(string $id, Context $context, ?string $name = null, ?string $versionId = null): string
    {
        return $this->repository->createVersion($id, $context, $name, $versionId);
    }

    public function merge(string $versionId, Context $context): void
    {
        $this->repository->merge($versionId, $context);
    }
}
