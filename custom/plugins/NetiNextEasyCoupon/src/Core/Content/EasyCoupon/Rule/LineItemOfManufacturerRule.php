<?php

declare(strict_types=1);

namespace NetInventors\NetiNextEasyCoupon\Core\Content\EasyCoupon\Rule;

use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\Rule\CartRuleScope;
use Shopware\Core\Framework\Rule\Exception\UnsupportedOperatorException;
use Shopware\Core\Framework\Rule\Rule;
use Shopware\Core\Framework\Rule\RuleScope;
use Shopware\Core\Framework\Validation\Constraint\ArrayOfUuid;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\NotBlank;

class LineItemOfManufacturerRule extends Rule
{
    /**
     * @var array
     */
    protected $manufacturerIds;

    /**
     * @var string
     */
    protected $operator;

    public function __construct(string $operator = self::OPERATOR_EQ, array $manufacturerIds = [])
    {
        parent::__construct();

        $this->manufacturerIds = $manufacturerIds;
        $this->operator        = $operator;
    }

    public function getName(): string
    {
        return 'netiEasyCouponCartLineItemOfManufacturer';
    }

    public function match(RuleScope $scope): bool
    {
        if (!$scope instanceof CartRuleScope) {
            return false;
        }

        foreach ($scope->getCart()->getLineItems() as $lineItem) {
            if (!$this->matchesOneOfManufacturers($lineItem)) {
                return false;
            }
        }

        return true;
    }

    public function getConstraints(): array
    {
        return [
            'manufacturerIds' => [ new NotBlank(), new ArrayOfUuid() ],
            'operator'        => [
                new NotBlank(),
                new Choice(
                    [
                        self::OPERATOR_EQ,
                        self::OPERATOR_NEQ,
                    ]
                ),
            ],
        ];
    }

    private function matchesOneOfManufacturers(LineItem $lineItem): bool
    {
        $manufacturerId = (string) $lineItem->getPayloadValue('manufacturerId');

        switch ($this->operator) {
            case self::OPERATOR_EQ:
                return \in_array($manufacturerId, $this->manufacturerIds, true);

            case self::OPERATOR_NEQ:
                return !\in_array($manufacturerId, $this->manufacturerIds, true);

            default:
                throw new UnsupportedOperatorException($this->operator, self::class);
        }
    }
}
