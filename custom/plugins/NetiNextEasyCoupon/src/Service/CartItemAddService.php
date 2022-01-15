<?php

/**
 * @copyright Copyright (c) 2020, Net Inventors GmbH
 * @category  Shopware
 * @author    mpeters
 */

declare(strict_types=1);

namespace NetInventors\NetiNextEasyCoupon\Service;

use NetInventors\NetiNextEasyCoupon\Service\LineItemExtension\LineItemExtensionBuilder;
use NetInventors\NetiNextEasyCoupon\Service\LineItemExtension\LineItemRequestService;
use NetInventors\NetiNextEasyCoupon\Struct\LineItemStruct;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\SalesChannel\AbstractCartItemAddRoute;
use Shopware\Core\Checkout\Cart\SalesChannel\CartResponse;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;

/**
 * @RouteScope(scopes={"store-api"})
 */
class CartItemAddService extends AbstractCartItemAddRoute
{
    /**
     * @var AbstractCartItemAddRoute
     */
    private $decoratedService;

    /**
     * @var LineItemRequestService
     */
    private $lineItemRequestService;

    /**
     * @var LineItemExtensionBuilder
     */
    private $lineItemExtensionBuilder;

    public function __construct(
        AbstractCartItemAddRoute $decoratedService,
        LineItemRequestService $lineItemRequestService,
        LineItemExtensionBuilder $lineItemExtensionBuilder
    ) {
        $this->decoratedService         = $decoratedService;
        $this->lineItemRequestService   = $lineItemRequestService;
        $this->lineItemExtensionBuilder = $lineItemExtensionBuilder;
    }

    public function getDecorated(): AbstractCartItemAddRoute
    {
        return $this->decoratedService;
    }

    /**
     * @param Request             $request
     * @param Cart                $cart
     * @param SalesChannelContext $context
     * @param LineItem[]|null     $items
     *
     * @return CartResponse
     */
    public function add(Request $request, Cart $cart, SalesChannelContext $context, ?array $items): CartResponse
    {
        $this->prepareItems($items);

        return $this->decoratedService->add($request, $cart, $context, $items);
    }

    /**
     * @param LineItem[]|null $items
     */
    protected function prepareItems(?array $items): void
    {
        if (!\is_array($items)) {
            return;
        }

        foreach ($items as $item) {
            if (null === $this->lineItemRequestService->fetchRequestLineItemData($item)) {
                continue;
            }

            $item = $this->lineItemExtensionBuilder->addExtension($item);

            if ($item->hasExtension(LineItemStruct::NAME)) {
                $item->setId(Uuid::randomHex());
            }
        }
    }
}
