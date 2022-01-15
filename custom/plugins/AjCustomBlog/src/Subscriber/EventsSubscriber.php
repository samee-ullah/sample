<?php declare(strict_types=1);

namespace AjCustomBlog\Subscriber;

use Shopware\Core\Content\Cms\CmsPageEvents;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityLoadedEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Shopware\Core\Content\Product\ProductEvents;

class EventsSubscriber implements EventSubscriberInterface
{

    /** @var EntityRepositoryInterface */
    private $productRepository;

    public function __construct(EntityRepositoryInterface $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CmsPageEvents::PAGE_LOADED_EVENT => 'onCmsPageLoaded'
        ];
    }

    public function onCmsPageLoaded(EntityLoadedEvent $event)
    {
    }
}
