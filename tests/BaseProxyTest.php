<?php
namespace Statical\Tests;

use Statical\Manager;
use Statical\Tests\Fixtures\Utils;

/**
 * @runTestsInSeparateProcesses
 */
class BaseProxyTest extends \PHPUnit_Framework_TestCase
{
    /**
    * Test that we can get a target instance
    *
    */
    public function testGetInstance()
    {
        $manager = new Manager();

        $alias = 'Foo';
        $proxy = 'Statical\\Tests\\Fixtures\\FooProxy';
        $instance = Utils::fooInstance();

        $manager->addProxyInstance($alias, $proxy, $instance);
        $this->assertSame($instance, \Foo::getInstance());
    }

    /**
    * Test an exception is thrown if the proxy is not registered
    *
    * @expectedException RuntimeException
    * @expectedExceptionMessage Resolver not set
    */
    public function testGetInstanceFailNotSet()
    {
        \Statical\Tests\Fixtures\FooProxy::getClass();
    }
}
