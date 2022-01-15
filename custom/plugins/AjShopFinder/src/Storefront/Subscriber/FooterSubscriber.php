<?php


namespace AjShopFinder\Storefront\Subscriber;


use AjShopFinder\Core\Content\ShopFinder\ShopFinderCollection;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Shopware\Storefront\Pagelet\Footer\FooterPageletLoadedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class FooterSubscriber implements EventSubscriberInterface
{
    private $systemConfigService;
    private $shopFinderRepository;

    public function __construct(
        SystemConfigService       $systemConfigService,
        EntityRepositoryInterface $shopFinderRepository
    )
    {
        $this->systemConfigService = $systemConfigService;
        $this->shopFinderRepository = $shopFinderRepository;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            FooterPageletLoadedEvent::class => 'onFooterPageletLoaded'
        ];
    }

    public function onFooterPageletLoaded(FooterPageletLoadedEvent $event): void
    {
        $key = $this->systemConfigService->get('AjShopFinder.config.showInStorefront');
        if (!$key) {
            return;
        }

        $shops = $this->fetchShops($event->getContext());

        $event->getPagelet()->addExtension('aj_shop_finder', $shops);
    }

    private function fetchShops(Context $context): ShopFinderCollection
    {
        $criteria = new Criteria();
        $criteria->addAssociation('country');
        $criteria->addFilter(new EqualsFilter('active', 1));
        $criteria->setLimit(5);

        /** @var ShopFinderCollection $shopFinderCollection */
        return $this->shopFinderRepository->search($criteria, $context)->getEntities();
    }
}
