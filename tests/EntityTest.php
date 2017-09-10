<?php

declare(strict_types=1);

namespace TiSuit\ORM\Tests;

use PHPUnit\Framework\TestCase;

class EntityTest extends TestCase
{
    protected $container;

    protected function setUp(): void
    {
        //Init app
        $dir = __DIR__.'/data/config';
        $app = new \TiSuit\Core\App(['config_dir' => $dir]);
        $this->container = $app->getContainer();
        //Init db
        ob_start();
        include $dir.'/../dump.sql';
        $query = ob_get_clean();
        $this->container->medoo->pdo->exec($query);
    }

    public function testGetSet(): void
    {
        //getData (from scratch)
        $entity = $this->container['entity']('dummy_entity');
        $this->assertEquals([], $entity->getData());
        //setData (from scratch)
        $entity->setData(['one' => true, 'two' => true]);
        $this->assertEquals(true, $entity->getOne());
        $this->assertEquals(true, $entity->getTwo());
        //setData (mege)
        $entity->setData(['one' => false]);
        $this->assertEquals(false, $entity->getOne());
        $this->assertEquals(true, $entity->getTwo());
        //getData (with data)
        $this->assertEquals(['one' => false, 'two' => true], $entity->getData());
    }

    public function testSave(): void
    {
        $data = ['email' => 'example@example.com', 'name' => 'Test user'];
        $entity = $this->container['entity']('user');
        $entity->setData($data);
        $this->assertEquals(null, $entity->getId());
        $entity->save();
        $this->assertEquals(true, is_numeric($entity->getId()));
        $entity->setData(['name' => 'New test']);
        $entity->save();
        $this->assertEquals('New test', $entity->getName());
    }
}
