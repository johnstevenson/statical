<?php
namespace Statical\Tests;

use Statical\Manager;
use Statical\Tests\Fixtures\Utils;

/**
 * @runTestsInSeparateProcesses
 */
class BootTest extends \PHPUnit_Framework_TestCase
{
    /**
    * Test default boot from constructor.
    *
    */
    public function testBootDefault()
    {
        $manager = new Manager();

        $this->assertSame($manager, Statical::getInstance());
    }

    /**
    * Test boot from constructor with no namespacing.
    *
    */
    public function testBootNoNamespacing()
    {
        $manager = new Manager('enable', false);

        $alias = 'Foo';
        $proxy = 'Statical\\Tests\\Fixtures\\FooProxy';
        $instance = Utils::fooInstance();
        $namespace = array('Statical\\Tests');

        $manager->addProxyInstance($alias, $proxy, $instance, $namespace);
        $this->assertFalse(class_exists(__NAMESPACE__.'\\Foo'), false);
    }

    /**
    * Test boot enable from constructor.
    *
    */
    public function testBootEnable()
    {
        $manager = new Manager('enable');

        $alias = 'Foo';
        $proxy = 'Statical\\Tests\\Fixtures\\FooProxy';
        $instance = Utils::fooInstance();

        $manager->addProxyInstance($alias, $proxy, $instance);

        $expected = get_class($instance);
        $this->assertEquals($expected, \Foo::getClass());
    }

    /**
    * Test boot none from constructor.
    *
    */
    public function testBootNone()
    {
        $manager = new Manager('none');

        $alias = 'Foo';
        $proxy = 'Statical\\Tests\\Fixtures\\FooProxy';
        $instance = Utils::fooInstance();

        $manager->addProxyInstance($alias, $proxy, $instance);
        $this->assertFalse(class_exists(__NAMESPACE__.'\\Foo'), false);
    }

    /**
    * Test invalid boot value from constructor.
    *
    * @expectedException InvalidArgumentException
    */
    public function testBootInvalid()
    {
        $manager = new Manager('');
    }
}
