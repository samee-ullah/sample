<?php

/**
 * @copyright Copyright (c) 2020, Net Inventors GmbH
 * @category  Shopware
 * @author    malte
 */

declare(strict_types=1);

namespace NetInventors\NetiNextEasyCoupon\Service;

use NetInventors\NetiNextEasyCoupon\Core\Checkout\Cart\AbstractCartProcessor;
use NetInventors\NetiNextEasyCoupon\Core\Content\Condition\ConditionCollection;
use NetInventors\NetiNextEasyCoupon\Core\Content\Condition\ConditionEntity;
use NetInventors\NetiNextEasyCoupon\Core\Content\EasyCoupon\EasyCouponEntity;
use NetInventors\NetiNextEasyCoupon\Core\Content\EasyCoupon\Rule\EasyCouponRuleInterface;
use NetInventors\NetiNextEasyCoupon\Core\Content\EasyCoupon\Rule\EasyCouponRuleScope;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Content\Rule\DataAbstractionLayer\Indexing\ConditionTypeNotFound;
use Shopware\Core\Content\Rule\RuleCollection;
use Shopware\Core\Content\Rule\RuleEntity;
use Shopware\Core\Framework\Rule\Collector\RuleConditionRegistry;
use Shopware\Core\Framework\Rule\Container\ContainerInterface;
use Shopware\Core\Framework\Rule\Rule;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\DependencyInjection\Container;
use NetInventors\NetiNextEasyCoupon\Core\Content\Product\Aggregate\Condition\ConditionEntity as ProductConditionEntity;
use NetInventors\NetiNextEasyCoupon\Core\Content\Product\Aggregate\Condition\ConditionCollection as ProductConditionCollection;

class ConditionService
{
    /**
     * @var RuleConditionRegistry
     */
    private $ruleConditionRegistry;

    /**
     * @var Container
     */
    private $container;

    public function __construct(
        Container $container,
        RuleConditionRegistry $ruleConditionRegistry
    ) {
        $this->container             = $container;
        $this->ruleConditionRegistry = $ruleConditionRegistry;
    }

    public function validateConditions(EasyCouponEntity $easyCoupon, Cart $cart, SalesChannelContext $context): bool
    {
        $conditions = $easyCoupon->getConditions();
        if (!$conditions instanceof ConditionCollection) {
            return true;
        }

        $ruleCollection = $this->createRuleCollection($conditions);

        if (
            !$ruleCollection instanceof RuleCollection
            || 0 === $ruleCollection->count()
        ) {
            return true;
        }

        $matchingRules = $ruleCollection->filter(
            function (RuleEntity $rule) use ($easyCoupon, $cart, $context) {
                $voucherLineItems = $cart->getLineItems()->filterType(AbstractCartProcessor::EASY_COUPON_LINE_ITEM_TYPE);
                $lineItems        = $cart->getLineItems()->filter(
                    function (LineItem $lineItem) {
                        return $lineItem->getType() !== AbstractCartProcessor::EASY_COUPON_LINE_ITEM_TYPE;
                    }
                );
                $cart->setLineItems($lineItems);

                $result = $rule->getPayload()->match(
                    new EasyCouponRuleScope($easyCoupon, $cart, $context)
                );

                foreach ($voucherLineItems as $voucherLineItem) {
                    $lineItems->add($voucherLineItem);
                }
                $cart->setLineItems($lineItems);

                return $result;
            }
        );

        return $matchingRules->count() > 0;
    }

    /**
     * This method transforms the 1-dimensional condition collection into a multi-dimensional array.
     *
     * @param ProductConditionCollection|null $conditions
     * @param string|null                     $parentId
     *
     * @return array
     */
    public function mapConditions (
        ?ProductConditionCollection $conditions,
        string $parentId = null
    ): array {
        if (null === $conditions) {
            return [];
        }

        return array_map(
            function (
                ProductConditionEntity $condition) use ($conditions) {
                return [
                    'type'     => $condition->getLegacyType(),
                    'value'    => $condition->getValue(),
                    'position' => $condition->getPosition(),
                    'children' => $this->mapConditions($conditions, $condition->getId())
                ];
            },
            $conditions->filterByProperty('parentId', $parentId)->getElements()
        );
    }

    /**
     * @param ConditionCollection $conditions
     *
     * @return RuleCollection
     * @throws \Exception
     */
    protected function createRuleCollection(ConditionCollection $conditions): RuleCollection
    {
        $nestedRules = $this->buildNested($conditions->getElements());

        return new RuleCollection(
            array_map(
                static function ($nestedRule) {
                    $rule = new RuleEntity();
                    $rule->setId(Uuid::randomHex());
                    $rule->setPayload($nestedRule);

                    return $rule;
                },
                $nestedRules
            )
        );
    }

    /**
     * Copied by Shopware\Core\Content\Rule\DataAbstractionLayer\buildNested(array $rules, ?string $parentId)
     *
     * @param ConditionEntity[] $rules
     * @param string|null       $parentId
     *
     * @return Rule[]
     * @throws \Exception
     */
    protected function buildNested(array $rules, ?string $parentId = null): array
    {
        $nested = [];

        foreach ($rules as $entity) {
            $rule = $entity->getVars();

            if ($rule['parentId'] !== $parentId) {
                continue;
            }

            if (null === $rule['type']) {
                continue;
            }

            if (!$this->ruleConditionRegistry->has($rule['type'])) {
                throw new ConditionTypeNotFound($rule['type']);
            }

            $ruleClass       = $this->ruleConditionRegistry->getRuleClass($rule['type']);
            $classImplements = \class_implements($ruleClass);

            if (
                false !== $classImplements
                && true === \in_array(EasyCouponRuleInterface::class, $classImplements, true)
            ) {
                $object = $this->container->get($ruleClass);
            } else {
                $object = new $ruleClass();
            }

            if (null !== $rule['value']) {
                /** @var Rule $object */
                $object->assign($rule['value']);
            }

            if ($object instanceof ContainerInterface) {
                $children = $this->buildNested($rules, $rule['id']);
                foreach ($children as $child) {
                    $object->addRule($child);
                }
            }

            $nested[] = $object;
        }

        return $nested;
    }
}
