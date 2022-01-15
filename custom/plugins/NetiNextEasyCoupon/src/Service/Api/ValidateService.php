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
use NetInventors\NetiNextEasyCoupon\Service\Account\VoucherTransactionsService;
use NetInventors\NetiNextEasyCoupon\Service\Repository\VoucherRepository;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Pricing\PriceCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;

class ValidateService
{
    protected VoucherRepository        $voucherRepository;

    protected VoucherTransactionsService $voucherTransactionsService;

    public function __construct(VoucherRepository $voucherRepository, VoucherTransactionsService $voucherTransactionsService)
    {
        $this->voucherRepository          = $voucherRepository;
        $this->voucherTransactionsService = $voucherTransactionsService;
    }

    /**
     * @param EasyCouponEntity $coupon
     * @param array            $data
     *
     * @throws InvalidCouponValue
     * @throws MaxRedemptionValueExceeded
     * @throws NoResidualValue
     * @throws ValidUntilExceeded
     */
    public function validateVoucher(EasyCouponEntity $coupon, array $data): void
    {
        $redeemValue = $data['value'];

        $this->validateValidUntil($coupon);
        $this->validateMaxRedemptionValue($coupon, $redeemValue, $data['currencyId']);
        $this->validateCouponValue($coupon, $redeemValue);
        $this->validateResidualValue($coupon, $data);
    }

    /**
     * @param EasyCouponEntity $coupon
     * @param array            $data
     *
     * @throws NoResidualValue
     */
    private function validateResidualValue(EasyCouponEntity $coupon, array $data): void
    {
        $transactions = $this->getTransactions($coupon, $data);
        if ($transactions instanceof EntitySearchResult) {
            if (
                EasyCouponEntity::VALUE_TYPE_PERCENTAL === $coupon->getValueType()
                && 0 < $transactions->count()
            ) {
                throw new NoResidualValue('Voucher has no residual value');
            } elseif (EasyCouponEntity::VALUE_TYPE_ABSOLUTE === $coupon->getValueType()) {
                if ($coupon->isDiscardRemaining() && 0 < $transactions->count()) {
                    throw new NoResidualValue('Voucher has no residual value');
                }

                $residualValue = $this->voucherTransactionsService->getRestValueForVouchers([$coupon], $transactions->getElements());
                if ($residualValue < $data['value']) {
                    throw new NoResidualValue('Voucher has no residual value');
                }
            }
        }
    }

    private function getTransactions(EasyCouponEntity $coupon, array $data): ?EntitySearchResult
    {
        $transactions = null;
        if (EasyCouponEntity::VOUCHER_TYPE_GENERAL === $coupon->getVoucherType()) {
            if (isset($data['customerId']) && \is_string($data['customerId'])) {
                $transactions = $this->voucherTransactionsService->getTransactionsForGeneralVoucher($coupon, $data['customerId']);
            }

        } elseif (EasyCouponEntity::VOUCHER_TYPE_INDIVIDUAL === $coupon->getVoucherType()) {
            $transactions = $this->voucherTransactionsService->getTransactionsForIndividualVoucher($coupon);
        }

        return $transactions;
    }

    /**
     * @param EasyCouponEntity $coupon
     * @param float            $redeemValue
     *
     * @throws InvalidCouponValue
     */
    private function validateCouponValue(EasyCouponEntity $coupon, float $redeemValue): void
    {
        $couponValue = $coupon->getValue() * $coupon->getCurrencyFactor();
        if ($couponValue < $redeemValue) {
            throw new InvalidCouponValue('The coupon value of ' . $couponValue . ' was exceeded');
        }
    }

    /**
     * @param EasyCouponEntity $coupon
     * @param float            $redeemValue
     * @param string           $currencyId
     *
     * @throws MaxRedemptionValueExceeded
     */
    private function validateMaxRedemptionValue(EasyCouponEntity $coupon, float $redeemValue, string $currencyId): void
    {
        if (!$coupon->getMaxRedemptionValue() instanceof PriceCollection) {
            return;
        }

        $price = $coupon->getMaxRedemptionValue()->getCurrencyPrice($currencyId);
        if ($price->getGross() < $redeemValue) {
            throw new MaxRedemptionValueExceeded('The max redemption value ' . $price->getGross() . ' was exceeded');
        }
    }

