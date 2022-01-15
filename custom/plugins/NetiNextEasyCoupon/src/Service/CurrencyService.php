<?php

declare(strict_types=1);

namespace NetInventors\NetiNextEasyCoupon\Service;

use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\Currency\CurrencyEntity;

class CurrencyService
{
    /**
     * @var EntityRepositoryInterface
     */
    private $currencyRepository;

    /**
     * @var CurrencyEntity
     */
    private $defaultCurrency;

    public function __construct(
        EntityRepositoryInterface $currencyRepository
    ) {
        $this->currencyRepository = $currencyRepository;
    }

    public function getDefaultCurrency(Context $context): CurrencyEntity
    {
        if (null === $this->defaultCurrency) {
            $criteria = new Criteria([ Defaults::CURRENCY ]);
            $result   = $this->currencyRepository->search($criteria, $context);

            $this->defaultCurrency = $result->first();
        }

        return $this->defaultCurrency;
    }
}