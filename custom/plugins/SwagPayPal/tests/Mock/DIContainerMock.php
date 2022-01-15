<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\PayPal\Test\Mock;

use Symfony\Component\DependencyInjection\ContainerInterface;

// ToDo: Remove workaround when minVersion >= 6.4.3.0
$hasMethod = new \ReflectionMethod(ContainerInterface::class, 'has');
$parameter = \current($hasMethod->getParameters());

if ($parameter === false || !$parameter->hasType()) {
    class DIContainerMock implements ContainerInterface
    {
        /**
         * {@inheritdoc}
         */
        public function set(string $id, ?object $service): void
        {
        }

        /**
         * {@inheritdoc}
         */
        public function get($id, int $invalidBehavior = self::EXCEPTION_ON_INVALID_REFERENCE): ?object
        {
        }

        /**
         * {@inheritdoc}
         */
        public function has($id): bool
        {
        }

        /**
         * {@inheritdoc}
         */
        public function initialized(string $id): bool
        {
        }

        /**
         * {@inheritdoc}
         *
         * @return array|bool|float|int|string|null
         */
        public function getParameter(string $name)
        {
        }

        /**
         * {@inheritdoc}
         */
        public function hasParameter(string $name): bool
        {
        }

        /**
         * {@inheritdoc}
         */
        public function setParameter(string $name, $value): void
        {
        }
    }
} else {
    class DIContainerMock implements ContainerInterface
    {
        /**
         * {@inheritdoc}
         */
        public function set(string $id, ?object $service): void
        {
        }

        /**
         * {@inheritdoc}
         */
        public function get($id, int $invalidBehavior = self::EXCEPTION_ON_INVALID_REFERENCE): ?object
        {
        }

        /**
         * {@inheritdoc}
         */
        public function has(string $id): bool
        {
        }

        /**
         * {@inheritdoc}
         */
        public function initialized(string $id): bool
        {
        }

        /**
         * {@inheritdoc}
         *
         * @return array|bool|float|int|string|null
         */
        public function getParameter(string $name)
        {
        }

        /**
         * {@inheritdoc}
         */
        public function hasParameter(string $name): bool
        {
        }

        /**
         * {@inheritdoc}
         */
        public function setParameter(string $name, $value): void
        {
        }
    }
}
