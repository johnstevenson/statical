<?php
namespace Statical\Tests;

use Statical\Input;
use Statical\Tests\Fixtures\Utils;

class InputTest extends \PHPUnit_Framework_TestCase
{
    /**
    * Test checkContainer returns a formatted container
    *
    */
    public function testCheckContainer()
    {
        $container = Utils::container();
        $expected = Utils::formatContainer($container);
        $this->AssertSame($expected, Input::checkContainer($container));
    }

    /**
    * Test checkContainer fails with a non-object instance.
    *
    * @expectedException InvalidArgumentException
    */
    public function testCheckContainerFailsInvalidInstance()
    {
        $container = array('string', 'getValue');
        Input::checkContainer($container);
    }

    /**
    * Test checkContainer fails with a non-existent method.
    *
    * @expectedException InvalidArgumentException
    */
    public function testCheckContainerFailsInvalidMethod()
    {
        $container = array(Utils::arrayContainer(), 'getMyValue');
        Input::checkContainer($container);
    }

    /**
    * Test checkContainer fails with a class missing the default method.
    *
    * @expectedException InvalidArgumentException
    */
    public function testCheckContainerFailsNoDefaultMethod()
    {
        $container = new \stdClass();
        Input::checkContainer($container);
    }

    /**
    * Test checkContainerEx throws an exception when passed a null container
    * and the default has not been set.
    *
    * @expectedException RuntimeException
    */
    public function testCheckContainerExFailsNoDefaultOnNull()
    {
        Input::checkContainerEx(null, null);
    }

    /**
    * Test checkContainerEx returns the default container when null is passed in.
    *
    */
    public function testCheckContainerExReturnsDefaultOnNull()
    {
        $container = Utils::container();
        $default = Utils::formatContainer($container);

        $this->AssertSame($default, Input::checkContainerEx(null, $default));
    }
}

