<?php

/**
 * @copyright Copyright (c) 2020, Net Inventors GmbH
 * @category  Shopware
 * @author    mpeters
 */

declare(strict_types=1);

namespace NetInventors\NetiNextEasyCoupon\Service\LineItemExtension;

use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Symfony\Component\HttpFoundation\RequestStack;

class LineItemRequestService
{
    /**
     * @var RequestStack
     */
    private $requestStack;

    public function __construct(
        RequestStack $requestStack
    ) {
        $this->requestStack = $requestStack;
    }

    public function fetchRequestLineItemData(LineItem $lineItem): ?array
    {
        $requestLineItem = $this->fetchRequestLineItem($lineItem);

        if (\is_array($requestLineItem) && isset($requestLineItem['easyCoupon']['voucherValue'])) {
            if (-1.00 === (float) $requestLineItem['easyCoupon']['voucherValue']) {
                return null;
            }

            $requestLineItem['easyCoupon']['voucherValue'] = (float) $requestLineItem['easyCoupon']['voucherValue'];
        }

        return $requestLineItem['easyCoupon'] ?? null;
    }

    private function fetchRequestLineItem(LineItem $lineItem): ?array
    {
        $request = $this->requestStack->getCurrentRequest();
        if (null === $request) {
            return null;
        }

        $requestLineItems = $request->request->get('lineItems');

        return $requestLineItems[$lineItem->getId()] ?? null;
    }
}
