<?php
namespace Statical\Tests\Fixtures;

use Statical\Tests\Fixtures\Manager;

class TestBase extends \PHPUnit_Framework_TestCase
{
    protected $manager;

    public function setUp()
    {
        $this->manager = new Manager();
    }

    public function tearDown()
    {
        if ($this->manager) {
            $this->manager->disable();
        }
    }

    public function replaceManager($container = null, $config = array())
    {
        $this->manager = new Manager($container, $config);
    }
}
