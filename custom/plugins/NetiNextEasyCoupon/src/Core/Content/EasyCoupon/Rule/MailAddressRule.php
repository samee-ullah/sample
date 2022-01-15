<?php

declare(strict_types=1);

namespace NetInventors\NetiNextEasyCoupon\Core\Content\EasyCoupon\Rule;

use Shopware\Core\Framework\Rule\Rule;
use Shopware\Core\Framework\Rule\RuleScope;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\NotBlank;
use Shopware\Core\Framework\Validation\Constraint\ArrayOfType;
use Shopware\Core\Framework\Rule\Exception\UnsupportedOperatorException;

class MailAddressRule extends Rule
{
    /**
     * @var string[]|null
     */
    protected ?array $emails;

    protected string $operator;

    public function __construct(string $operator = self::OPERATOR_EQ, ?array $emails = null)
    {
        parent::__construct();

        $this->operator = $operator;
        $this->emails   = $emails;
    }

    public function match(RuleScope $scope): bool
    {
        $customer = $scope->getSalesChannelContext()->getCustomer();

        if (null === $customer) {
            return true;
        }

        switch ($this->operator) {
            case self::OPERATOR_EQ:
                return \in_array($customer->getEmail(), $this->emails, true);

            case self::OPERATOR_NEQ:
                return !\in_array($customer->getEmail(), $this->emails, true);

            default:
                throw new UnsupportedOperatorException($this->operator, self::class);
        }
    }

    public function getConstraints(): array
    {
        return [
            'emails'   => [ new NotBlank(), new ArrayOfType('string') ],
            'operator' => [ new NotBlank(), new Choice([ self::OPERATOR_EQ, self::OPERATOR_NEQ ]) ],
        ];
    }

    public function getName(): string
    {
        return 'netiEasyCouponMailAddress';
    }
}
