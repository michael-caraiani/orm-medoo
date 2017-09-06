<?php

declare(strict_types=1);

namespace TiSuit\ORM;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

class Provider implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function register(Container $container): void
    {
        $container['medoo'] = $this->setMedoo($container);
        $container['entity'] = $this->setEntityLoader($container);
    }

    /**
     * Set Medoo into container.
     *
     * @param Container $container
     *
     * @return callable
     */
    protected function setMedoo(Container $container): callable
    {
        return function ($c) {
            $config = $container['config']('medoo');

            return new \Medoo\Medoo($config);
        };
    }

    /**
     * Set entity() function into container.
     *
     * @param Container $container
     *
     * @return callable
     */
    protected function setEntity(Container $container): callable
    {
        return $container->protect(function (string $name) use ($container) {
            return $container->factory(function ($container) use ($name) {
                $parts = explode('_', $name);
                $class = $container['config']('medoo.namespace');
                foreach ($parts as $part) {
                    $class .= ucfirst($part);
                }
                if (!$container->has('entity_'.$class)) {
                    $container['entity_'.$class] = new $class($container);
                }

                return $container['entity_'.$class];
            });
        });
    }
}
