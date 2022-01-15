<?php

declare(strict_types=1);

namespace NetInventors\NetiNextEasyCoupon\Core\Framework\Api\Controller;

use NetInventors\NetiNextEasyCoupon\Core\Content\EasyCoupon\EasyCouponDefinition;
use NetInventors\NetiNextEasyCoupon\Core\Content\Transaction\TransactionDefinition;
use NetInventors\NetiNextEasyCoupon\Exception\Api\ValidUntilExceeded;
use NetInventors\NetiNextEasyCoupon\Service\Api\ApiService;
use NetInventors\NetiNextEasyCoupon\Service\Repository\TransactionRepository;
use NetInventors\NetiNextEasyCoupon\Service\Repository\VoucherRepository;
use Shopware\Core\Framework\Api\Converter\ApiVersionConverter;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @RouteScope(scopes={"api"})
 */
class ApiController extends AbstractController
{
    protected ApiService          $apiService;

    protected VoucherRepository   $voucherRepository;

    protected ApiVersionConverter $apiVersionConverter;

    protected TransactionRepository $transactionRepository;

    public function __construct(
        ApiService $apiService,
        VoucherRepository $voucherRepository,
        ApiVersionConverter $apiVersionConverter,
        TransactionRepository $transactionRepository
    ) {
        $this->apiService            = $apiService;
        $this->voucherRepository     = $voucherRepository;
        $this->apiVersionConverter   = $apiVersionConverter;
        $this->transactionRepository = $transactionRepository;
    }

    /**
     * @Route("/api/_action/neti_next_easy_coupon_api", name="api.action.easy-coupon-create", defaults={"auth_required"=true}, methods={"POST"})
     */
    public function create(Request $request, Context $context): JsonResponse
    {
        try {
            $data = $request->request->all();
            $this->apiService->createVoucher($data, $context);

            $voucher = $this->voucherRepository->getVoucherByCodeWithAssociations($data['code'], $context);
        } catch (\Exception $exception) {
            return new JsonResponse([
                'success' => false,
                'errors' => $exception->getMessage()
            ]);
        }

        return new JsonResponse([
            'success' => true,
            'data'    => $this->apiVersionConverter->convertEntity(new EasyCouponDefinition(), $voucher),
        ]);
    }

    /**
     * @Route("/api/_action/neti_next_easy_coupon_api/{id}/book", name="api.action.easy-coupon-book-value", defaults={"auth_required"=true}, methods={"POST"})
     */
    public function book(Request $request, Context $context): JsonResponse
    {
        try {
            $couponId = $request->attributes->get('id');
            $data     = $request->request->all();
            $this->apiService->bookValue(
                array_merge([ 'id' => $couponId ], $data),
                $context
            );

            $transaction = $this->transactionRepository->getLatestTransactionIdOfCoupon($couponId, $context);
        } catch (\Exception $exception) {
            return new JsonResponse(
                [
                    'success' => false,
                    'errors'  => $exception->getMessage(),
                ]
            );
        }

        return new JsonResponse(
            [
                'success' => true,
                'data'    => array_merge(
                    ['force' => $data['force'] ?? false],
                    $this->apiVersionConverter->convertEntity(new TransactionDefinition(), $transaction),
                )
            ]
        );
    }

    /**
     * @Route("/api/_action/neti_next_easy_coupon_api/{id}/validate", name="api.action.easy-coupon-validate", defaults={"auth_required"=true}, methods={"GET"})
     */
    public function validate(Request $request, Context $context): JsonResponse
    {
        try {
            $couponId = $request->attributes->get('id');
            $this->apiService->validateVoucher($couponId, $context);
        } catch (ValidUntilExceeded $exception) {
            return new JsonResponse([
                'success' => true,
                'data'    => [
                    'id' => $couponId,
                    'valid' => false,
                ],
            ]);
        } catch (\Exception $exception) {
            return new JsonResponse(
                [
                    'success' => false,
                    'errors'  => $exception->getMessage(),
                ]
            );
        }

        return new JsonResponse([
            'success' => true,
            'data'    => [
                'id' => $couponId,
                'valid' => true,
            ],
        ]);
    }
}
