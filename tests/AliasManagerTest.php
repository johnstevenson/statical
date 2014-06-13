<?php
namespace Statical\Tests;

use Statical\Tests\Fixtures\AliasManager;
use Statical\Tests\Fixtures\Utils;

class AliasManagerTest extends \PHPUnit_Framework_TestCase
{
    protected $manager;

    public function setUp()
    {
        $this->manager = new AliasManager();
        Utils::unregisterAppLoader();
    }

    /**
    * Test that class aliases are added correctly to the aliases array.
    *
    * The aliases array is a key-value array where:
    * key: alias name
    * value: original classname
    */
    public function testAddClassAlias()
    {
        $original = 'Statical\\Tests\\Fixtures\\Foo';
        $alias = 'Foo';

        $this->manager->add($original, $alias);

        // key => $alias, value = $original
        $this->assertEquals($this->manager->aliases[$alias], $original);
    }

    public function testGetNamespaceAlias()
    {
        $original = 'Statical\\Tests\\Fixtures\\Foo';
        $alias = 'Foo';
        $this->manager->add($original, $alias);
        $this->manager->addNamespace($alias, 'Bar\\Baz');

        $this->assertEquals($alias,
            $this->manager->getNamespaceAlias('Bar\\Baz\\Foo'));
    }

    public function testGetNamespaceAliasReturnsNull()
    {
        $original = 'Statical\\Tests\\Fixtures\\Foo';
        $alias = 'Foo';
        $this->manager->add($original, $alias);
        $this->manager->addNamespace($alias, 'Bar\\Baz');

        $this->assertEquals(null,
            $this->manager->getNamespaceAlias('Bar\\Foo'));
    }

    /**
    * Test we can enable the autoloader without namespacing.
    *
    */
    public function testEnable()
    {
        $this->manager->enable();

        $this->assertTrue($this->loaderRegistered());
        $this->assertTrue($this->loaderLast());
        $this->assertFalse($this->usingNamespacing());
    }

    /**
    * Test we can enable the autoloader with namespacing.
    *
    */
    public function testEnableUseNamespacing()
    {
        $this->manager->enable(true);

        $this->assertTrue($this->loaderRegistered());
        $this->assertTrue($this->loaderLast());
        $this->assertTrue($this->usingNamespacing());
    }

    /**
    * Test that updating a non-namespacing enable without namespacing does not
    * re-register the autoloader on the end.
    *
    * We can check this by registering a new autoloader then checking that the
    * position of ours has not changed.
    */
    public function testEnableUpdate()
    {
        $this->manager->enable();

        $this->assertTrue($this->loaderRegistered());
        $this->assertTrue($this->loaderLast());
        $this->assertFalse($this->usingNamespacing());

        Utils::registerAppLoader();

        // test that the loader is still registered and is no longer at the end
        $this->assertTrue($this->loaderRegistered());
        $this->assertFalse($this->loaderLast());

        $this->manager->enable();
        $this->assertTrue($this->loaderRegistered());

        // test that the loader has not been re-registered on the end
        $this->assertFalse($this->loaderLast());
        $this->assertFalse($this->usingNamespacing());
    }

    /**
    * Test that updating a namespacing enable with namespacing re-registers the
    * autoloader on the end.
    *
    * We can check this by registering a new autoloader then checking that the
    * position of ours has changed.
    */
    public function testEnableUpdateNamespacing()
    {
        $this->manager->enable(true);

        $this->assertTrue($this->loaderRegistered());
        $this->assertTrue($this->loaderLast());
        $this->assertTrue($this->usingNamespacing());

        Utils::registerAppLoader();

        // test that the loader is still registered and is no longer at the end
        $this->assertTrue($this->loaderRegistered());
        $this->assertFalse($this->loaderLast());

        $this->manager->enable(true);
        $this->assertTrue($this->loaderRegistered());

        // test that the loader has been re-registered on the end
        $this->assertTrue($this->loaderLast());
        $this->assertTrue($this->usingNamespacing());
    }

