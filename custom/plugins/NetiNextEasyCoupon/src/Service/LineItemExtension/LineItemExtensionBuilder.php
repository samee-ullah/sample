<?php

/**
 * @copyright Copyright (c) 2020, Net Inventors GmbH
 * @category  Shopware
 * @author    mpeters
 */

declare(strict_types=1);

namespace NetInventors\NetiNextEasyCoupon\Service\LineItemExtension;

use NetInventors\NetiNextEasyCoupon\Struct\LineItemStruct;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Framework\Struct\Struct;

class LineItemExtensionBuilder
{
    /**
     * @var LineItemRequestService
     */
    protected $lineItemRequestService;

    public function __construct(
        LineItemRequestService $lineItemRequestService
    ) {
        $this->lineItemRequestService = $lineItemRequestService;
    }

    public function addExtension(LineItem $lineItem, array $options = []): LineItem
    {
        $extension = $this->buildExtension($lineItem, $options);
        if (null === $extension) {
            return $lineItem;
        }

        $lineItem->addExtension(LineItemStruct::NAME, $extension);
        $lineItem->setPayloadValue(
            LineItemStruct::PAYLOAD_NAME,
            $extension->getVars()
        );

        return $lineItem;
    }

    protected function buildExtension(LineItem $lineItem, array $options = []): ?Struct
    {
        if ([] === $options) {
            $options = $this->lineItemRequestService->fetchRequestLineItemData($lineItem);
            if (null === $options) {
                return null;
            }
        }

        $extension = $this->fetchExtension($lineItem);
        if (null === $extension) {
            return null;
        }

        $extension->assign($options);

        return $extension;
    }

    private function fetchExtension(LineItem $lineItem): ?Struct
    {
        if (!$lineItem->hasExtension(LineItemStruct::NAME)) {
            return new LineItemStruct();
        }

        return $lineItem->getExtension(LineItemStruct::NAME);
    }
}
