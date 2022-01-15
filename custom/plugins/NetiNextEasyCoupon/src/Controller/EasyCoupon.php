<?php

namespace NetInventors\NetiNextEasyCoupon\Controller;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use NetInventors\NetiNextEasyCoupon\Core\Content\EasyCoupon\EasyCouponEntity;
use NetInventors\NetiNextEasyCoupon\Core\Content\Product\Aggregate\EasyCouponProductEntity;
use NetInventors\NetiNextEasyCoupon\Core\Content\Transaction\TransactionEntity;
use NetInventors\NetiNextEasyCoupon\Core\Framework\Pricing\ProductValuePrice;
use NetInventors\NetiNextEasyCoupon\Core\Framework\Traits\ValueTypeTrait;
use NetInventors\NetiNextEasyCoupon\Service\Account\VoucherTransactionsService;
use NetInventors\NetiNextEasyCoupon\Service\CurrencyService;
use NetInventors\NetiNextEasyCoupon\Service\PluginConfig;
use NetInventors\NetiNextEasyCoupon\Service\VoucherCodeGenerator;
use NetInventors\NetiNextEasyCoupon\Service\VoucherRedemption\Validator\PaymentActivationStateValidator;
use NetInventors\NetiNextEasyCoupon\Service\VoucherService;
use NetInventors\NetiNextEasyCoupon\Struct\VoucherCodeGeneratorConfig;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Core\System\Currency\CurrencyEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @RouteScope(scopes={"api"})
 */
class EasyCoupon extends AbstractController
{
    /**
     * @var PluginConfig
     */
    private $config;

    /**
     * @var VoucherService
     */
    private $voucherService;

    /**
     * @var Connection
     */
    private $db;

    /**
     * @var VoucherCodeGenerator\Validator\DuplicateValidator
     */
    private $duplicateValidator;

    /**
     * @var EntityRepositoryInterface
     */
    private $couponRepository;

    /**
     * @var VoucherTransactionsService
     */
    private $voucherTransactionsService;

    /**
     * @var PaymentActivationStateValidator
     */
    private $paymentActivationStateValidator;

    /**
     * @var CurrencyService
     */
    private $currencyService;

    /**
     * EasyCoupon constructor.
     *
     * @param PluginConfig                                      $config
     * @param VoucherService                                    $voucherService
     * @param VoucherCodeGenerator\Validator\DuplicateValidator $duplicateValidator
     * @param Connection                                        $db
     * @param EntityRepositoryInterface                         $couponRepository
     * @param VoucherTransactionsService                        $voucherTransactionsService
     * @param PaymentActivationStateValidator                   $paymentActivationStateValidator
     * @param CurrencyService                                   $currencyService
     */
    public function __construct(
        PluginConfig $config,
        VoucherService $voucherService,
        VoucherCodeGenerator\Validator\DuplicateValidator $duplicateValidator,
        Connection $db,
        EntityRepositoryInterface $couponRepository,
        VoucherTransactionsService $voucherTransactionsService,
        PaymentActivationStateValidator $paymentActivationStateValidator,
        CurrencyService $currencyService
    ) {
        $this->config                          = $config;
        $this->voucherService                  = $voucherService;
        $this->duplicateValidator              = $duplicateValidator;
        $this->db                              = $db;
        $this->couponRepository                = $couponRepository;
        $this->voucherTransactionsService      = $voucherTransactionsService;
        $this->paymentActivationStateValidator = $paymentActivationStateValidator;
        $this->currencyService                 = $currencyService;
    }

    /**
     * @Route(
     *     "/api/_action/neti-easy-coupon/status/{couponId}",
     *     name="api.action.neti-easy-coupon.status",
     *     methods={"GET"}
     * )
     *
     * @param Context $context
     * @param string  $couponId
     *
     * @return JsonResponse
     */
    public function getStatus(Context $context, string $couponId): JsonResponse
    {
        $criteria = new Criteria([ $couponId ]);
        $criteria->addAssociation('transactions');
        $criteria->addAssociation('currency');

        $result = $this->couponRepository->search($criteria, $context);
        $coupon = $result->first();

        if ($coupon instanceof EasyCouponEntity) {
            $value = $coupon->getValue();

            if ($coupon->getValueType() === EasyCouponEntity::VALUE_TYPE_ABSOLUTE) {
                $totalValue            = 0;
                $redemptionValue       = 0;
                $defaultCurrency       = $this->currencyService->getDefaultCurrency($context);
                $defaultCurrencyFactor = $defaultCurrency->getFactor();

                /** @var TransactionEntity $transaction */
                foreach ($coupon->getTransactions() as $transaction) {
                    if ($transaction->getValue() > 0) {
                        $totalValue += $transaction->getValue() * $coupon->getCurrencyFactor() / $defaultCurrencyFactor;
                    } else {
                        $redemptionValue -= $transaction->getValue() * $coupon->getCurrencyFactor() / $defaultCurrencyFactor;
                    }
                }

                $remainingValue = $totalValue - $redemptionValue;

                $value = [
                    'initial'             => $coupon->getValue() * $coupon->getCurrencyFactor() / $defaultCurrencyFactor,
                    'total'               => $totalValue,
                    'redemption'          => $redemptionValue,
                    'remaining'           => $remainingValue,
                    'remainingPercentage' => round($remainingValue / $totalValue * 100),
                ];
            }

            return new JsonResponse(
                [
                    'success' => true,
                    'data'    => [
                        'valueType'       => $coupon->getValueType(),
                        'voucherType'     => $coupon->getVoucherType(),
                        'value'           => $value,
                        'redemptionCount' => $coupon->getTransactions()->filter(
                            function (TransactionEntity $transaction) {
                                return $transaction->getValue() < 0;
                            }
                        )->count(),
                        'currencyCode'    => $coupon->getCurrency()->getIsoCode(),
                    ],
                ]
            );
        }

        return new JsonResponse(
            [
                'success' => false,
            ]
        );
    }