    /**
    * Test that updating a non-namespacing enable with namespacing re-registers
    * the autoloader on the end.
    *
    * We can check this by registering a new autoloader then checking that the
    * position of ours has changed.
    */
    public function testEnableUpdateAddNamespacing()
    {
        $this->manager->enable();

        $this->assertTrue($this->loaderRegistered());
        $this->assertTrue($this->loaderLast());
        $this->assertFalse($this->usingNamespacing());

        Utils::registerAppLoader();

        // test that the loader is still registered and is no longer at the end
        $this->assertTrue($this->loaderRegistered());
        $this->assertFalse($this->loaderLast());

        $this->manager->enable(true);

        // test that the loader is still registered
        $this->assertTrue($this->loaderRegistered());

        // test that the loader has been re-registered on the end
        $this->assertTrue($this->loaderLast());

        // test that usingNamespacing has changed to true
        $this->assertTrue($this->usingNamespacing());
    }

    /**
    * Test that updating a namespacing enable without namespacing does not make
    * and changes.
    *
    * We can check this by registering a new autoloader then checking that the
    * position of ours has changed and that namespacing is still enabled.
    */
    public function testEnableUpdateRemoveNamespacing()
    {
        $this->manager->enable(true);

        $this->assertTrue($this->loaderRegistered());
        $this->assertTrue($this->loaderLast());
        $this->assertTrue($this->usingNamespacing());

        Utils::registerAppLoader();

        // test that the loader is still registered and is no longer at the end
        $this->assertTrue($this->loaderRegistered());
        $this->assertFalse($this->loaderLast());

        // call enable without a usingNamespacing value
        $this->manager->enable();

        // test that the loader is still registered
        $this->assertTrue($this->loaderRegistered());

        // test that the loader has not been re-registered on the end
        $this->assertFalse($this->loaderLast());

        // test that usingNamespacing is still true
        $this->assertTrue($this->usingNamespacing());
    }

    /**
    * Test we can disable the autoloader and namespacing.
    *
    */
    public function testDisable()
    {
        $this->manager->enable(true);

        $this->assertTrue($this->loaderRegistered());
        $this->assertTrue($this->loaderLast());
        $this->assertTrue($this->usingNamespacing());

        $this->manager->disable();
        $this->assertFalse($this->loaderRegistered());
        $this->assertFalse($this->usingNamespacing());

    }

    /**
    * Test we can disable namespacing only.
    *
    */
    public function testDisableNamespacing()
    {
        $this->manager->enable(true);

        $this->assertTrue($this->loaderRegistered());
        $this->assertTrue($this->loaderLast());
        $this->assertTrue($this->usingNamespacing());

        $this->manager->disable(true);
        $this->assertTrue($this->loaderRegistered());
        $this->assertTrue($this->loaderLast());
        $this->assertFalse($this->usingNamespacing());
    }

    /**
    * Tests that disable adds any default __autoload function if the stack is empty.
    *
    */
    public function testDisableAddsAutoloadIfEmpty()
    {
        Utils::requireAutoload();

        // Clear autoload stack
        $funcs = spl_autoload_functions();
        foreach ($funcs as $loader) {
            spl_autoload_unregister($loader);
        }

        $this->manager->enable(true);
        $beforeCount = count(spl_autoload_functions());

        $this->manager->disable();
        $new = spl_autoload_functions();
        $newCount = count($new);

        // Replace autoload stack
        foreach ($funcs as $loader) {
            spl_autoload_register($loader);
        }

        $this->assertEquals(1, $beforeCount);
        $this->assertEquals(1, $newCount);
        $this->assertEquals($new[0], '__autoload');
    }

    protected function usingNamespacing()
    {
        return $this->manager->useNamespacing;
    }

    protected function loaderRegistered()
    {
        $this->getAutoloadState($index, $last);
        return false !== $index;
    }

    protected function loaderLast()
    {
        $this->getAutoloadState($index, $last);
        return $index === $last;
    }

    protected function getAutoloadState(&$index, &$last)
    {
        $loader = array($this->manager, 'loader');
        $index = false;
        $last = 0;

        if ($funcs = spl_autoload_functions()) {
            $index = array_search($loader, $funcs, true);
            $last = count($funcs) - 1;
        }
    }
}
