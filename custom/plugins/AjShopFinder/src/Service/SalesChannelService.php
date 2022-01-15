<?php

namespace AjShopFinder\Service;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\System\SalesChannel\Context\AbstractSalesChannelContextFactory;
use Shopware\Core\System\SalesChannel\Context\SalesChannelContextService;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SalesChannel\SalesChannelEntity;

class SalesChannelService
{
    private EntityRepositoryInterface $salesChannelRepository;

    private AbstractSalesChannelContextFactory $salesChannelContextFactory;

    public function __construct(
        EntityRepositoryInterface $salesChannelRepository,
        AbstractSalesChannelContextFactory $salesChannelContextFactory
    )
    {
        $this->salesChannelRepository = $salesChannelRepository;
        $this->salesChannelContextFactory = $salesChannelContextFactory;
    }

    /**
     * Method for creating a sales channel context
     *
     * @param string|null $salesChannelId
     * @param string|null $languageId
     * @return SalesChannelContext
     */
    public function createSalesChannelContext(string $salesChannelId = null, string $languageId = null) : SalesChannelContext
    {
        //get the sales channel ID and language ID, if they are missing
        if (!isset($salesChannelId) || !isset($languageId)) {
            $criteria = new Criteria();
            if (isset($salesChannelId)) {
                $criteria->addFilter(new EqualsFilter('salesChannelId', $salesChannelId));
            }
            if (isset($languageId)) {
                $criteria->addFilter(new EqualsFilter('languageId', $languageId));
            }

            /** @var SalesChannelEntity $salesChannel */
            $salesChannel = $this->salesChannelRepository->search($criteria, Context::createDefaultContext())->first();
            if ($salesChannel) {
                $salesChannelId = $salesChannel->getId();
                $languageId = $salesChannel->getLanguageId();
            }
        }

        return $this->salesChannelContextFactory->create('', $salesChannelId, [SalesChannelContextService::LANGUAGE_ID => $languageId]);
    }

}
