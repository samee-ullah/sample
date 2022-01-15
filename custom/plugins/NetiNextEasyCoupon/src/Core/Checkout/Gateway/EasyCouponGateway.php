<?php

declare(strict_types=1);

namespace NetInventors\NetiNextEasyCoupon\Core\Checkout\Gateway;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class EasyCouponGateway
{
    /**
     * @var EntityRepositoryInterface
     */
    private $easyCouponRepository;

    public function __construct(EntityRepositoryInterface $easyCouponRepository)
    {
        $this->easyCouponRepository = $easyCouponRepository;
    }

    public function get(Criteria $criteria, SalesChannelContext $context): EntityCollection
    {
        return $this->easyCouponRepository->search(
            $criteria,
            $context->getContext()
        )->getEntities();
    }
}
