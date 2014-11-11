<?php
namespace Statical\Tests;

use Statical\Manager;

/**
 * @runTestsInSeparateProcesses
 */
class SingletonTest extends \PHPUnit_Framework_TestCase
{
    /**
    * Test that we can create a new manager if we are not set as a singleton.
    *
    */
    public function testCreateNewNoSingleton()
    {
        $manager = new Manager();
        $newManager = new Manager();
        $this->assertNotSame($manager, $newManager);
    }

    /**
    * Test that we cannnot create a new manager if we have been set as a singleton.
    *
    * @expectedException RuntimeException
    */
    public function testCreateNewFailsWhenSingleton()
    {
        $manager = new Manager();
        $manager->makeSingleton();
        $newManager = new Manager();
    }
}
