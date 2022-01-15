<?php

declare(strict_types=1);

namespace NetInventors\NetiNextEasyCoupon\Storefront\Controller;

use NetInventors\NetiNextEasyCoupon\Core\Content\Product\Cart\EasyCouponError;
use NetInventors\NetiNextEasyCoupon\Service\PluginConfig;
use NetInventors\NetiNextEasyCoupon\Service\VoucherRedemption\ValidationProcessor;
use NetInventors\NetiNextEasyCoupon\Service\VoucherRedemption\Validator\ValidationContext;
use NetInventors\NetiNextEasyCoupon\Service\VoucherRedemptionService;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\SalesChannel\CartService;
use Shopware\Core\Checkout\Promotion\Cart\PromotionItemBuilder;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @RouteScope(scopes={"storefront"})
 */
class VoucherLinkController extends StorefrontController
{
    private VoucherRedemptionService $voucherRedemptionService;

    private PluginConfig             $pluginConfig;

    private CartService              $cartService;

    private ValidationProcessor      $validationProcessor;

    public function __construct(
        VoucherRedemptionService $voucherRedemptionService,
        PluginConfig $pluginConfig,
        CartService $cartService,
        ValidationProcessor $validationProcessor
    ) {
        $this->voucherRedemptionService = $voucherRedemptionService;
        $this->pluginConfig             = $pluginConfig;
        $this->cartService              = $cartService;
        $this->validationProcessor      = $validationProcessor;
    }

    /**
     * @Route("/EasyCoupon/add/{code}", name="frontend.easy_coupon.add", methods={"GET"},
     *                                  defaults={"XmlHttpRequest"=false})
     *
     * @param Request             $request
     * @param SalesChannelContext $salesChannelContext
     *
     * @return Response
     */
    public function addAction(Request $request, SalesChannelContext $salesChannelContext, string $code): Response
    {
        if (!$this->pluginConfig->isActive()) {
            return $this->redirectToRoute('frontend.home.page');
        }

        $cart    = $this->cartService->getCart($salesChannelContext->getToken(), $salesChannelContext);
        $voucher = $this->voucherRedemptionService->getEasyCoupon($code, $salesChannelContext);
        $message = $this->trans('neti-easy-coupon.messages.link-voucher.added-later');
        $type    = $this::SUCCESS;

        if (null === $voucher) {
            $this->addFlash(
                $this::DANGER,
                $this->trans('neti-easy-coupon.messages.link-voucher.voucher-not-found', [ 'voucherCode' => $code ])
            );

            return $this->redirectToRoute('frontend.home.page');
        }

        $session         = $request->getSession();
        $sessionVouchers = $session->get('EasyCouponVoucherCodes');

        if (null === $sessionVouchers) {
            $sessionVouchers = [];
        }

        try {
            $itemBuilder = new PromotionItemBuilder();

            $lineItem = $itemBuilder->buildPlaceholderItem($code);
            $this->cartService->add($cart, $lineItem, $salesChannelContext);

            if (1 === $cart->getLineItems()->count()) {
                //add a fake line item to trick the validator to pass the cart validation
                $fakeItem = new LineItem('netiECfakeItem', LineItem::PRODUCT_LINE_ITEM_TYPE);
                $cart->add($fakeItem);
            }

            $validationResult =
                $this->validationProcessor->validate(new ValidationContext($voucher, $cart, $salesChannelContext));
            $cart->getLineItems()->remove('netiECfakeItem');

            if ($validationResult->hasErrors()) {
                $validationError = $validationResult->getErrors()->first();
                $message         =
                    $this->trans('checkout.' . $validationError->getMessageKey(), [ '%id%' => $code ]);

                $this->addFlash($this::DANGER, $message);

                return $this->redirectToRoute('frontend.home.page');
            }
        } catch (EasyCouponError $easyCouponError) {
            $type    = $this::DANGER;
            $message = $this->trans('checkout.' . $easyCouponError->getMessageKey());
        }

        $sessionVouchers[$voucher->getId()] = $voucher->getCode();
        $session->set('EasyCouponVoucherCodes', $sessionVouchers);

        if (1 < $cart->getLineItems()->count() && $type === $this::SUCCESS) {
            $message = $this->trans('neti-easy-coupon.messages.link-voucher.added-now');;
        }

        $this->addFlash($type, $message);

        return $this->redirectToRoute('frontend.home.page');
    }
}
