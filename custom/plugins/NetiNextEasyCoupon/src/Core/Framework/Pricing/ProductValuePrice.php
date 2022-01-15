<?php

/**
 * @copyright Copyright (c) 2020, Net Inventors GmbH
 * @category  Shopware
 * @author    mpeters
 */

declare(strict_types=1);

namespace NetInventors\NetiNextEasyCoupon\Core\Framework\Pricing;

use Shopware\Core\Framework\DataAbstractionLayer\Pricing\Price;
use Shopware\Core\System\Currency\CurrencyEntity;

class ProductValuePrice extends Price
{
    /**
     * @var ?float
     */
    protected $from;

    /**
     * @var ?float
     */
    protected $to;

    /**
     * @var array
     */
    protected $selectableValues;

    public function __construct(
        string $currencyId,
        float $net,
        float $gross,
        bool $linked,
        ?Price $listPrice = null,
        ?float $from = null,
        ?float $to = null,
        array $selectableValues = []
    ) {
        parent::__construct($currencyId, $net, $gross, $linked, $listPrice);

        $this->from             = $from;
        $this->to               = $to;
        $this->selectableValues = $selectableValues;
    }

    public function transformToFactor(CurrencyEntity $currency): self
    {
        $factor           = $currency->getFactor();
        $selectableValues = $this->selectableValues;
        foreach ($selectableValues as &$value) {
            $value *= $factor;
        }
        unset($value);

        return new self(
            $currency->getId(),
            $this->net * $factor,
            $this->gross * $factor,
            $this->linked,
            $this->listPrice,
            (\is_float($this->from) ? $this->from * $factor : null),
            (\is_float($this->to) ? $this->to * $factor : null),
            $selectableValues
        );
    }

    public function getMaxSelectableValue(): float
    {
        $this->checkSelectableValues();

        $responseValue = 0.00;
        foreach ($this->selectableValues as $value) {
            if ($value > $responseValue) {
                $responseValue = $value;
            }
        }

        return (float) $responseValue;
    }

    public function getMinSelectableValue(): float
    {
        $this->checkSelectableValues();

        $responseValue = 0.00;
        foreach ($this->selectableValues as $value) {
            if (0.00 === $responseValue || $value < $responseValue) {
                $responseValue = $value;
            }
        }

        return (float) $responseValue;
    }

    public function getFrom(): ?float
    {
        return $this->from;
    }

    public function getTo(): ?float
    {
        return $this->to;
    }

    public function getSelectableValues(): array
    {
        return $this->selectableValues;
    }

    private function checkSelectableValues(): void
    {
        if (!\is_array($this->selectableValues) || [] === $this->selectableValues) {
            throw new \InvalidArgumentException('SelectablesValues has no elements');
        }
    }
}
