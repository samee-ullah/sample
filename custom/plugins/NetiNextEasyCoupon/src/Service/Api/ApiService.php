<?php

declare(strict_types=1);

namespace NetInventors\NetiNextEasyCoupon\Service\Api;

use NetInventors\NetiNextEasyCoupon\Core\Content\EasyCoupon\EasyCouponEntity;
use NetInventors\NetiNextEasyCoupon\Exception\Api\InvalidCode;
use NetInventors\NetiNextEasyCoupon\Exception\Api\InvalidCouponValue;
use NetInventors\NetiNextEasyCoupon\Exception\Api\InvalidValueType;
use NetInventors\NetiNextEasyCoupon\Exception\Api\InvalidVoucherType;
use NetInventors\NetiNextEasyCoupon\Exception\Api\MaxRedemptionValueExceeded;
use NetInventors\NetiNextEasyCoupon\Exception\Api\MissingParameter;
use NetInventors\NetiNextEasyCoupon\Exception\Api\NoResidualValue;
use NetInventors\NetiNextEasyCoupon\Exception\Api\ValidUntilExceeded;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Exception\EntityNotFoundException;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;

class ApiService
{
    protected ValidateService           $validateService;

    protected EntityRepositoryInterface $easyCouponRepo;

    protected ItemBuilder               $itemBuilder;

    protected EntityRepositoryInterface $transactionRepo;

    public function __construct(
        ValidateService $validateService,
        EntityRepositoryInterface $easyCouponRepo,
        ItemBuilder $itemBuilder,
        EntityRepositoryInterface $transactionRepo
    ) {
        $this->validateService = $validateService;
        $this->easyCouponRepo  = $easyCouponRepo;
        $this->itemBuilder     = $itemBuilder;
        $this->transactionRepo = $transactionRepo;
    }

    /**
     * @param array   $data
     * @param Context $context
     *
     * @throws MissingParameter
     * @throws InvalidVoucherType
     * @throws \ReflectionException
     * @throws InvalidValueType
     * @throws InvalidCode
     */
    public function createVoucher(array $data, Context $context): void
    {
        $this->validateService->validateCreateVoucherParams($data, $context);

        $this->easyCouponRepo->create([ $this->itemBuilder->buildCreateVoucherItem($data, $context) ], $context);
    }

    /**
     * @param array   $data
     * @param Context $context
     *
     * @throws MissingParameter
     * @throws InvalidCouponValue
     * @throws MaxRedemptionValueExceeded
     * @throws NoResidualValue
     * @throws ValidUntilExceeded
     */
    public function bookValue(array $data, Context $context): void
    {
        $this->validateService->validateBookValueParams($data);

        $coupon = $this->easyCouponRepo->search(new Criteria([$data['id']]), $context)->first();
        if (!$coupon instanceof EasyCouponEntity) {
            throw new EntityNotFoundException(EasyCouponEntity::class, $data['id']);
        }

        if (!isset($data['force']) || true !== $data['force']) {
            $this->validateService->validateVoucher($coupon, $data);
        }

        $this->transactionRepo->create([ $this->itemBuilder->buildBookVoucherItem($data, $context) ], $context);
    }

    /**
     * @param string  $voucherId
     * @param Context $context
     *
     * @throws ValidUntilExceeded
     */
    public function validateVoucher(string $voucherId, Context $context): void
    {
        $coupon = $this->easyCouponRepo->search(new Criteria([$voucherId]), $context)->first();
        if (!$coupon instanceof EasyCouponEntity) {
            throw new EntityNotFoundException(EasyCouponEntity::class, $voucherId);
        }

        $this->validateService->validateValidUntil($coupon);
    }
}