    /**
     * @Route(
     *     "/api/_action/neti-easy-coupon/generate-code",
     *     name="api.action.neti-easy-coupon.generate-code",
     *     methods={"GET"}
     * )
     *
     * @throws \Exception
     */
    public function generateCode(): JsonResponse
    {
        $config = new VoucherCodeGeneratorConfig();
        $config->setNumOfVoucherCodes(1);
        $config->setPattern($this->config->getDefaultCodePattern());

        $codes = $this->voucherService->generateVoucherCodes($config);

        return new JsonResponse(
            [
                'code' => $codes->first(),
            ]
        );
    }

    /**
     * @Route(
     *     "/api/_action/neti-easy-coupon/validate-code",
     *     name="api.action.neti-easy-coupon.validate-code",
     *     methods={"POST"}
     * )
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function validateCode(Request $request): JsonResponse
    {
        $code = $request->get('code');

        return new JsonResponse(
            [
                'valid' => $this->duplicateValidator->validate($code),
            ]
        );
    }

    /**
     * This route collects the required information for the easy coupon administration module
     *
     * @Route(
     *     "/api/_action/neti-easy-coupon/config",
     *     name="api.action.neti-easy-coupon.config",
     *     methods={"GET"}
     * )
     *
     * @param Context $context
     *
     * @return JsonResponse
     */
    public function getConfig(Context $context): JsonResponse
    {
        $defaultCurrency = $this->currencyService->getDefaultCurrency($context);

        return new JsonResponse(
            [
                'defaultCurrency'   => [
                    'id'     => $defaultCurrency->getId(),
                    'factor' => $defaultCurrency->getFactor(),
                ],
                'priceField'        => $this->getEmptyPriceField(),
                'freeTaxId'         => $this->getFreeTaxId(),
                'valueTypes'        => [
                    EasyCouponEntity::VALUE_TYPE_ABSOLUTE,
                    EasyCouponEntity::VALUE_TYPE_PERCENTAL,
                ],
                'voucherTypes'      => [
                    EasyCouponEntity::VOUCHER_TYPE_GENERAL,
                    EasyCouponEntity::VOUCHER_TYPE_INDIVIDUAL,
                ],
                'productValueTypes' => [
                    EasyCouponProductEntity::VALUE_TYPE_FIXED,
                    EasyCouponProductEntity::VALUE_TYPE_RANGE,
                    EasyCouponProductEntity::VALUE_TYPE_SELECTION,
                ],
                'transactionTypes'  => [
                    'createdByAdmin' => TransactionEntity::TYPE_CREATED_BY_ADMIN,
                ],
            ]
        );
    }

    /**
     * @Route(
     *     "/api/_action/neti-easy-coupon/customer-vouchers/{customerId}",
     *     name="api.action.neti-easy-coupon.customer-vouchers",
     *     methods={"GET"}
     * )
     *
     * @param Context $context
     * @param string  $customerId
     *
     * @return JsonResponse
     */
    public function customerVouchers(Context $context, string $customerId): JsonResponse
    {
        $vouchers   = $this->voucherTransactionsService->getVouchersForCustomer($customerId, $context);
        $voucherIds = [];

        /** @var EasyCouponEntity $voucher */
        foreach ($vouchers as $voucher) {
            $voucherId              = $voucher->getId();
            $voucherIds[$voucherId] = $voucherId;

            $active = $this->paymentActivationStateValidator->notPurchasedOrMatchesPaymentActivationState(
                $this->config->getVoucherActivatePaymentStatus(),
                $voucher,
                $context
            );

            $voucher->setActive($active);
        }

        $transactions = $this->voucherTransactionsService->getTransactionsForVouchers($voucherIds);
        $restValues   = $this->voucherTransactionsService->getRestValueForVouchers(
            $vouchers,
            $transactions->getElements()
        );

        $data = [];
        foreach ($vouchers as $voucher) {
            $cashedValue = 0;
            $restValue   = 0;

            if (isset($restValues[$voucher->getId()]) && $voucher->getValueType() === 10010) {
                $cashedValue = $restValues[$voucher->getId()];

                if (!$voucher->isDiscardRemaining()) {
                    $restValue = $voucher->getValue() + $cashedValue;
                }
            }

            $data[$voucher->getId()] = [
                'active'      => $voucher->isActive(),
                'value'       => $voucher->getValue(),
                'restValue'   => $restValue,
                'cashedValue' => $cashedValue,
            ];
        }

        return new JsonResponse(
            [
                'success' => true,
                'data'    => $data,
            ]
        );
    }

    protected function getEmptyPriceField(): array
    {
        $price = new ProductValuePrice(
            '',
            0,
            0,
            false
        );

        return $price->jsonSerialize();
    }

    /**
     * @return string|null
     * @throws DBALException
     */
    protected function getFreeTaxId(): ?string
    {
        $sql = '
            SELECT HEX(id)
            FROM tax
            WHERE tax_rate = 0
        ';

        $id = $this->db->fetchColumn($sql);

        if (true === is_string($id)) {
            return strtolower($id);
        }

        return null;
    }
}
