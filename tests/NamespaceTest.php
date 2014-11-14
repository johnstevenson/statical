<?php
namespace Statical\Tests;

use Statical\Manager;
use Statical\Tests\Fixtures\Utils;

/**
 * @runTestsInSeparateProcesses
 */
class NamespaceTest extends \PHPUnit_Framework_TestCase
{
    protected $manager;

    public function setUp()
    {
        $this->manager = new Manager('enable');
    }

    /**
    * Test Foo can be called in Statical\\Tests namespace
    *
    */
    public function testAddNamespaceRoot()
    {
        $alias = 'Foo';
        $proxy = 'Statical\\Tests\\Fixtures\\FooProxy';
        $instance = new \Statical\Tests\Fixtures\Foo();

        $this->manager->addProxyInstance($alias, $proxy, $instance);
        $this->manager->addNamespace('Foo', 'Statical\\Tests');

        $expected = get_class($instance);
        $this->assertEquals($expected, Foo::getClass());
    }

    /**
    * Test Foo can be called in Statical\\* namespace
    *
    */
    public function testAddNamespaceBase()
    {
        $alias = 'Foo';
        $proxy = 'Statical\\Tests\\Fixtures\\FooProxy';
        $instance = new \Statical\Tests\Fixtures\Foo();

        $this->manager->addProxyInstance($alias, $proxy, $instance);
        $this->manager->addNamespace('Foo', 'Statical\\*');

        $expected = get_class($instance);
        $this->assertEquals($expected, Foo::getClass());
    }

    /**
    * Test Foo can be called in * namespace
    *
    */
    public function testAddNamespaceAny()
    {
        $alias = 'Foo';
        $proxy = 'Statical\\Tests\\Fixtures\\FooProxy';
        $instance = new \Statical\Tests\Fixtures\Foo();

        $this->manager->addProxyInstance($alias, $proxy, $instance);
        $this->manager->addNamespace('Foo', '*');

        $expected = get_class($instance);
        $this->assertEquals($expected, Foo::getClass());
    }

    /**
    * Test Foo can be called in Statical\\Tests namespace
    *
    */
    public function testAddNamespaceGroupName()
    {
        $alias = 'Foo';
        $proxy = 'Statical\\Tests\\Fixtures\\FooProxy';
        $instance = new \Statical\Tests\Fixtures\Foo();

        $this->manager->addProxyInstance($alias, $proxy, $instance);
        $this->manager->addNamespaceGroup('name', 'Foo', 'Statical\\Tests');

        $expected = get_class($instance);
        $this->assertEquals($expected, Foo::getClass());
    }

    /**
    * Test Foo can be called in Statical\\* namespace
    *
    */
    public function testAddNamespaceGroupPath()
    {
        $alias = 'Foo';
        $proxy = 'Statical\\Tests\\Fixtures\\FooProxy';
        $instance = new \Statical\Tests\Fixtures\Foo();

        $this->manager->addProxyInstance($alias, $proxy, $instance);
        $this->manager->addNamespaceGroup('path', 'Foo', 'Statical');

        $expected = get_class($instance);
        $this->assertEquals($expected, Foo::getClass());
    }

    /**
    * Test Foo can be called in * namespace
    *
    */
    public function testAddNamespaceGroupAny()
    {
        $alias = 'Foo';
        $proxy = 'Statical\\Tests\\Fixtures\\FooProxy';
        $instance = new \Statical\Tests\Fixtures\Foo();

        $this->manager->addProxyInstance($alias, $proxy, $instance);
        $this->manager->addNamespaceGroup('any', 'Foo');

        $expected = get_class($instance);
        $this->assertEquals($expected, Foo::getClass());
    }

    /**
    * Test * can be called in Statical\\Tests namespace
    *
    */
    public function testAddNamespaceGlobal()
    {
        $alias = 'Foo';
        $proxy = 'Statical\\Tests\\Fixtures\\FooProxy';
        $instance = new \Statical\Tests\Fixtures\Foo();

        $this->manager->addProxyInstance($alias, $proxy, $instance);
        $this->manager->addNamespace('*', 'Statical\\Tests');

        $expected = get_class($instance);
        $this->assertEquals($expected, Foo::getClass());
    }

    /**
    * Test Foo can be called from multiple namespaces
    *
    */
    public function testAddNamespaceMultiple()
    {
        $alias = 'Foo';
        $proxy = 'Statical\\Tests\\Fixtures\\FooProxy';
        $instance = new \Statical\Tests\Fixtures\Foo();

        $this->manager->addProxyInstance($alias, $proxy, $instance);

        // add namespaces for this file and App
        $namespace = array('Statical\\Tests', 'App\\*');
        $this->manager->addNamespace('Foo', $namespace);

        // test we can call initial Foo
        $expected = get_class($instance);
        $this->assertEquals($expected, Foo::getClass());

        // register new autoloader for App namespaces
        Utils::registerAppLoader();

        $dirs = array('Controllers', 'Models', 'Views');
        $directories = array_merge($dirs, $dirs);

        foreach ($directories as $dir) {
            $class = '\\App\\Library\\'.$dir.'\\Caller';
            $caller = new $class();
            $this->assertEquals($expected, $caller->callFoo());

            $this->assertEquals($expected, Foo::getClass());
        }
    }

    /**
    * Demonstrate major pitfall when another class has the same name as an
    * aliased one.
    *
    */
    public function testNamespacePitfallFail()
    {
        // register new autoloader after alias autoloader
        Utils::registerAppLoader();

        $alias = 'Foo';
        $proxy = 'Statical\\Tests\\Fixtures\\FooProxy';
        $instance = new \Statical\Tests\Fixtures\Foo();

        $this->manager->addProxyInstance($alias, $proxy, $instance);

        // add namespaces for this file and App
        $namespace = array('Statical\\Tests', 'App\\*');
        $this->manager->addNamespace('Foo', $namespace);

        // test we can call initial Foo
        $expected = get_class($instance);
        $this->assertEquals($expected, Foo::getClass());

        // test that wrong Foo is loaded
        $otherFoo = new \App\Library\Foo();
        $expected = get_class($otherFoo);
        $this->assertEquals($expected, $proxy);
    }

    /**
    * Test that enable allows another class to have same name as an aliased one.
    *
    */
    public function testNamespacePitfallPass()
    {
        // register new autoloader after alias autoloader
        Utils::registerAppLoader();

        // call enable again to put alias autoloader last
        $this->manager->enable();

        $alias = 'Foo';
        $proxy = 'Statical\\Tests\\Fixtures\\FooProxy';
        $instance = new \Statical\Tests\Fixtures\Foo();

        $this->manager->addProxyInstance($alias, $proxy, $instance);

        // add namespaces for this file and App
        $namespace = array('Statical\\Tests', 'App\\*');
        $this->manager->addNamespace('Foo', $namespace);

        // test we can call initial Foo
        $expected = get_class($instance);
        $this->assertEquals($expected, Foo::getClass());

        // test that the correct Foo is loaded
        $otherFoo = new \App\Library\Foo();
        $expected = get_class($otherFoo);
        $this->assertEquals($expected, $otherFoo->getClass());
    }
}

