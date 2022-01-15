<?php

declare(strict_types=1);

namespace NetInventors\NetiNextEasyCoupon\Service;

use NetInventors\NetiNextEasyCoupon\Core\Content\Product\Aggregate\Condition\ConditionCollection as ProductConditionCollection;
use NetInventors\NetiNextEasyCoupon\Core\Content\Product\Aggregate\Condition\ConditionEntity as ProductConditionEntity;
use NetInventors\NetiNextEasyCoupon\Core\Content\Transaction\TransactionEntity;
use NetInventors\NetiNextEasyCoupon\Struct\VoucherCodeGeneratorConfig;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\Currency\CurrencyEntity;

class EasyCouponDecorationService
{
    /**
     * @var EntityRepositoryInterface
     */
    private $currencyRepository;

    /**
     * @var EntityRepositoryInterface
     */
    private $transactionRepository;

    /**
     * @var EntityRepositoryInterface
     */
    private $easyCouponConditionRepository;

    /**
     * @var PluginConfig
     */
    private $pluginConfig;

    /**
     * @var VoucherService
     */
    private $voucherService;

    /**
     * @var EntityRepositoryInterface
     */
    private $manufacturerRepository;

    /**
     * @var EntityRepositoryInterface
     */
    private $salesChannelRepository;

    /**
     * @var EntityRepositoryInterface
     */
    private $productRepository;

    /**
     * @var EntityRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var EntityRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @var EntityRepositoryInterface
     */
    private $customerGroupRepository;

    /**
     * EasyCouponDecorationService constructor.
     *
     // * @param EntityRepositoryInterface $easyCouponRepository
     * @param EntityRepositoryInterface $currencyRepository
     * @param EntityRepositoryInterface $transactionRepository
     * @param EntityRepositoryInterface $easyCouponConditionRepository
     * @param EntityRepositoryInterface $manufacturerRepository
     * @param EntityRepositoryInterface $salesChannelRepository
     * @param EntityRepositoryInterface $productRepository
     * @param EntityRepositoryInterface $customerRepository
     * @param EntityRepositoryInterface $categoryRepository
     * @param EntityRepositoryInterface $customerGroupRepository
     * @param PluginConfig              $pluginConfig
     * @param VoucherService            $voucherService
     */
    public function __construct(
        EntityRepositoryInterface $currencyRepository,
        EntityRepositoryInterface $transactionRepository,
        EntityRepositoryInterface $easyCouponConditionRepository,
        EntityRepositoryInterface $manufacturerRepository,
        EntityRepositoryInterface $salesChannelRepository,
        EntityRepositoryInterface $productRepository,
        EntityRepositoryInterface $customerRepository,
        EntityRepositoryInterface $categoryRepository,
        EntityRepositoryInterface $customerGroupRepository,
        PluginConfig $pluginConfig,
        VoucherService $voucherService
    ) {
        $this->currencyRepository            = $currencyRepository;
        $this->transactionRepository         = $transactionRepository;
        $this->easyCouponConditionRepository = $easyCouponConditionRepository;
        $this->pluginConfig                  = $pluginConfig;
        $this->voucherService                = $voucherService;
        $this->manufacturerRepository        = $manufacturerRepository;
        $this->salesChannelRepository        = $salesChannelRepository;
        $this->productRepository             = $productRepository;
        $this->customerRepository            = $customerRepository;
        $this->categoryRepository            = $categoryRepository;
        $this->customerGroupRepository       = $customerGroupRepository;
    }

    public function getCurrencyByISO(string $isoCode, Context $context): CurrencyEntity
    {
        $criteria = new Criteria();

        $criteria->addFilter(new EqualsFilter('isoCode', $isoCode));

        return $this->currencyRepository->search($criteria, $context)->first();
    }

