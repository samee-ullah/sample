<?php

declare(strict_types=1);

namespace NetInventors\NetiNextEasyCoupon\Service;

use Faker\Factory;
use NetInventors\NetiNextEasyCoupon\Core\Content\EasyCoupon\EasyCouponEntity;
use NetInventors\NetiNextEasyCoupon\Core\Content\Product\Aggregate\EasyCouponProductEntity;
use NetInventors\NetiNextEasyCoupon\Core\Content\Transaction\TransactionEntity;
use NetInventors\NetiNextEasyCoupon\Core\Framework\Exception\InvalidTypeException;
use NetInventors\NetiNextEasyCoupon\Core\Framework\Pricing\ProductValuePrice;
use NetInventors\NetiNextEasyCoupon\Service\Exception\EasyCouponFakerException;
use NetInventors\NetiNextEasyCoupon\Struct\BadwordCollection;
use NetInventors\NetiNextEasyCoupon\Struct\VoucherCodeGeneratorConfig;
use NetInventors\NetiNextEasyCoupon\Struct\VoucherCollection;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\ContainsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\Currency\CurrencyEntity;

class FakeDataService
{
    /**
     * @var string[]
     */
    protected $voucherCodes;

    /**
     * @var string[]
     */
    private $vouchers;

    /**
     * @var EntitySearchResult
     */
    private $currencies;

    /**
     * @var ?float
     */
    private $currencyFactor;

    /**
     * @var string[]
     */
    private $products;

    /**
     * @var string[]
     */
    private $taxes;

    /**
     * @var EntityRepositoryInterface
     */
    private $productRepository;

    /**
     * @var EntityRepositoryInterface
     */
    private $easyCouponRepository;

    /**
     * @var EntityRepositoryInterface
     */
    private $currencyRepository;

    /**
     * @var EntityRepositoryInterface
     */
    private $taxRepository;

    /**
     * @var VoucherService
     */
    private $voucherService;

    /**
     * @var EntityRepositoryInterface
     */
    private $easyCouponProductRepository;

    /**
     * @var EntityRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var EntityRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var EntityRepositoryInterface
     */
    private $userRepository;

    /**
     * @var EntityRepositoryInterface
     */
    private $salesChannelRepository;

    /**
     * @var string[]
     */
    private $customers;

    /**
     * @var string[]
     */
    private $orders;

    /**
     * @var string[]
     */
    private $users;

    /**
     * @var string[]
     */
    private $salesChannels;

    /**
     * @var EntityRepositoryInterface
     */
    private $easyCouponTransactionRepository;

    /**
     * @var int[][][]
     */
    private $typesCache = [];

    public function __construct(
        EntityRepositoryInterface $productRepository,
        EntityRepositoryInterface $easyCouponRepository,
        EntityRepositoryInterface $currencyRepository,
        EntityRepositoryInterface $taxRepository,
        EntityRepositoryInterface $easyCouponProductRepository,
        EntityRepositoryInterface $customerRepository,
        EntityRepositoryInterface $orderRepository,
        EntityRepositoryInterface $userRepository,
        EntityRepositoryInterface $salesChannelRepository,
        EntityRepositoryInterface $easyCouponTransactionRepository,
        VoucherService $voucherService
    ) {
        $this->productRepository               = $productRepository;
        $this->easyCouponRepository            = $easyCouponRepository;
        $this->currencyRepository              = $currencyRepository;
        $this->taxRepository                   = $taxRepository;
        $this->easyCouponProductRepository     = $easyCouponProductRepository;
        $this->customerRepository              = $customerRepository;
        $this->orderRepository                 = $orderRepository;
        $this->userRepository                  = $userRepository;
        $this->salesChannelRepository          = $salesChannelRepository;
        $this->voucherService                  = $voucherService;
        $this->easyCouponTransactionRepository = $easyCouponTransactionRepository;
    }

