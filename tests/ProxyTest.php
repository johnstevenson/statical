<?php
namespace Statical\Tests;

use Statical\Tests\Fixtures\TestBase;
use Statical\Tests\Fixtures\Utils;

/**
 * @runTestsInSeparateProcesses
 */
class ProxyTest extends TestBase
{
    protected $fooInstance;
    protected $barInstance;

    public function setUp()
    {
        $this->fooInstance = Utils::fooInstance();
        $this->barInstance = Utils::barInstance();
        parent::setUp();
    }

    /**
    * Test we can add a proxy instance
    *
    */
    public function testAddProxyInstance()
    {
        $alias = 'Foo';
        $proxy = 'Statical\\Tests\\Fixtures\\FooProxy';

        $this->manager->addProxyInstance($alias, $proxy, $this->fooInstance);

        $expected = get_class($this->fooInstance);
        $this->assertEquals($expected, \Foo::getClass());
    }

    /**
    * Test we can add a proxy instance with a namespace
    *
    */
    public function testAddProxyInstanceWithNamespace()
    {
        $alias = 'Foo';
        $proxy = 'Statical\\Tests\\Fixtures\\FooProxy';

        $namespace = 'Statical\\Tests';
        $this->manager->addProxyInstance($alias, $proxy, $this->fooInstance, $namespace);

        $expected = get_class($this->fooInstance);
        $this->assertEquals($expected, Foo::getClass());
    }

    /**
    * Test we can add a proxy instance with a closure
    *
    */
    public function testAddProxyInstanceClosure()
    {
        $alias = 'Foo';
        $proxy = 'Statical\\Tests\\Fixtures\\FooProxy';
        $closure = Utils::fooClosure();

        $this->manager->addProxyInstance($alias, $proxy, $closure);

        $expected = get_class($this->fooInstance);
        $this->assertEquals($expected, \Foo::getClass());
    }

    /**
    * Test that a proxy instance with a closure resolves only once
    *
    */
    public function testAddProxyInstanceClosureResolvesOnce()
    {
        $alias = 'Foo';
        $proxy = 'Statical\\Tests\\Fixtures\\FooProxy';
        $closure = Utils::fooClosure();

        $this->manager->addProxyInstance($alias, $proxy, $closure);

        $this->assertSame(\Foo::getInstance(), \Foo::getInstance());
    }

    /**
    * Test we can replace a proxy instance
    *
    */
    public function testReplaceProxyInstance()
    {
        $alias = 'Foo';
        $proxy = 'Statical\\Tests\\Fixtures\\FooProxy';

        // Use fooInstance first
        $this->manager->addProxyInstance($alias, $proxy, $this->fooInstance);

        $expected = get_class($this->fooInstance);
        $this->assertEquals($expected, \Foo::getClass());

        // Replace with barInstance
        $this->manager->addProxyInstance($alias, $proxy, $this->barInstance);

        $expected = get_class($this->barInstance);
        $this->assertEquals($expected, \Foo::getClass());
    }

    /**
    * Test that null proxy instance fails.
    *
    * @expectedException InvalidArgumentException
    */
    public function testAddProxyWithNullInstanceFails()
    {
        $alias = 'Foo';
        $proxy = 'Statical\\Tests\\Fixtures\\FooProxy';

        $this->manager->addProxyInstance($alias, $proxy, null);
    }

    /**
    * Test we can add a proxy service from an array container
    *
    */
    public function testAddProxyServiceFromArrayContainer()
    {
        $alias = 'Foo';
        $proxy = 'Statical\\Tests\\Fixtures\\FooProxy';
        $id = 'foo';
        $container = Utils::arrayContainer();

        // Set the instance in the container and register it
        $container->set($id, $this->fooInstance);
        $this->manager->addProxyService($alias, $proxy, $container, $id);

        $expected = get_class($this->fooInstance);
        $this->assertEquals($expected, \Foo::getClass());

        // Set the closure in the container and register it
        $id = 'foo2';
        $container->set($id, Utils::fooClosure());
        $this->manager->addProxyService($alias, $proxy, $container, $id);

        $this->assertEquals($expected, \Foo::getClass());
    }

    /**
    * Test we can add a proxy service from an standard container
    *
    */
    public function testAddProxyServiceFromStandardContainer()
    {
        $alias = 'Foo';
        $proxy = 'Statical\\Tests\\Fixtures\\FooProxy';
        $id = 'foo';
        $container = Utils::container();

        // Set the instance in the container and register it
        $container->set($id, $this->fooInstance);
        $this->manager->addProxyService($alias, $proxy, $container, $id);

        $expected = get_class($this->fooInstance);
        $this->assertEquals($expected, \Foo::getClass());

        // Set the closure in the container and register it
        $id = 'foo2';
        $container->set($id, Utils::fooClosure());
        $this->manager->addProxyService($alias, $proxy, $container, $id);

        $this->assertEquals($expected, \Foo::getClass());
    }

