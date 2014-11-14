<?php
namespace Statical\Tests;

use Statical\Tests\Fixtures\AliasManager;
use Statical\Tests\Fixtures\Utils;

/**
* These tests do not need a separate process because although they do stuff
* with the autoload stack they do not actually make calls to load classes.
*/
class AliasManagerTest extends \PHPUnit_Framework_TestCase
{
    protected $aliasManager;

    public function setUp()
    {
        $this->aliasManager = new AliasManager(true);
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

        $this->aliasManager->add($original, $alias);

        // key => $alias, value = $original
        $this->assertEquals($this->aliasManager->aliases[$alias], $original);
    }

    /**
    * Test that getNamespaceAlias matches root
    *
    */
    public function testGetNamespaceAlias()
    {
        $original = 'Statical\\Tests\\Fixtures\\Foo';
        $alias = 'Foo';
        $this->aliasManager->add($original, $alias);
        $this->aliasManager->addNamespace($alias, 'Bar\\Baz');

        $this->assertEquals($alias,
            $this->aliasManager->getNamespaceAlias('Bar\\Baz\\Foo'));
    }

    /**
    * Test that getNamespaceAlias returns null when it doesn't match
    *
    */
    public function testGetNamespaceAliasReturnsNull()
    {
        $original = 'Statical\\Tests\\Fixtures\\Foo';
        $alias = 'Foo';
        $this->aliasManager->add($original, $alias);
        $this->aliasManager->addNamespace($alias, 'Bar\\Baz');

        $this->assertEquals(null,
            $this->aliasManager->getNamespaceAlias('Bar\\Foo'));
    }

    /**
    * Test we can enable the autoloader.
    *
    */
    public function testEnable()
    {
        $this->aliasManager->enable();

        $this->assertTrue($this->loaderRegistered());
        $this->assertTrue($this->loaderLast());
    }

    /**
    * Test that updating enable re-registers the autoloader on the end.
    *
    * We can check this by registering a new autoloader then checking that the
    * position of ours has changed.
    */
    public function testEnableUpdate()
    {
        $this->aliasManager->enable();

        $this->assertTrue($this->loaderRegistered());
        $this->assertTrue($this->loaderLast());

        // register a new loader - it will be at the end of the stack
        Utils::registerAppLoader();

        // test that the loader is still registered and is no longer at the end
        $this->assertTrue($this->loaderRegistered());
        $this->assertFalse($this->loaderLast());

        $this->aliasManager->enable();

        // test that the loader has been re-registered on the end
        $this->assertTrue($this->loaderRegistered());
        $this->assertTrue($this->loaderLast());

        // unregister new loader
        Utils::unregisterAppLoader();
    }

    /**
    * Test we can disable the autoloader.
    *
    */
    public function testDisable()
    {
        $this->aliasManager->enable();

        $this->assertTrue($this->loaderRegistered());
        $this->assertTrue($this->loaderLast());

        $this->aliasManager->disable();
        $this->assertFalse($this->loaderRegistered());
    }

    /**
    * Tests that disable adds any default __autoload function if the stack is empty.
    *
    */
    public function testDisableAddsAutoloadIfEmpty()
    {
        // load a dummy __autoload
        Utils::requireAutoload();

        // Clear autoload stack and store in oldStack
        $oldStack = spl_autoload_functions();
        foreach ($oldStack as $loader) {
            spl_autoload_unregister($loader);
        }

        // add our autoloader
        $this->aliasManager->enable();
        $this->assertEquals(1, count(spl_autoload_functions()));

        // remove our autoloader
        $this->aliasManager->disable();

        // check we only have __autoload of the stack
        $newStack = spl_autoload_functions();
        $this->assertEquals(1, count($newStack));
        $this->assertEquals($newStack[0], '__autoload');

        // Replace original autoload stack
        foreach ($oldStack as $loader) {
            spl_autoload_register($loader);
        }
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
        $loader = array($this->aliasManager, 'loader');
        $index = false;
        $last = 0;

        if ($funcs = spl_autoload_functions()) {
            $index = array_search($loader, $funcs, true);
            $last = count($funcs) - 1;
        }
    }
}