    /**
     * @param int  $number
     * @param bool $reset
     *
     * @throws EasyCouponFakerException
     * @throws \ReflectionException
     */
    public function fakeCouponData(int $number, bool $reset): void
    {
        if ($reset) {
            $this->resetEasyCoupons();
            $this->resetTransactionFakeData();
            echo "Fake vouchers and transactions wiped out \n";
        }

        if (0 === $number) {
            $number++;
        }

        if (empty($this->voucherCodes)) {
            $this->voucherCodes = $this->createRandomVouchers($number);
        }

        $faker        = Factory::create();
        $coupons      = [];
        $transactions = [];

        for ($i = 1; $i <= $number; $i++) {
            $voucherCodeArrayKey = array_rand($this->voucherCodes, 1);
            if (\is_array($voucherCodeArrayKey)) {
                continue;
            }

            $code                = $this->voucherCodes[$voucherCodeArrayKey];
            unset($this->voucherCodes[$voucherCodeArrayKey]);

            $value       = \random_int(1, 100);
            $id          = Uuid::randomHex();
            $customerId  = $this->getRandomCustomerId();
            $valueType   = $this->getRandomType(EasyCouponEntity::class, EasyCouponEntity::PREFIX_VALUE_TYPE);
            $voucherType = $this->getRandomType(EasyCouponEntity::class, EasyCouponEntity::PREFIX_VOUCHER_TYPE);

            $maxRedemptionValue = EasyCouponEntity::VALUE_TYPE_PERCENTAL === $valueType && 1 === \random_int(0, 3) ? [
                [
                    'currencyId' => Defaults::CURRENCY,
                    'gross'      => $defaultPrice = $faker->randomFloat(2, 1, 50),
                    'linked'     => true,
                    'net'        => $defaultPrice / 1.19,
                ],
            ] : null;

            if (\is_array($maxRedemptionValue) && 1 === \random_int(0, 1)) {
                $maxRedemptionValue[] = [
                    'currencyId' => $this->getRandomCurrencyId(true),
                    'gross'      => $randomPrice = $faker->randomFloat(2, 1, 50),
                    'linked'     => true,
                    'net'        => $randomPrice / 1.19,
                ];
            }

            $coupon = [
                'id'                       => $id,
                'active'                   => $faker->boolean,
                'voucherType'              => $voucherType,
                'code'                     => $code,
                'valueType'                => $valueType,
                'value'                    => $value,
                'discardRemaining'         => $faker->boolean,
                'shippingCharge'           => $faker->boolean,
                'excludeFromShippingCosts' => $faker->boolean,
                'noDeliveryCharge'         => $faker->boolean,
                'customerGroupCharge'      => $faker->boolean,
                'mailSent'                 => $faker->boolean,
                'productId'                => $this->getRandomProductId(),
                'ruleId'                   => null,
                'comment'                  => null,
                'currencyId'               => Defaults::CURRENCY,
                'currencyFactor'           => 1,
                'orderPositionNumber'      => $faker->word . '-' . $id,
                'taxId'                    => $this->getRandomTaxId(),
                'tagId'                    => null,
                'maxRedemptionValue'       => $maxRedemptionValue,
                'combineVouchers'          => $faker->boolean,
                'translations'             => [
                    [
                        'title'      => 'I am a FAKER ' . $faker->colorName,
                        'languageId' => Defaults::LANGUAGE_SYSTEM,
                    ],
                ],
            ];

            $transactionTypes = [
                TransactionEntity::TYPE_CREATED_BY_PURCHASE,
                TransactionEntity::TYPE_CREATED_BY_ADMIN,
            ];
            $transactionType  = $transactionTypes[\array_rand($transactionTypes)];
            $orderId          = TransactionEntity::TYPE_CREATED_BY_PURCHASE === $transactionType
                ? $this->getRandomOrderId()
                : null;

            $transaction = [
                'id'              => Uuid::randomHex(),
                'easyCouponId'    => $id,
                'customerId'      => $customerId,
                'orderId'         => $orderId,
                'value'           => $value,
                'transactionType' => $transactionType,
                'internComment'   => 'I am a FAKER ' . $faker->colorName,
                'currencyId'      => Defaults::CURRENCY,
                'currencyFactor'  => 1,
                'userId'          => $this->getRandomUserId($orderId),
                'salesChannelId'  => $this->getRandomSalesChannelId(),
            ];

            $coupons[]      = $coupon;
            $transactions[] = $transaction;

            if (0 === $i % 1000) {
                $this->easyCouponRepository->create($coupons, Context::createDefaultContext());
                $coupons = [];
                echo sprintf("%d vouchers created \n", $i);
                $this->easyCouponTransactionRepository->create($transactions, Context::createDefaultContext());
                $transactions = [];
            }
        }

        if ([] !== $coupons) {
            $this->easyCouponRepository->create($coupons, Context::createDefaultContext());
            echo sprintf("%d vouchers created \n", \count($coupons));
            $this->easyCouponTransactionRepository->create($transactions, Context::createDefaultContext());
        }
    }

