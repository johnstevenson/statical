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
    * Test that formatInput throws an exception for invalid values.
    *
    */
    public function testFormatInputFailsWhenInvalid()
    {
        $defaults = $this->config->getDefault();

        foreach ($defaults as $key => $default) {
            // Copy $default to $config then make the test value invalid
            $config = $defaults;
            $config[$key] = new \stdClass();

            try {
                $this->config->formatInput($config);
            } catch (\InvalidArgumentException $e) {
                continue;
            }

            $this->fail('Expected exception not raised for ' . $key);
        }
    }

    /**
    * Test that formatInput throws an exception if container is invalid.
    *
    * @expectedException InvalidArgumentException
    * @expectedExceptionMessage Container must be a callable
    */
    public function testFormatInputFailsWithInvalidContainer()
    {
        $config['container'] = '';

        $this->config->formatInput($config);
    }

    /**
    * Test that formatInput adds container to service values
    *
    */
    public function testFormatInputAddsContainer()
    {
        $container = Utils::container();
        $config['services'] = array();
        $config['services'][] = array('Foo', 'Name\\Space\\Proxy');
        $config['container'] = $container;

        $result = $this->config->formatInput($config);
        $expected = Utils::formatContainer($container);

        $this->assertSame($expected, $result['services'][0][2]);
    }
}