    /**
    * Test we can add a proxy service from a custom container
    *
    */
    public function testAddProxyServiceFromCustomContainer()
    {
        $alias = 'Foo';
        $proxy = 'Statical\\Tests\\Fixtures\\FooProxy';
        $id = 'foo';

        // Use a custom container
        $container = Utils::customContainer();

        // Set the instance in the container and register it
        $container->set($id, $this->fooInstance);
        $this->manager->addProxyService($alias, $proxy, Utils::formatContainer($container), $id);

        $expected = get_class($this->fooInstance);
        $this->assertEquals($expected, \Foo::getClass());

        // Set the closure in the container and register it
        $id = 'foo2';
        $container->set($id, Utils::fooClosure());
        $this->manager->addProxyService($alias, $proxy, Utils::formatContainer($container), $id);

        $this->assertEquals($expected, \Foo::getClass());
    }

    /**
    * Test we can add a proxy service using different containers
    *
    */
    public function testAddProxyServiceMultipleContainer()
    {
        $proxy = 'Statical\\Tests\\Fixtures\\FooProxy';
        $id = 'foo';

        // Use a standard container
        $container = Utils::container();

        // Set the instance in the container and register the id
        $alias = 'Foo1';
        $container->set($id, $this->fooInstance);
        $this->manager->addProxyService($alias, $proxy, $container, $id);

        // Use an array container
        $container = Utils::arrayContainer();

        // Set the closure in the container and register it
        $alias = 'Foo2';
        $container->set($id, Utils::fooClosure());
        $this->manager->addProxyService($alias, $proxy, $container, $id);

        // Use a custom container
        $container = Utils::customContainer();

        // Set the instance in the container and register it
        $alias = 'Foo3';
        $container->set($id, $this->fooInstance);
        $this->manager->addProxyService($alias, $proxy, Utils::formatContainer($container), $id);

        $expected = get_class($this->fooInstance);

        $this->assertEquals($expected, \Foo1::getClass());
        $this->assertEquals($expected, \Foo2::getClass());
        $this->assertEquals($expected, \Foo3::getClass());
    }

    /**
    * Test we can add a proxy service with a namespace
    *
    */
    public function testAddProxyServiceWithNamespace()
    {
        $alias = 'Foo';
        $proxy = 'Statical\\Tests\\Fixtures\\FooProxy';
        $id = 'foo';
        $container = Utils::container();
        $namespace = 'Statical\\Tests';

        // Set the instance in the container and register it with the namespace
        $container->set($id, $this->fooInstance);
        $this->manager->addProxyService($alias, $proxy, $container, $id, $namespace);

        $expected = get_class($this->fooInstance);
        $this->assertEquals($expected, Foo::getClass());
    }

    /**
    * Test we can replace a proxy service
    *
    */
    public function testReplaceProxyService()
    {
        $alias = 'Foo';
        $proxy = 'Statical\\Tests\\Fixtures\\FooProxy';
        $id = 'foo';
        $container = Utils::container();

        // Set foo instance in the container and register it
        $container->set($id, $this->fooInstance);
        $this->manager->addProxyService($alias, $proxy, $container, $id);

        $expected = get_class($this->fooInstance);
        $this->assertEquals($expected, \Foo::getClass());

        $id = 'bar';
        // Set bar closure in the container and register it
        $container->set($id, Utils::barClosure());
        $this->manager->addProxyService($alias, $proxy, $container, $id);

        $expected = get_class($this->barInstance);
        $this->assertEquals($expected, \Foo::getClass());
    }

    /**
    * Test a proxy service can be swapped
    *
    */
    public function testSwapProxyService()
    {
        $alias = 'Foo';
        $proxy = 'Statical\\Tests\\Fixtures\\FooProxy';
        $id = 'foo';
        $container = Utils::container();

        // Set foo instance in the container and register it
        $container->set($id, $this->fooInstance);
        $this->manager->addProxyService($alias, $proxy, $container, $id);

        $expected = get_class($this->fooInstance);
        $this->assertEquals($expected, \Foo::getClass());

        // Set bar closure in the container
        $container->set($id, Utils::barClosure());

        $expected = get_class($this->barInstance);
        $this->assertEquals($expected, \Foo::getClass());
    }

    /**
    * Test addProxySelf allows us to use Statical alias in any namespace
    *
    */
    public function testAddProxySelf()
    {
        $manager = new \Statical\Manager('none');
        $manager->addProxySelf();
        $this->assertSame($manager, Statical::getInstance());
    }
}