    /**
     * @param int  $number
     * @param bool $reset
     *
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function fakeVoucherProductData(int $number, bool $reset): void
    {
        if ($reset) {
            $this->resetEasyCouponProducts();
            echo "Fake products wiped out \n";
        }

        if (0 === $number) {
            $number++;
        }

        $faker = Factory::create();

        $productPrice = new ProductValuePrice(Defaults::CURRENCY, 0, 0, true, null, 20, 40);

        $products = [];
        for ($i = 1; $i <= $number; $i++) {
            $id    = Uuid::randomHex();
            $price = $faker->randomFloat(2, 1, 50);

            $product = [
                'id'            => $id,
                'name'          => $faker->colorName,
                'active'        => true,
                'taxId'         => $this->getRandomTaxId(),
                'stock'         => $faker->numberBetween(1, 25),
                'productNumber' => 'EasyCoupon-' . $id,
                'price'         => [
                    [
                        'currencyId' => Defaults::CURRENCY,
                        'gross'      => $price,
                        'linked'     => true,
                        'net'        => $price / 1.19,
                    ],
                ],
                'extensions'    => [
                    'netiEasyCouponProduct' => [
                        'id'                       => Uuid::randomHex(),
                        'productId'                => $id,
                        'postal'                   => $faker->boolean(10),
                        'value'                    => [
                            // [
                            //     "currencyId" => Defaults::CURRENCY,
                            //     "gross"      => $price,
                            //     "linked"     => true,
                            //     "net"        => $price / 1.19,
                            // ],
                            $productPrice->getVars(),
                        ],
                        'valueType'                => $this->getValueTypeId($productPrice),
                        'discardRemaining'         => $faker->boolean(85),
                        'shippingCharge'           => $faker->boolean,
                        'excludeFromShippingCosts' => $faker->boolean,
                        'noDeliveryCharge'         => $faker->boolean,
                        'customerGroupCharge'      => $faker->boolean,
                        'ruleId'                   => null,
                        'comment'                  => null,
                        'orderPositionNumber'      => $faker->word . '-' . $id,
                        'taxId'                    => $this->getRandomTaxId(),
                        'translations'             => [
                            [
                                'title'      => $this->generateRandomString(),
                                'languageId' => Defaults::LANGUAGE_SYSTEM,
                            ],
                        ],
                    ],

                ],
            ];
            $products[] = $product;

            if (0 === $i % 1000) {
                $this->productRepository->create($products, Context::createDefaultContext());
                $products = [];
                echo sprintf("%d products created \n", $i);
            }
        }

        if ([] !== $products) {
            $this->productRepository->create($products, Context::createDefaultContext());
            echo sprintf("%d products created \n", \count($products));
        }
    }

    /**
     * @param int         $number
     * @param string|null $easyCouponId
     *
     * @throws EasyCouponFakerException
     * @throws InvalidTypeException
     * @throws \ReflectionException
     */
    public function fakeTransactionData(int $number, ?string $easyCouponId): void
    {
        if (0 === $number) {
            $number++;
        }

        $faker = Factory::create();

        for ($i = 1; $i <= $number; $i++) {
            if (empty($this->vouchers)) {
                $this->getRandomEasyCoupon($easyCouponId);
            }
            $arrayPosition = array_rand($this->vouchers, 1);
            if (is_array($arrayPosition)) {
                continue;
            }

            /** @var EasyCouponEntity $voucher */
            $voucher      = $this->vouchers[$arrayPosition];
            $voucherValue = $voucher->getValue();
            unset($this->vouchers[$arrayPosition]);
            $value = $faker->randomFloat(2, -$voucherValue, $voucherValue);
            if ($value > 0) {
                $orderId = null;
            } else {
                $orderId = $this->getRandomOrderId();
            }

            switch ($voucher->getVoucherType()) {
                case EasyCouponEntity::VOUCHER_TYPE_INDIVIDUAL:
                    $customerId = $this->getCustomerIdFromTransaction($voucher->getId());
                    break;
                case EasyCouponEntity::VOUCHER_TYPE_GENERAL:
                    $customerId = $this->getRandomCustomerId();
                    break;
                default:
                    throw new InvalidTypeException(\sprintf('Invalid voucher type "%s".', $voucher->getVoucherType()));
            }

            $transactionType = $this->getRandomType(
                TransactionEntity::class,
                TransactionEntity::PREFIX_TRANSACTION_TYPE,
                [
                    TransactionEntity::TYPE_CREATED_BY_PURCHASE,
                    TransactionEntity::TYPE_CREATED_BY_ADMIN,
                ]
            );

            $transaction = [
                'id'              => Uuid::randomHex(),
                'easyCouponId'    => $voucher->getId(),
                'customerId'      => $customerId,
                'orderId'         => $orderId,
                'value'           => $value,
                'transactionType' => $transactionType,
                'internComment'   => 'I am a FAKER ' . $faker->colorName,
                'currencyId'      => $this->getRandomCurrencyId(),
                'currencyFactor'  => $this->currencyFactor,
                'userId'          => $this->getRandomUserId($orderId),
                'salesChannelId'  => $this->getRandomSalesChannelId(),
            ];
            $this->easyCouponTransactionRepository->create([ $transaction ], Context::createDefaultContext());
            $this->easyCouponRepository->update([
                [
                    'id'         => $voucher->getId(),
                    'customerId' => $customerId,
                ],
            ], Context::createDefaultContext());
        }

        echo sprintf("%d transactions created \n", $number);
    }

