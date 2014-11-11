<?php
namespace Statical\Tests;

use Statical\Tests\Fixtures\TestBase;
use Statical\Tests\Fixtures\Manager;

require 'bootstrap.php';

/**
 * @runTestsInSeparateProcesses
 * @backupGlobals disabled
 * @preserveGlobalState disabled
 */
class SingletonTest extends TestBase
{
    /**
    * Test that we can create a new manager if we are not set as a singleton.
    *
    */
    public function testCreateNewNoSingleton()
    {
        $newManager = new Manager();
        $this->assertNotSame($this->manager, $newManager);
    }

    /**
    * Test that we cannnot create a new manager if we have been set as a singleton.
    *
    * @expectedException RuntimeException
    */
    public function testCreateNewFailsWhenSingleton()
    {
        $this->manager->makeSingleton();
        $newManager = new Manager();
    }
}
