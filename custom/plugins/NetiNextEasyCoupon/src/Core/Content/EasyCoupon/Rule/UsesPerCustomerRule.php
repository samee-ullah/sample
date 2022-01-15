<?php

declare(strict_types=1);

namespace NetInventors\NetiNextEasyCoupon\Core\Content\EasyCoupon\Rule;

use NetInventors\NetiNextEasyCoupon\Core\Content\EasyCoupon\EasyCouponEntity;
use NetInventors\NetiNextEasyCoupon\Core\Content\Transaction\TransactionEntity;
use NetInventors\NetiNextEasyCoupon\Service\Account\VoucherTransactionsService;
use Shopware\Core\Framework\Rule\Rule;
use Shopware\Core\Framework\Rule\RuleScope;
use Symfony\Component\Validator\Constraints\NotBlank;

class UsesPerCustomerRule extends Rule implements EasyCouponRuleInterface
{
    /**
     * @var int|null
     */
    protected $value;

    /**
     * @var VoucherTransactionsService
     */
    private $transactionsService;

    public function __construct(
        VoucherTransactionsService $transactionsService,
        ?int $value = null
    ) {
        parent::__construct();

        $this->transactionsService = $transactionsService;
        $this->value               = $value;
    }

    public function match(RuleScope $scope): bool
    {
        if (!$scope instanceof EasyCouponRuleScope) {
            return false;
        }

        $easyCoupon   = $scope->getEasyCoupon();
        if (
            EasyCouponEntity::VOUCHER_TYPE_INDIVIDUAL === $easyCoupon->getVoucherType()
            && EasyCouponEntity::VALUE_TYPE_PERCENTAL === $easyCoupon->getValueType()
        ) {
            $transactions      = $this->transactionsService->getTransactionsForIndividualVoucher($easyCoupon);
            $countTransactions =
                $transactions->filterByProperty('transactionType', TransactionEntity::TYPE_REDEMPTION_BY_ADMIN)->count()
                + $transactions->filterByProperty('transactionType', TransactionEntity::TYPE_REDEMPTION_IN_STOREFRONT)
                    ->count();

            if ($this->value <= $countTransactions) {
                return false;
            }
        }

        // If the user is not logged in, the voucher is added to cart
        if (!$customer = $scope->getSalesChannelContext()->getCustomer()) {
            return true;
        }

        $transactions = $this->transactionsService->getTransactionsForVouchers(
            [ $easyCoupon->getId() ],
            $customer->getId()
        );

        $transactions = $transactions->filter(
            static function (TransactionEntity $transaction) {
                return TransactionEntity::TYPE_REDEMPTION_IN_STOREFRONT === $transaction->getTransactionType();
            }
        );

        return 0 === $transactions->count()
            || $this->getValue() > $transactions->count();
    }

    public function getConstraints(): array
    {
        return [
            'value' => [ new NotBlank() ],
        ];
    }

    public function getName(): string
    {
        return 'netiEasyCouponUsesPerCustomer';
    }

    public function getValue(): int
    {
        return $this->value ?? 0;
    }
}