    /**
     * https://stackoverflow.com/questions/4356289/php-random-string-generator
     *
     * @param int $length
     *
     * @return string
     * @throws \Exception
     */
    private function generateRandomString(int $length = 10): string
    {
        $characters       = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString     = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }

        return $randomString;
    }

    /**
     * @param ProductValuePrice $price
     *
     * @return int
     * @throws \ReflectionException
     */
    private function getValueTypeId(ProductValuePrice $price): int
    {
        if ($price->getNet() > 0.00 && $price->getGross() > 0.00) {
            return EasyCouponProductEntity::VALUE_TYPE_FIXED;
        }

        if ([] !== $price->getSelectableValues()) {
            return EasyCouponProductEntity::VALUE_TYPE_SELECTION;
        }

        if (
            \is_float($price->getFrom())
            && $price->getFrom() > 0.00
            && \is_float($price->getTo())
            && $price->getTo() > 0.00
        ) {
            return EasyCouponProductEntity::VALUE_TYPE_RANGE;
        }

        return $this->getRandomType(
            TransactionEntity::class,
            TransactionEntity::PREFIX_TRANSACTION_TYPE,
            [
                TransactionEntity::TYPE_CREATED_BY_PURCHASE,
                TransactionEntity::TYPE_CREATED_BY_ADMIN,
            ]
        );
    }

    private function getRandomProductId(): string
    {
        if (empty($this->products)) {
            $criteria = new Criteria();
            $criteria->setLimit(100);

            $this->products = array_map(
                'strval',
                $this->productRepository->searchIds($criteria, Context::createDefaultContext())->getIds()
            );
        }

        $key = array_rand($this->products, 1);
        if (\is_array($key)) {
            return '';
        }

        return $this->products[$key];
    }

    /**
     * @param class-string $entity
     * @param string       $prefix
     * @param array        $excluded
     *
     * @return int
     * @throws \ReflectionException
     */
    private function getRandomType(string $entity, string $prefix, array $excluded = []): int
    {
        if (!isset($this->typesCache[$entity][$prefix])) {
            $this->typesCache[$entity][$prefix] = [];
            $reflection                         = new \ReflectionClass($entity);
            $constants                          = $reflection->getConstants() ?? [];

            foreach ($constants as $name => $value) {
                if (0 !== \strpos($name, $prefix) || \in_array($value, $excluded, true)) {
                    continue;
                }
                $this->typesCache[$entity][$prefix][] = $value;
            }
        }

        return $this->typesCache[$entity][$prefix][\array_rand($this->typesCache[$entity][$prefix])];
    }

    /**
     * @param bool $excludeDefaultCurrency
     *
     * @return string
     * @throws EasyCouponFakerException
     */
    private function getRandomCurrencyId($excludeDefaultCurrency = false): string
    {
        $currency = null;
        $counter  = 0;

        if (empty($this->currencies)) {
            $criteria = new Criteria();
            $criteria->setLimit(100);

            $this->currencies = $this->currencyRepository->search($criteria, Context::createDefaultContext());
        }

        do {
            $key = \array_rand($this->currencies->getElements(), 1);
            if (\is_array($key)) {
                continue;
            }

            /** @var CurrencyEntity $currency */
            $currency             = $this->currencies->getElements()[$key];
            $this->currencyFactor = $currency->getFactor();

            if ($excludeDefaultCurrency && Defaults::CURRENCY === $currency->getId()) {
                $currency = $this->currencyFactor  = null;
            }
        } while (++$counter < 100 && !$currency instanceof CurrencyEntity);

        if (!$currency instanceof CurrencyEntity) {
            throw new EasyCouponFakerException('No currency found');
        }

        return $currency->getId();
    }

    private function getRandomTaxId(): string
    {
        if (empty($this->taxes)) {
            $criteria = new Criteria();
            $criteria->setLimit(100);

            $this->taxes = \array_map(
                'strval',
                $this->taxRepository->searchIds($criteria, Context::createDefaultContext())->getIds()
            );
        }

        $key = array_rand($this->taxes, 1);
        if (\is_array($key)) {
            return '';
        }

        return $this->taxes[$key];
    }

    private function createRandomVouchers(int $number): array
    {
        $voucherCodeGeneratorConfig = new VoucherCodeGeneratorConfig();
        $voucherCodeGeneratorConfig->setNumOfVoucherCodes($number);
        $voucherCodeGeneratorConfig->setReservedVouchers(
            new VoucherCollection(
                [
                    'xfbq-khtm-85',
                ]
            )
        );
        $voucherCodeGeneratorConfig->setBadwords(
            new BadwordCollection(
                [
                    'fick',
                    'doof',
                    'dumm',
                    'nazi',
                    'heil',
                ]
            )
        );
        $vouchers = $this->voucherService->generateVoucherCodes($voucherCodeGeneratorConfig);

        return $vouchers->map(
            function ($el) {
                return $el;
            }
        );
    }

    /**
     * @param string|null $easyCouponId
     *
     * @throws EasyCouponFakerException
     */
    private function getRandomEasyCoupon(?string $easyCouponId = null): void
    {
        if (!empty($this->vouchers)) {
            return;
        }
        $criteria = new Criteria();
        $criteria->setLimit(100);

        if (is_string($easyCouponId) && '' !== $easyCouponId) {
            $criteria->addFilter(new EqualsFilter('id', $easyCouponId));
        }

        $result = $this->easyCouponRepository->search($criteria, Context::createDefaultContext());
        if (empty($result->getElements())) {
            throw new EasyCouponFakerException('create some vouchers first with neti:easy_coupon:coupon_fake');
        }
        $this->vouchers = $result->getElements();
    }

    private function getRandomCustomerId(): string
    {
        if (empty($this->customers)) {
            $criteria = new Criteria();
            $criteria->setLimit(100);

            $this->customers = array_map(
                'strval',
                $this->customerRepository->searchIds($criteria, Context::createDefaultContext())->getIds()
            );
        }

        $key = array_rand($this->customers, 1);
        if (\is_array($key)) {
            return '';
        }

        return $this->customers[$key];
    }

    /**
     * @return string|null
     * @throws \Exception
     */
    private function getRandomOrderId(): ?string
    {
        if (empty($this->orders)) {
            $criteria = new Criteria();
            $criteria->setLimit(100);

            $this->orders =
                \array_map(
                    'strval',
                    $this->orderRepository->searchIds($criteria, Context::createDefaultContext())->getIds()
                );
        }

        if (1 === \random_int(0, 1)) {
            $key = array_rand($this->orders, 1);
            if (\is_array($key)) {
                return null;
            }

            return $this->orders[$key];
        }

        return null;
    }

    private function getRandomUserId(?string $orderId): ?string
    {
        if (null !== $orderId) {
            return null;
        }

        if (empty($this->users)) {
            $criteria = new Criteria();
            $criteria->setLimit(100);

            $this->users = \array_map(
                'strval',
                $this->userRepository->searchIds($criteria, Context::createDefaultContext())->getIds()
            );
        }

        $key = array_rand($this->users, 1);
        if (\is_array($key)) {
            return '';
        }

        return $this->users[$key];
    }

    private function getRandomSalesChannelId(): string
    {
        if (empty($this->salesChannels)) {
            $criteria = new Criteria();
            $criteria->setLimit(100);

            $this->salesChannels =
                \array_map(
                    'strval',
                    $this->salesChannelRepository->searchIds($criteria, Context::createDefaultContext())->getIds());
        }

        $key = array_rand($this->salesChannels, 1);
        if (\is_array($key)) {
            return '';
        }

        return $this->salesChannels[$key];
    }

    private function resetEasyCoupons(): void
    {
        $criteria = new Criteria();
        $criteria->addFilter(new ContainsFilter('title', 'I am a FAKER'));

        $context     = Context::createDefaultContext();
        $easyCoupons = $this->easyCouponRepository->searchIds($criteria, $context);

        if (0 === $easyCoupons->getTotal()) {
            return;
        }

        $keys = array_map(
            static function ($id) {
                return [ 'id' => $id ];
            },
            $easyCoupons->getIds()
        );

        $this->easyCouponRepository->delete($keys, $context);
    }

    private function resetTransactionFakeData(): void
    {
        $criteria = new Criteria();
        $criteria->addFilter(new ContainsFilter('internComment', 'I am a FAKER'));

        $context                = Context::createDefaultContext();
        $easyCouponTransactions = $this->easyCouponTransactionRepository->searchIds($criteria, $context);

        if (0 === $easyCouponTransactions->getTotal()) {
            return;
        }

        $keys = array_map(
            static function ($id) {
                return [ 'id' => $id ];
            },
            $easyCouponTransactions->getIds()
        );

        $this->easyCouponTransactionRepository->delete($keys, $context);
    }

    private function resetEasyCouponProducts(): void
    {
        $criteria = new Criteria();
        $criteria->addFilter(new ContainsFilter('productNumber', 'EasyCoupon'));

        $context            = Context::createDefaultContext();
        $easyCouponProducts = $this->productRepository->searchIds($criteria, $context);

        if (0 === $easyCouponProducts->getTotal()) {
            return;
        }

        $keys = array_map(
            static function ($id) {
                return [ 'id' => $id ];
            },
            $easyCouponProducts->getIds()
        );

        $this->productRepository->delete($keys, $context);

        $this->deleteProductsExtensions();
    }

    private function deleteProductsExtensions(): void
    {
        $criteria = new Criteria();

        $context            = Context::createDefaultContext();
        $easyCouponProducts = $this->easyCouponProductRepository->searchIds($criteria, $context);

        if (0 === $easyCouponProducts->getTotal()) {
            return;
        }

        $keys = array_map(
            static function ($id) {
                return [ 'id' => $id ];
            },
            $easyCouponProducts->getIds()
        );

        $this->easyCouponProductRepository->delete($keys, $context);
    }

    /**
     * @param string $easyCouponId
     *
     * @return string
     */
    private function getCustomerIdFromTransaction(string $easyCouponId): ?string
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('easyCouponId', $easyCouponId));

        /** @var TransactionEntity $result */
        $result = $this->easyCouponTransactionRepository->search($criteria, Context::createDefaultContext())->first();

        return $result->getCustomerId();
    }
}
