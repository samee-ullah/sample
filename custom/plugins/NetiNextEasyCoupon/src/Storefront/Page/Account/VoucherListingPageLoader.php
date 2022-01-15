<?php

declare(strict_types=1);

namespace NetInventors\NetiNextEasyCoupon\Storefront\Page\Account;

use NetInventors\NetiNextEasyCoupon\Core\Content\EasyCoupon\EasyCouponEntity;
use NetInventors\NetiNextEasyCoupon\Core\Content\Transaction\TransactionEntity;
use NetInventors\NetiNextEasyCoupon\Service\Account\VoucherTransactionsService;
use NetInventors\NetiNextEasyCoupon\Service\PluginConfig;
use NetInventors\NetiNextEasyCoupon\Service\VoucherRedemption\ValidationProcessor;
use NetInventors\NetiNextEasyCoupon\Service\VoucherRedemption\Validator\Error\EmptyCartError;
use NetInventors\NetiNextEasyCoupon\Service\VoucherRedemption\Validator\PaymentActivationStateValidator;
use NetInventors\NetiNextEasyCoupon\Service\VoucherRedemption\Validator\ValidationContext;
use Shopware\Core\Checkout\Customer\CustomerEntity;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Page\GenericPageLoader;
use Shopware\Storefront\Page\Page;
use Symfony\Component\HttpFoundation\Request;
use Shopware\Core\Checkout\Cart\SalesChannel\CartService;

class VoucherListingPageLoader
{
    /**
     * @var GenericPageLoader
     */
    private $genericLoader;

    /**
     * @var VoucherTransactionsService
     */
    private $voucherTransactionsService;

    /**
     * @var PluginConfig
     */
    private $pluginConfig;

    /**
     * @var PaymentActivationStateValidator
     */
    private $paymentActivationStateValidator;

    /**
     * @var ValidationProcessor
     */
    private $validationProcessor;

    public function __construct(
        GenericPageLoader $genericLoader,
        VoucherTransactionsService $voucherTransactionsService,
        PluginConfig $pluginConfig,
        PaymentActivationStateValidator $paymentActivationStateValidator,
        ValidationProcessor $validationProcessor,
        CartService $cartService
    ) {
        $this->genericLoader                   = $genericLoader;
        $this->voucherTransactionsService      = $voucherTransactionsService;
        $this->pluginConfig                    = $pluginConfig;
        $this->paymentActivationStateValidator = $paymentActivationStateValidator;
        $this->validationProcessor             = $validationProcessor;
        $this->cartService                     = $cartService;
    }

    public function load(Request $request, SalesChannelContext $context): Page
    {
        $page = $this->genericLoader->load($request, $context);
        $page = VoucherListingPage::createFrom($page);

        /** @var CustomerEntity $customer */
        $customer   = $context->getCustomer();
        $vouchers   =
            $this->voucherTransactionsService->getVouchersForCustomer($customer->getId(), $context->getContext());
        $voucherIds = [];
        $showRedeemButton = [];
        /** @var EasyCouponEntity $voucher */
        foreach ($vouchers as $voucher) {
            $voucherId              = $voucher->getId();
            $voucherIds[$voucherId] = $voucherId;
            // Check payment status
            $notPurchasedOrMatchesPaymentActivationState =
                $this->paymentActivationStateValidator->notPurchasedOrMatchesPaymentActivationState(
                    $this->pluginConfig->getVoucherActivatePaymentStatus(),
                    $voucher,
                    $context->getContext()
                );
            $voucher->setActive($notPurchasedOrMatchesPaymentActivationState);

            $cart = $this->cartService->getCart($context->getToken(), $context);

            /** @var  $validationResult */
            $validationResult =
                $this->validationProcessor->validate(new ValidationContext($voucher, $cart, $context));

            $showRedeemButton[$voucherId] = !$validationResult->hasErrors();
            $errors                       = $validationResult->getErrors();

            if ($errors->count() === 1 && $errors->first()->getMessageKey() === EmptyCartError::KEY) {
                $showRedeemButton[$voucherId] = 'empty-cart';
            }
        }

        $transactions =
            $this->voucherTransactionsService->getTransactionsForVouchers(
                $voucherIds
            )->getElements();

        /** @var TransactionEntity $transaction */
        foreach ($transactions as $transaction) {
            if (
                EasyCouponEntity::VOUCHER_TYPE_GENERAL !== $transaction->getEasyCoupon()->getVoucherType()
                || TransactionEntity::TYPE_CREATED_BY_ADMIN === $transaction->getTransactionType()
                || TransactionEntity::TYPE_CREATED_BY_PURCHASE === $transaction->getTransactionType()
                || $customer->getId() === $transaction->getCustomerId()
            ) {
                continue;
            }

            unset($transactions[$transaction->getId()]);
        }

        $cachedValues = $this->voucherTransactionsService->getRestValueForVouchers(
            $vouchers,
            $transactions
        );

        $restValues = [];

        /** @var EasyCouponEntity $voucher */
        foreach($vouchers as $voucher) {
            $voucherId = $voucher->getId();
            if($voucher->getValueType() === EasyCouponEntity::VALUE_TYPE_ABSOLUTE) {
                if (isset($cachedValues[$voucherId])) {
                    if ($voucher->isDiscardRemaining()) {
                        $restValues[$voucherId] = 0.0;
                        continue;
                    }

                    $restValues[$voucherId] = $cachedValues[$voucherId];
                    continue;
                }

                $restValues[$voucherId] = $voucher->getValue();
            }
        }

        $page->setVouchers($vouchers);
        $page->setTransactions($transactions);
        $page->setRestValues($restValues);
        $page->setShowRedeemButton($showRedeemButton);

        return $page;
    }
}
