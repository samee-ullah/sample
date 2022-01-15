<?php

/**
 * @copyright  Copyright (c) 2020, Net Inventors GmbH
 * @category   Shopware
 * @author     drebrov
 */

declare(strict_types=1);

namespace NetInventors\NetiNextEasyCoupon\Storefront\Controller;

use NetInventors\NetiNextEasyCoupon\Service\PluginConfig;
use NetInventors\NetiNextEasyCoupon\Storefront\Page\Account\VoucherListingPageLoader;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Shopware\Storefront\Page\MetaInformation;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @RouteScope(scopes={"storefront"})
 */
class AccountVoucherListingController extends StorefrontController
{
    /**
     * @var PluginConfig
     */
    private $pluginConfig;

    /**
     * @var VoucherListingPageLoader
     */
    private $pageLoader;

    public function __construct(
        PluginConfig $pluginConfig,
        VoucherListingPageLoader $pageLoader
    ) {
        $this->pluginConfig = $pluginConfig;
        $this->pageLoader   = $pageLoader;
    }

    /**
     * @Route("/EasyCoupon/list", name="frontend.easy_coupon.list", methods={"GET"}, defaults={"XmlHttpRequest"=false})
     *
     * @param Request             $request
     * @param SalesChannelContext $context
     *
     * @return Response
     */
    public function listAction(Request $request, SalesChannelContext $context): Response
    {
        $customer = $context->getCustomer();

        if (null === $customer) {
            $message = $this->trans('neti-easy-coupon.store-front.warning.login-needed');
            $this->addFlash('warning', $message);

            return $this->redirectToRoute('frontend.account.login.page');
        }

        if (!$this->pluginConfig->isActive() || !$this->pluginConfig->isDisplayInAccount()) {
            $message = $this->trans('neti-easy-coupon.store-front.warning.deactivated');
            $this->addFlash('warning', $message);

            return $this->redirectToRoute('frontend.account.home.page');
        }

        $page = $this->pageLoader->load($request, $context);

        $page->setMetaInformation((new MetaInformation())->assign([
            'robots' => 'noindex,nofollow',
        ]));
        $page->getMetaInformation()->setMetaTitle(
            $this->trans('neti-easy-coupon.store-front.account.meta.title')
        );

        return $this->renderStorefront(
            '@NetiNextEasyCoupon/storefront/easy_coupon/account/list_voucher.html.twig',
            [
                'page' => $page,
            ]
        );
    }
}
