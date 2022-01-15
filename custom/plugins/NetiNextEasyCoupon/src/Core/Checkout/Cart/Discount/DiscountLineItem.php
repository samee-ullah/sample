<?php

declare(strict_types=1);

namespace NetInventors\NetiNextEasyCoupon\Core\Checkout\Cart\Discount;

use Shopware\Core\Checkout\Cart\Exception\PayloadKeyNotFoundException;
use Shopware\Core\Checkout\Cart\Price\Struct\PriceDefinitionInterface;

class DiscountLineItem
{
    /**
     * @var string
     */
    private $label;

    /**
     * @var PriceDefinitionInterface
     */
    private $priceDefinition;

    /**
     * @var array
     */
    private $payload;

    /**
     * @var string|null
     */
    private $code;

    /**
     * @var int
     */
    private $type;

    public function __construct(string $label, PriceDefinitionInterface $priceDefinition, array $payload, ?string $code)
    {
        $this->label           = $label;
        $this->priceDefinition = $priceDefinition;
        $this->code            = $code;
        $this->type            = $payload['discountType'];
        $this->payload         = $payload;
    }

    /**
     * Gets the text label of the discount item
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * Gets the original price definition
     * of this discount item
     */
    public function getPriceDefinition(): PriceDefinitionInterface
    {
        return $this->priceDefinition;
    }

    /**
     * Gets the type of the discount
     */
    public function getType(): int
    {
        return $this->type;
    }

    /**
     * Gets the discount payload data
     */
    public function getPayload(): array
    {
        return $this->payload;
    }

    /**
     * @param string $key
     *
     * @return string|array
     * @throws PayloadKeyNotFoundException
     */
    public function getPayloadValue(string $key)
    {
        if (!$this->hasPayloadValue($key)) {
            throw new PayloadKeyNotFoundException($key, $this->getLabel());
        }

        return $this->payload[$key];
    }

    public function hasPayloadValue(string $key): bool
    {
        return isset($this->payload[$key]);
    }

    /**
     * Gets the code of the discount if existing.
     */
    public function getCode(): ?string
    {
        return $this->code;
    }
}
