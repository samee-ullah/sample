<?php

declare(strict_types=1);

namespace NetInventors\NetiNextEasyCoupon\Core\Content\EasyCoupon\Rule;

use Shopware\Core\Checkout\Customer\CustomerEntity;
use Shopware\Core\Framework\Rule\Exception\UnsupportedOperatorException;
use Shopware\Core\Framework\Rule\Rule;
use Shopware\Core\Framework\Rule\RuleScope;
use Shopware\Core\Framework\Validation\Constraint\ArrayOfUuid;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\NotBlank;

class CustomerRule extends Rule
{
    /**
     * @var string[]
     */
    protected $customerIds;

    /**
     * @var string
     */
    protected $operator;

    public function __construct(string $operator = self::OPERATOR_EQ, ?array $customerIds = null)
    {
        parent::__construct();

        $this->operator    = $operator;
        $this->customerIds = $customerIds;
    }

    public function match(RuleScope $scope): bool
    {
        $customerId = null;
        $customer   = $scope->getSalesChannelContext()->getCustomer();

        if ($customer instanceof CustomerEntity) {
            $customerId = $customer->getId();
        }

        switch ($this->operator) {
            case self::OPERATOR_EQ:
                return \in_array($customerId, $this->customerIds, true);

            case self::OPERATOR_NEQ:
                return !$customerId || !\in_array($customerId, $this->customerIds, true);

            default:
                throw new UnsupportedOperatorException($this->operator, self::class);
        }
    }

    public function getConstraints(): array
    {
        return [
            'customerIds' => [ new NotBlank(), new ArrayOfUuid() ],
            'operator'    => [ new NotBlank(), new Choice([ self::OPERATOR_EQ, self::OPERATOR_NEQ ]) ],
        ];
    }

    public function getName(): string
    {
        return 'netiEasyCouponCustomer';
    }
}