    /**
     * @param EasyCouponEntity $coupon
     *
     * @throws ValidUntilExceeded
     */
    public function validateValidUntil(EasyCouponEntity $coupon): void
    {
        $now = new \DateTime('now');
        if ($coupon->getValidUntil() instanceof \DateTime && $coupon->getValidUntil() < $now) {
            throw new ValidUntilExceeded('The valid until date was exceeded: '. $coupon->getValidUntil()->format('Y-m-d'));
        }
    }

    /**
     * @param array $data
     *
     * @throws MissingParameter
     */
    public function validateBookValueParams(array $data): void
    {
        $requiredFields = [
            'id',
            'value',
            'currencyId',
        ];

        $this->checkRequiredFieldsAvailable($requiredFields, $data);

        if (isset($data['orderId']) && !isset($data['orderVersionId'])) {
            throw new MissingParameter('The field "orderVersionId" is missing');
        }
    }

    /**
     * @param array   $data
     * @param Context $context
     *
     * @throws InvalidCode
     * @throws InvalidValueType
     * @throws InvalidVoucherType
     * @throws MissingParameter
     * @throws \ReflectionException
     */
    public function validateCreateVoucherParams(array $data, Context $context): void
    {
        $this->validateCreateNecessaryData($data);
        $this->validateVoucherType($data);
        $this->validateValueType($data);
        $this->validateVoucherCode($data, $context);
    }

    /**
     * @param array   $data
     * @param Context $context
     *
     * @throws InvalidCode
     */
    private function validateVoucherCode(array $data, Context $context): void
    {
        $voucher = $this->voucherRepository->getVoucherByCode($data['code'], $context);
        if ($voucher instanceof EasyCouponEntity) {
            throw new InvalidCode('Given Code "' . $data['code'] . '" is already in use');
        }
    }

    /**
     * @param array $data
     * @throws InvalidValueType
     * @throws \ReflectionException
     */
    private function validateValueType(array $data): void
    {
        $valueTypes = $this->getValueTypes();
        if (!\in_array($data['valueType'], $valueTypes, true)) {
            throw new InvalidValueType('Given value type "' . $data['valueType'] . '" is invalid');
        }
    }

    /**
     * @param array $data
     * @throws InvalidVoucherType
     * @throws \ReflectionException
     */
    private function validateVoucherType(array $data): void
    {
        $voucherTypes = $this->getVoucherTypes();
        if (!\in_array($data['voucherType'], $voucherTypes, true)) {
            throw new InvalidVoucherType('Given voucher type "' . $data['voucherType'] . '" is invalid');
        }
    }

    /**
     * @param array $data
     * @throws MissingParameter
     */
    private function validateCreateNecessaryData(array $data): void
    {
        $requiredFields = [
            'active',
            'voucherType',
            'code',
            'value',
            'valueType',
            'discardRemaining',
            'excludeFromShippingCosts',
            'noDeliveryCharge',
            'customerGroupCharge',
            'orderPositionNumber',
            'combineVouchers',
            'currencyId',
        ];

        $this->checkRequiredFieldsAvailable($requiredFields, $data);

        if (isset($data['productId']) && !isset($data['productVersionId'])) {
            throw new MissingParameter('The field "productVersionId" is missing');
        }
    }

    /**
     * @param string[] $requiredFields
     *
     * @throws MissingParameter
     */
    private function checkRequiredFieldsAvailable(array $requiredFields, array $data): void
    {
        foreach ($requiredFields as $field) {
            if (isset($data[$field])) {
                continue;
            }

            throw new MissingParameter('Field "' . $field . '" is missing');
        }
    }

    /**
     * @return array
     * @throws \ReflectionException
     */
    private function getVoucherTypes(): array
    {
       return $this->getConstantsWithBeginningName(EasyCouponEntity::class, 'VOUCHER_TYPE_');
    }

    /**
     * @return array
     * @throws \ReflectionException
     */
    private function getValueTypes(): array
    {
        return $this->getConstantsWithBeginningName(EasyCouponEntity::class, 'VALUE_TYPE_');
    }

    /**
     * @param string $className
     * @param string $beginName
     * @return array
     * @throws \ReflectionException
     */
    private function getConstantsWithBeginningName(string $className, string $beginName): array
    {
        $constants = $this->getConstants($className);

        foreach ($constants as $name => $value) {
            if (\mb_strpos($name, $beginName) === false) {
                unset($constants[$name]);
            }
        }

        return $constants;
    }

    /**
     * @param string $className
     * @return array
     * @throws \ReflectionException
     */
    private function getConstants(string $className): array
    {
        $reflectionClass = new \ReflectionClass($className);

        return $reflectionClass->getConstants();
    }
}
