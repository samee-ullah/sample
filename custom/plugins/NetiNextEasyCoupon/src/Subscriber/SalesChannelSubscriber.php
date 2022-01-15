<?php
/**
 * @copyright Copyright (c) 2020, Net Inventors GmbH
 * @category  Shopware
 * @author    mpeters
 */

declare(strict_types=1);

namespace NetInventors\NetiNextEasyCoupon\Subscriber;

use NetInventors\NetiNextEasyCoupon\Service\PluginConfig;
use NetInventors\NetiNextEasyCoupon\Service\ProductService;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelEntityLoadedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SalesChannelSubscriber implements EventSubscriberInterface
{
    /**
     * @var PluginConfig
     */
    private $pluginConfig;

    /**
     * @var ProductService
     */
    private $productService;

    public function __construct(
        PluginConfig $pluginConfig,
        ProductService $productService
    ) {
        $this->pluginConfig   = $pluginConfig;
        $this->productService = $productService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'sales_channel.product.loaded' => 'onSalesChannelProductLoaded',
        ];
    }

    public function onSalesChannelProductLoaded(SalesChannelEntityLoadedEvent $event): void
    {
        if (!$this->pluginConfig->isActive()) {
            return;
        }

        $this->productService->setPriceRanges($event->getEntities(), $event->getSalesChannelContext()->getCurrency()->getId());
    }
}
