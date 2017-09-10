<?php

declare(strict_types=1);

namespace TiSuit\ORM\Tests;

use PHPUnit\Framework\TestCase;

class ProviderTest extends TestCase
{
    protected $container;

    protected function setUp(): void
    {
        $dir = __DIR__.'/data/config';
        $app = new \TiSuit\Core\App(['config_dir' => $dir]);
        $this->container = $app->getContainer();
    }

    public function testMedoo(): void
    {
        $this->assertInstanceOf('\Medoo\Medoo', $this->container->medoo);
    }

    public function testEntityLoader(): void
    {
        $entity = $this->container['entity']('dummy_entity');
        $this->assertInstanceOf('\TiSuit\ORM\Entity', $this->container['entity']('dummy_entity'));
    }
}
