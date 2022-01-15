<?php

declare(strict_types=1);

namespace NetInventors\NetiNextEasyCoupon\Service\Api;

use NetInventors\NetiNextEasyCoupon\Core\Content\Transaction\TransactionEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Exception\EntityNotFoundException;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\Currency\CurrencyEntity;

class ItemBuilder
{
    protected EntityRepositoryInterface $currencyRepo;

    public function __construct(EntityRepositoryInterface $currencyRepo)
    {
        $this->currencyRepo = $currencyRepo;
    }

    public function buildBookVoucherItem(array $data, Context $context): array
    {
        $currency = $this->getCurrencyEntity($data['currencyId'], $context);
        $factor   = $currency->getFactor();

        return [
            'transactionType' => TransactionEntity::TYPE_REDEMPTION_BY_API,
            'value'           => $data['value'] / $factor,
            'internComment'   => $data['internComment'] ?? null,
            'currencyFactor'  => $factor,
            'currencyId'      => $data['currencyId'],
            'easyCouponId'    => $data['id'],
            'customerId'      => $data['customerId'] ?? null,
            'orderId'         => $data['orderId'] ?? null,
            'orderVersionId'  => isset($data['orderVersionId']) && isset($data['orderId']) ? $data['orderVersionId'] : null,
            'salesChannelId'  => $data['salesChannelId'] ?? null,
            'userId'          => $context->getSource()->getUserId(),
        ];
    }

    public function buildCreateVoucherItem(array $data, Context $context): array
    {
        $currency = $this->getCurrencyEntity($data['currencyId'], $context);
        $factor   = $currency->getFactor();
        $value    = $data['value'] / $factor;

        return [
            'deleted'                  => false,
            'active'                   => $data['active'],
            'voucherType'              => $data['voucherType'],
            'code'                     => $data['code'],
            'value'                    => $value,
            'valueType'                => $data['valueType'],
            'discardRemaining'         => $data['discardRemaining'],
            'shippingCharge'           => false,
            'excludeFromShippingCosts' => $data['excludeFromShippingCosts'],
            'noDeliveryCharge'         => $data['noDeliveryCharge'],
            'customerGroupCharge'      => $data['customerGroupCharge'],
            'mailSent'                 => false,
            'comment'                  => $data['comment'] ?? null,
            'currencyFactor'           => $factor,
            'orderPositionNumber'      => $data['orderPositionNumber'],
            'maxRedemptionValue'       => $data['maxRedemptionValue'] ?? null,
            'combineVouchers'          => $data['combineVouchers'],
            'currencyId'               => $data['currencyId'],
            'productId'                => $data['productId'] ?? null,
            'productVersionId'         => isset($data['productVersionId']) && isset($data['productId']) ? $data['productVersionId'] : null,
            'taxId'                    => $data['taxId'] ?? null,
            'validUntil'               => $data['validUntil'] ?? null,
            'conditions'               => $data['conditions'] ?? null,
            'translations'             => $data['translations'] ?? null,
            'transactions'             => [
                [
                    'transactionType' => TransactionEntity::TYPE_CREATED_BY_API,
                    'value'           => $value,
                    'currencyFactor'  => $factor,
                    'currencyId'      => $currency->getId(),
                    'userId'          => $context->getSource()->getUserId(),
                ]
            ]
        ];
    }

    /**
     * @param string $currencyId
     * @param Context $context
     * @return CurrencyEntity
     */
    protected function getCurrencyEntity(string $currencyId, Context $context): CurrencyEntity
    {
        $result = $this->currencyRepo->search(new Criteria([$currencyId]), $context);
        if (0 === $result->count()) {
            throw new EntityNotFoundException('currency', $currencyId);
        }

        return $result->first();
    }
}
