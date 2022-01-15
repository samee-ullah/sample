<?php declare(strict_types=1);
/**
 * @copyright Copyright (c) 2021, Net Inventors GmbH
 * @category  Shopware
 * @author    mpeters
 */

namespace NetInventors\NetiNextEasyCoupon\Service\VoucherRedemption\Validator;

use NetInventors\NetiNextEasyCoupon\Core\Checkout\Cart\AbstractCartProcessor;
use NetInventors\NetiNextEasyCoupon\Service\PluginConfig;
use NetInventors\NetiNextEasyCoupon\Service\VoucherRedemption\ValidationResult;
use NetInventors\NetiNextEasyCoupon\Service\VoucherRedemption\Validator\Error\NotCombinableError;
use Shopware\Core\Checkout\Cart\LineItem\LineItemCollection;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;

class VoucherCombinableValidator implements ValidatorInterface
{
    /**
     * @var PluginConfig
     */
    protected $pluginConfig;

    /**
     * @var EntityRepositoryInterface
     */
    protected $easyCouponRepository;

    /**
     * VoucherCombine constructor.
     *
     * @param PluginConfig              $pluginConfig
     * @param EntityRepositoryInterface $easyCouponRepository
     */
    public function __construct(
        PluginConfig $pluginConfig,
        EntityRepositoryInterface $easyCouponRepository
    ) {
        $this->pluginConfig         = $pluginConfig;
        $this->easyCouponRepository = $easyCouponRepository;
    }

    public function validate(ValidationContext $validationContext, ValidationResult $validationResult): bool
    {
        $voucher            = $validationContext->getEasyCoupon();
        $cart               = $validationContext->getCart();
        $voucherLineItems   = $cart->getLineItems()->filterType(AbstractCartProcessor::EASY_COUPON_LINE_ITEM_TYPE);
        $currentLineItemKey = $this->buildKey($voucher->getCode());

        if (2 > $voucherLineItems->count() || !isset($voucherLineItems->getElements()[$currentLineItemKey])) {
            return true;
        }

        if (!$this->pluginConfig->isAllowCombineVouchers() || !$voucher->isCombineVouchers()) {
            $validationResult->addErrors(new NotCombinableError($voucher->getCode()));

            return false;
        }

        $vouchersWithNoCombination = $this->getNotCombinableVouchers($voucherLineItems, $validationContext->getSalesChannelContext()->getContext());
        if (0 < $vouchersWithNoCombination->count()) {
            $validationResult->addErrors(new NotCombinableError($voucher->getCode()));

            return false;
        }

        return true;
    }

    protected function buildKey(string $voucherCode): string
    {
        return AbstractCartProcessor::EASY_COUPON_LINE_ITEM_TYPE . '-' . $voucherCode;
    }

    protected function getNotCombinableVouchers(LineItemCollection $voucherLineItems, Context $context): EntitySearchResult
    {
        $voucherCodes = \array_values($voucherLineItems->getReferenceIds());
        $criteria     = new Criteria();
        $criteria->addFilter(new MultiFilter(MultiFilter::CONNECTION_AND, [
            new EqualsAnyFilter('code', $voucherCodes),
            new EqualsFilter('combineVouchers', false),
            new EqualsFilter('deleted', false)
        ]));

        return $this->easyCouponRepository->search($criteria, $context);
    }
}
