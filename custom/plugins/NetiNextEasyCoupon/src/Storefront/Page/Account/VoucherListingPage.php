<?php

declare(strict_types=1);

namespace NetInventors\NetiNextEasyCoupon\Storefront\Page\Account;

use NetInventors\NetiNextEasyCoupon\Core\Content\EasyCoupon\Aggregate\EasyCouponTranslation\EasyCouponTranslationEntity;
use NetInventors\NetiNextEasyCoupon\Core\Content\EasyCoupon\EasyCouponEntity;
use Shopware\Storefront\Page\Page;

class VoucherListingPage extends Page
{
    /**
     * @var EasyCouponEntity[]
     */
    private $vouchers;

    /**
     * @var EasyCouponTranslationEntity[]
     */
    private $transactions;

    /**
     * @var array
     */
    private $restValues;

    /**
     * @var bool[]
     */
    private $showRedeemButton;

    /**
     * @return EasyCouponEntity[]
     */
    public function getVouchers(): array
    {
        return $this->vouchers;
    }

    /**
     * @param EasyCouponEntity[] $vouchers
     */
    public function setVouchers(array $vouchers): void
    {
        $this->vouchers = $vouchers;
    }

    public function getRestValues(): array
    {
        return $this->restValues;
    }

    public function setRestValues(array $restValues): void
    {
        $this->restValues = $restValues;
    }

    /**
     * @return EasyCouponTranslationEntity[]
     */
    public function getTransactions(): array
    {
        return $this->transactions;
    }

    /**
     * @param EasyCouponTranslationEntity[] $transactions
     */
    public function setTransactions(array $transactions): void
    {
        $this->transactions = $transactions;
    }

    public function getShowRedeemButton(): array
    {
        return $this->showRedeemButton;
    }

    public function setShowRedeemButton(array $showRedeemButton): void
    {
        $this->showRedeemButton = $showRedeemButton;
    }
}
