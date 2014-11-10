<?php
namespace Statical\Tests;

use Statical\Tests\Fixtures\Manager;
use Statical\Tests\Fixtures\Config;
use Statical\Tests\Fixtures\Utils;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    protected $manager;
    protected $config;

    public function setUp()
    {
        $this->manager = new Manager;
        $this->config = new Config($this->manager);
    }

    /**
    * Test that formatInput throws an exception for invalid values.
    *
    */
    public function testFormatInputFailsWhenInvalid()
    {
        $defaults = Config::getEmpty();

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
}
