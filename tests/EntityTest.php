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

    public function testLoad(): void
    {
        $this->testSave();
        $entity = $this->container['entity']('user');
        $this->assertEquals([], $entity->getData());
        $entity->load('example@example.com', 'email');
        $this->assertEquals('example@example.com', $entity->getEmail());
    }

    public function testLoadAll(): void
    {
        $this->testSave();
        $entity = $this->container['entity']('user');
        $collection = $entity->loadAll(['email' => 'example@example.com']);
        $this->assertInstanceOf('\Slim\Interfaces\CollectionInterface', $collection);
        $this->assertEquals(true, ($collection->count() > 0));
    }

    public function testHas(): void
    {
        $this->testSave();
        $entity = $this->container['entity']('user');
        $this->assertEquals(true, $entity->has(['email' => 'example@example.com']));
    }

    public function testCount(): void
    {
        $this->testSave();
        $entity = $this->container['entity']('user');
        $this->assertEquals(true, ($entity->count(['email' => 'example@example.com']) > 0));
    }

    public function testDelete(): void
    {
        $this->testSave();
        $entity = $this->container['entity']('user')->load('example@example.com', 'email');
        $this->assertEquals(true, (bool) $entity->getId());
        $id = $entity->getId();
        $this->assertEquals(true, $entity->delete());
        $this->assertEquals(false, $entity->has(['id' => $id]));
    }

    public function testLoadRelation(): void
    {
        //create some articles
        $user = $this->container['entity']('user')->load('example@example.com', 'email');
        $this->assertEquals(true, (bool) $user->getId());
        $article = $this->container['entity']('article');
        $article->setData([
            'author_id' => $user->getId(),
            'title' => 'Test',
            'description' => 'test',
        ]);
        foreach (range(1, 5) as $i) {
            $article->setId(null);
            $article->save();
        }

        $collection = $user->getArticles();
        $this->assertInstanceOf('\Slim\Interfaces\CollectionInterface', $collection);
        $this->assertEquals(true, ($collection->count() > 0));
        $this->assertEquals(null, $user->getErrorRelation());
    }
}