    public function createImportTransactionEntry(array $data, Context $context, CurrencyEntity $currency): void
    {
        $virtualImportData = json_decode($data['virtualImport'], true);

        $insertData = [
            'easyCouponId'    => $data['id'],
            'transactionType' => TransactionEntity::TYPE_CREATED_BY_IMPORT,
            'value'           => $data['value'],
            'currencyId'      => $data['currencyId'],
            'currencyFactor'  => $currency->getFactor(),
            'salesChannelId'  => Defaults::SALES_CHANNEL,
        ];

        if (isset($virtualImportData['customerNumber'])) {
            $customerId = $this->getCustomerIdByNumber($virtualImportData['customerNumber'], $context);

            if (null !== $customerId) {
                $insertData['customerId'] = $customerId;
            }
        }

        $this->transactionRepository->upsert([ $insertData ], $context);
    }

    public function importConditions(array $conditions, string $easyCouponId, Context $context): bool
    {
        $insertData = $this->mapConditions($conditions, null, $context, $easyCouponId);

        if ([] !== $insertData || empty($insertData)) {
            $this->easyCouponConditionRepository->upsert($insertData, $context);
        }

        return true;
    }

    /**
     * This method transforms the 1-dimensional condition collection into a multi-dimensional array.
     *
     * @param array       $conditions
     * @param string|null $parentId
     * @param Context     $context
     * @param string      $easyCouponId
     *
     * @return array
     */
    public function mapConditions (
        array $conditions,
        ?string $parentId,
        Context $context,
        string $easyCouponId
    ): array {
        if ([] === $conditions) {
            return [];
        }

        return array_values(
            array_filter(
                array_map(
                    function ($condition) use ($conditions, $context, $easyCouponId) {
                        return [
                            'type'     => $condition['type'],
                            'value'    => $condition['value'],
                            'position' => $condition['position'],
                            'couponId' => $easyCouponId,
                            'children' => $this->mapConditions($conditions, $condition['id'], $context, $easyCouponId)
                        ];
                    },
                    array_filter(
                        $conditions,
                        static function ($condition) use($parentId) {
                            return $condition['parentId'] === $parentId;
                        }
                    )
                ),
                function ($condition) use($context) {
                    return $this->isIdMappable($condition['value'], $context);
                }
            )
        );
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function generateNewCode(): string
    {
        $config = new VoucherCodeGeneratorConfig();
        $config->setNumOfVoucherCodes(1);
        $config->setPattern($this->pluginConfig->getDefaultCodePattern());

        return $this->voucherService->generateVoucherCodes($config)->first();
    }

    /**
     * @param array|null $conditionValue
     * @param Context    $context
     *
     * @return bool
     */
    public function isIdMappable(?array $conditionValue, Context $context): bool
    {
        if (null === $conditionValue || [] === $conditionValue) {
            return true;
        }

        switch ($conditionValue) {
            case isset($conditionValue['manufacturerIds']):
                $repository = $this->manufacturerRepository;
                $ids = $conditionValue['manufacturerIds'];
                break;
            case isset($conditionValue['salesChannelIds']):
                $repository = $this->salesChannelRepository;
                $ids = $conditionValue['salesChannelIds'];
                break;
            case isset($conditionValue['identifiers']):
                $repository = $this->productRepository;
                $ids = $conditionValue['identifiers'];
                break;
            case isset($conditionValue['customerIds']):
                $repository = $this->customerRepository;
                $ids = $conditionValue['customerIds'];
                break;
            case isset($conditionValue['categoryIds']):
                $repository = $this->categoryRepository;
                $ids = $conditionValue['categoryIds'];
                break;
            case isset($conditionValue['customerGroupIds']):
                $repository = $this->customerGroupRepository;
                $ids = $conditionValue['customerGroupIds'];
                break;
            default:
                return true;
        }

        $criteria = new Criteria($ids);

        return !(0 === $repository->searchIds($criteria, $context)->getTotal());
    }

    private function getCustomerIdByNumber(string $customerNumber, Context $context): ?string
    {
        $criteria = new Criteria();

        $criteria->addFilter(new EqualsFilter('customerNumber', $customerNumber));

        return $this->customerRepository->searchIds($criteria, $context)->firstId();
    }
}
