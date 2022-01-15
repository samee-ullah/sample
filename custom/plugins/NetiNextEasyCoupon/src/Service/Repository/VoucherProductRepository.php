<?php

/**
 * @copyright Copyright (c) 2020, Net Inventors GmbH
 * @category  Shopware
 * @author    mpeters
 */

declare(strict_types=1);

namespace NetInventors\NetiNextEasyCoupon\Service\Repository;

use NetInventors\NetiNextEasyCoupon\Core\Content\Product\Aggregate\EasyCouponProductEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;

class VoucherProductRepository
{
    /**
     * @var EntityRepositoryInterface
     */
    protected $productAttributeRepository;

    public function __construct(
        EntityRepositoryInterface $productAttributeRepository
    ) {
        $this->productAttributeRepository = $productAttributeRepository;
    }

    /**
     * @param string[] $productIds
     * @param Context  $context
     *
     * @return EasyCouponProductEntity[]
     */
    public function getProductAttributeWithTranslations(array $productIds, Context $context): array
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsAnyFilter('productId', $productIds))
            ->addAssociation('product')
            ->addAssociation('translations')
            ->addAssociation('conditions');

        return $this->productAttributeRepository->search($criteria, $context)->getElements();
    }

    /**
     * @param string[] $productIds
     * @param Context  $context
     *
     * @return EasyCouponProductEntity[]
     */
    public function getProductAttribute(array $productIds, Context $context): array
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsAnyFilter('productId', $productIds));

        return $this->productAttributeRepository->search($criteria, $context)->getElements();
    }
}
