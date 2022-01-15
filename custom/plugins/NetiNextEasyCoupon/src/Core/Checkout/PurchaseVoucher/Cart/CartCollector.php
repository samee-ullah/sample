<?php
/**
 * @copyright Copyright (c) 2020, Net Inventors GmbH
 * @category  Shopware
 * @author    mpeters
 */

namespace NetInventors\NetiNextEasyCoupon\Core\Checkout\PurchaseVoucher\Cart;

use NetInventors\NetiNextEasyCoupon\Core\Content\Product\Aggregate\EasyCouponProductEntity;
use NetInventors\NetiNextEasyCoupon\Service\PluginConfig;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\CartBehavior;
use Shopware\Core\Checkout\Cart\CartDataCollectorInterface;
use Shopware\Core\Checkout\Cart\LineItem\CartDataCollection;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class CartCollector implements CartDataCollectorInterface
{
    public const PRODUCT_ATTRIBUTE_KEY = 'easy-coupon-attribute-';

    /**
     * @var PluginConfig
     */
    private $pluginConfig;

    /**
     * @var EntityRepositoryInterface
     */
    private $easyCouponProductRepository;

    public function __construct(
        PluginConfig $pluginConfig,
        EntityRepositoryInterface $easyCouponProductRepository
    ) {
        $this->pluginConfig                = $pluginConfig;
        $this->easyCouponProductRepository = $easyCouponProductRepository;
    }

    public function collect(
        CartDataCollection $data,
        Cart $original,
        SalesChannelContext $context,
        CartBehavior $behavior
    ): void {
        if (!$this->pluginConfig->isActive()) {
            return;
        }

        $productIds = $original->getLineItems()->filterType(LineItem::PRODUCT_LINE_ITEM_TYPE)->getReferenceIds();
        if ([] === $productIds) {
            return;
        }

        $filtered = $this->filterAlreadyFetchedPrices($productIds, $data);
        if ([] === $filtered) {
            return;
        }

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsAnyFilter('productId', $filtered));

        /** @var EasyCouponProductEntity[] $productAttributes */
        $productAttributes = $this->easyCouponProductRepository->search($criteria, $context->getContext())->getElements();
        foreach ($productAttributes as $attribute) {
            $productId = $attribute->getProductId();

            if (null === $productId) {
                continue;
            }

            $key = $this->buildKey($productId);

            if (!$data->has($key)) {
                $data->set($key, $attribute);
            }
        }
    }

    private function filterAlreadyFetchedPrices(array $productIds, CartDataCollection $data): array
    {
        $filtered = [];

        foreach ($productIds as $id) {
            $key = $this->buildKey($id);

            // already fetched from database?
            if ($data->has($key)) {
                continue;
            }

            $filtered[] = $id;
        }

        return $filtered;
    }

    private function buildKey(string $id): string
    {
        return self::PRODUCT_ATTRIBUTE_KEY . $id;
    }
}
