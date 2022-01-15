<?php

declare(strict_types=1);

namespace NetInventors\NetiNextEasyCoupon\Subscriber;

use NetInventors\NetiNextEasyCoupon\Constants\BusinessEventsConstants;
use Shopware\Core\Framework\Event\BusinessEventCollector;
use Shopware\Core\Framework\Event\BusinessEventCollectorEvent;
use Shopware\Core\Framework\Event\BusinessEventDefinition;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class BusinessEvent implements EventSubscriberInterface
{
    protected BusinessEventCollector $collector;

    public function __construct(BusinessEventCollector $collector)
    {
        $this->collector = $collector;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            BusinessEventCollectorEvent::NAME => 'onRegisterEvent',
        ];
    }

    public function onRegisterEvent(BusinessEventCollectorEvent $event)
    {
        foreach (BusinessEventsConstants::EVENT_CLASSES as $class) {
            $eventDefinition = $this->collector->define($class);
            if ($eventDefinition instanceof BusinessEventDefinition) {
                $event->getCollection()->set($class, $eventDefinition);
            }
        }
    }
}
