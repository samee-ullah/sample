<?php

declare(strict_types=1);

namespace NetInventors\NetiNextEasyCoupon\Subscriber;

use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityDeletedEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CouponSubscriber implements EventSubscriberInterface
{
    /**
     * @var EntityRepositoryInterface
     */
    private $productConditionRepository;

    public function __construct(EntityRepositoryInterface $productConditionRepository)
    {
        $this->productConditionRepository = $productConditionRepository;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'neti_easy_coupon_product.deleted' => 'onCouponProductDeleted',
        ];
    }

    public function onCouponProductDeleted(EntityDeletedEvent $event): void
    {
        $ids      = $event->getIds();
        $context  = $event->getContext();
        $criteria = (new Criteria())->addFilter(new EqualsAnyFilter('couponId', $ids));
        $ids      = $this->productConditionRepository->search($criteria, $context)->getIds();

        $ids = array_map(
            static function ($id) {
                return [
                    'id' => $id,
                ];
            },
            $ids
        );

        $this->productConditionRepository->delete(array_values($ids), $context);
    }
}
