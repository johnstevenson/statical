<?php
namespace Statical\Tests;

use Statical\Tests\Fixtures\Manager;
use Statical\Tests\Fixtures\Utils;

class ManagerTest extends \PHPUnit_Framework_TestCase
{
    protected $manager;

    public function setUp()
    {
        $this->manager = new Manager();
    }

    public function tearDown()
    {
        if ($this->manager) {
            $this->manager->disable();
        }
    }

    /**
    * Test that proxy instance is registered correctly in the registry array.
    *
    */
    public function testAddProxyWithInstance()
    {
        $proxy = 'Statical\\Tests\\Fixtures\\FooProxy';
        $id = null;
        $target = Utils::fooInstance();

        $this->manager->addProxy($proxy, $id, $target);
        $registry = $this->manager->registry;

        $this->assertArrayHasKey($proxy, $registry);
        $values = $registry[$proxy];

        $this->assertNull($values['id']);
        $this->assertSame($target, $values['target']);
        $this->assertNull($values['closure']);
    }

    /**
    * Test that proxy closure is registered correctly in the registry array.
    *
    */
    public function testAddProxyWithClosure()
    {
        $proxy = 'Statical\\Tests\\Fixtures\\FooProxy';
        $id = null;
        $target = Utils::fooClosure();

        $this->manager->addProxy($proxy, $id, $target);
        $registry = $this->manager->registry;

        $this->assertArrayHasKey($proxy, $registry);
        $values = $registry[$proxy];

        $this->assertNull($values['id']);
        $this->assertNull($values['target']);
        $this->assertSame($target, $values['closure']);
    }

    /**
    * Test that proxy service is registered correctly in the registry array.
    *
    */
    public function testAddProxyWithService()
    {
        $proxy = 'Statical\\Tests\\Fixtures\\FooProxy';
        $id = 'foo';
        $target = Utils::formatContainer(Utils::customContainer());

        $this->manager->addProxy($proxy, $id, $target);
        $registry = $this->manager->registry;

        $this->assertArrayHasKey($proxy, $registry);
        $values = $registry[$proxy];

        $this->assertSame($id, $values['id']);
        $this->assertSame($target, $values['target']);
        $this->assertNull($values['closure']);
    }

    /**
    * Test that getProxyTarget returns the instance
    *
    */
    public function testGetProxyTargetWithInstance()
    {
        $proxy = 'Statical\\Tests\\Fixtures\\FooProxy';
        $id = null;
        $target = Utils::fooInstance();

        $this->manager->addProxy($proxy, $id, $target);
        $instance = $this->manager->getProxyTarget($proxy);

        $this->assertSame($instance, $target);
    }

    /**
    * Test that getProxyTarget returns the closure instance
    *
    */
    public function testGetProxyTargetWithClosure()
    {
        $proxy = 'Statical\\Tests\\Fixtures\\FooProxy';
        $id = null;
        $target = Utils::fooClosure();

        $this->manager->addProxy($proxy, $id, $target);
        $instance = $this->manager->getProxyTarget($proxy);

        $this->assertTrue($instance instanceof \Statical\Tests\Fixtures\Foo);
    }

    /**
    * Test that getProxyTarget returns the service instance
    *
    */
    public function testGetProxyTargetWithService()
    {
        $proxy = 'Statical\\Tests\\Fixtures\\FooProxy';
        $id = 'foo';

        // Set up the container
        $foo = Utils::fooInstance();
        $container = Utils::customContainer();
        $container->set($id, $foo);
        $target = Utils::formatContainer($container);

        $this->manager->addProxy($proxy, $id, $target);
        $instance = $this->manager->getProxyTarget($proxy);

        $this->assertSame($instance, $foo);
    }

    /**
    * Test that getProxyTarget throws an exception when the target is missing.
    *
    * @expectedException RuntimeException
    */
    public function testGetProxyTargetNotRegisteredFails()
    {
        $proxy = 'Statical\\Tests\\Fixtures\\FooProxy';
        $this->manager->getProxyTarget($proxy);
    }
}
