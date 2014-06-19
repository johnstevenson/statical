<?php
namespace Statical\Tests;

use Statical\Tests\Fixtures\Manager;
use Statical\Tests\Fixtures\ConfigHandler;
use Statical\Tests\Fixtures\Utils;

class ConfigHandlerTest extends \PHPUnit_Framework_TestCase
{
    protected $manager;
    protected $config;

    public function setUp()
    {
        $this->manager = new Manager;
        $this->config = new ConfigHandler($this->manager);
    }

    /**
    * Test that checkInput throws an exception if container is null.
    *
    * @expectedException InvalidArgumentException
    * @expectedExceptionMessage Container must be a callable
    */
    public function testCheckInputFailsWithNullContainer()
    {
        $config = array(
            'instances' => array(
                'Foo' => 'Proxies\Foo'
            )
        );

        $this->config->apply($config, null);
    }

    /**
    * Test that checkInput does not throw an execption with a null container if
    * it is not required.
    *
    */
    public function testCheckInputAllowsNullContainerWhenNotRequired()
    {
        $config = array(
            'boot' => 'self'
        );

        $this->config->apply($config, null);
    }

    /**
    * Test that getItem throws an exception for invalid values.
    *
    */
    public function testGetItemFailsWhenInvalid()
    {
        $defaults = $this->config->getDefault();

        foreach ($defaults as $key => $default) {
            // Copy $default to $config then make the test value null
            $config = $defaults;
            $config[$key] = new \stdClass();

            try {
                $this->config->getItem($config, $key, $default);
            } catch (\InvalidArgumentException $e) {
                continue;
            }

            $this->fail('Expected exception not raised for ' . $key);
        }
    }

    /**
    * Test that checkInput formats array values to an array
    *
    */
    public function testCheckInputFormatsArrayValues()
    {
        $config = array(
            'instances' => array(
                'Foo' => 'Proxies\Foo'
            ),
            'services' => array(
                'Bar' => array('Proxies\Bar', 'baz')
            ),
            'namespaces' => array(
                'Foo' => '*'
            ),
            'boot' => 'enable'
        );

        $expected = array(
            'instances' => array(
                'Foo' => array('Proxies\Foo', '')
            ),
            'services' => array(
                'Bar' => array('Proxies\Bar', 'baz')
            ),
            'namespaces' => array(
                'Foo' => array('*', '')
            ),
            'boot' => 'enable'
        );


        $container = Utils::container();
        $this->manager->setContainer($container);
        $this->config->apply($config, $container);

        $this->assertSame($expected, $this->config->config);
    }
}
