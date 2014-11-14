<?php
namespace Statical\Tests;

use Statical\Tests\Fixtures\NamespaceManager;
use Statical\Input;

class NamespaceManagerTest extends \PHPUnit_Framework_TestCase
{
    protected $namespaceManager;

    public function setUp()
    {
        $this->namespaceManager = new NamespaceManager();
    }

    /**
     * Test addNamespace throw an exception with an empty namespace
     *
     * @expectedException InvalidArgumentException
     */
    public function testAddEmptyNamespace()
    {
        $namespace = Input::formatNamespace('');
        $this->namespaceManager->add('Foo', $namespace);
    }

    /**
     * Test addNamespace throws an exception with leading backslash namespace.
     *
     * @expectedException InvalidArgumentException
     */
    public function testAddInvalidNamespace1()
    {
        $namespace = Input::formatNamespace('\\Bar\\Baz');
        $this->namespaceManager->add('Foo', $namespace);
    }

    /**
     * Test addNamespace throws an exception with trailing backslash namespace.
     *
     * @expectedException InvalidArgumentException
     */
    public function testAddInvalidNamespace2()
    {
        $namespace = Input::formatNamespace('Bar\\Baz\\');
        $this->namespaceManager->add('Foo', $namespace);
    }

    /**
     * Test addNamespace throws an exception with a namespaced alias.
     *
     * @expectedException InvalidArgumentException
     */
    public function testAddInvalidAlias()
    {
        $namespace = Input::formatNamespace('Bar\\Baz');
        $this->namespaceManager->add('Foo\\Bar', $namespace);
    }

    /**
    * Test addNamespace adds an item
    *
    */
    public function testAddItem()
    {
        $namespace = Input::formatNamespace('Bar\\Baz');
        $this->namespaceManager->add('Foo', $namespace);
        $this->assertCount(1, $this->namespaceManager->namespaces);
    }

    /**
    * Test addNamespace adds an item to root
    *
    */
    public function testAddRoot()
    {
        $namespace = Input::formatNamespace('Bar\\Baz');
        $this->namespaceManager->add('Foo', $namespace);
        $this->assertContains('Bar\\Baz', $this->namespaceManager->namespaces['Foo']['root']);
    }

    /**
    * Test addNamespace adds trimmed namespace with trailing * to base
    *
    */
    public function testAddBase()
    {
        $namespace = Input::formatNamespace('Bar\\Baz\\*');
        $this->namespaceManager->add('Foo', $namespace);
        $this->assertContains('Bar\\Baz\\', $this->namespaceManager->namespaces['Foo']['base']);
    }

    /**
    * Test addNamespace sets * namespace to any
    *
    */
    public function testAddAny()
    {
        // Check we set * namespace to any
        $namespace = Input::formatNamespace('*');
        $this->namespaceManager->add('Foo', $namespace);
        $this->assertTrue($this->namespaceManager->namespaces['Foo']['any']);
    }

    /**
    * Test addNamespace accepts multiple items
    *
    */
    public function testAddMultiple()
    {
        $this->namespaceManager->add('Foo', array('Bar\\Baz\\*', 'Bar\\Barry\\*'));
        $this->assertContains('Bar\\Baz\\', $this->namespaceManager->namespaces['Foo']['base']);
        $this->assertContains('Bar\\Barry\\', $this->namespaceManager->namespaces['Foo']['base']);
        $this->assertCount(2, $this->namespaceManager->namespaces['Foo']['base']);
    }

    /**
    * Test addNamespace overwrites duplicate items
    *
    */
    public function testAddUnique()
    {
        $namespace = Input::formatNamespace('Bar\\Baz\\*');
        $this->namespaceManager->add('Foo', $namespace);
        $this->assertContains('Bar\\Baz\\', $this->namespaceManager->namespaces['Foo']['base']);

        $this->namespaceManager->add('Foo', $namespace);
        $this->assertContains('Bar\\Baz\\', $this->namespaceManager->namespaces['Foo']['base']);
        $this->assertCount(1, $this->namespaceManager->namespaces['Foo']['base']);
    }

    /**
    * Test getNamespace returns correct groups
    *
    */
    public function testGetNamespace()
    {
        $alias = 'Foo';
        $namespaceString = 'Bar\\Baz';
        $group = $this->namespaceManager->getDefaultGroups();
        $group['root'][] = $namespaceString;

        $namespace = Input::formatNamespace($namespaceString);
        $this->namespaceManager->add($alias, $namespace);
        $this->assertSame($group, $this->namespaceManager->getNamespace($alias, true));
    }

    /**
    * Test getNamespace returns empty array on non-match
    *
    */
    public function testGetNamespaceEmpty()
    {
        $namespace = Input::formatNamespace('Bar\\Baz');
        $this->namespaceManager->add('Foo', $namespace);
        $this->assertEmpty($this->namespaceManager->getNamespace('Bar'));
    }

    /**
    * Test getNamespace returns default groups on non-match
    *
    */
    public function testGetNamespaceEmptyWithDefault()
    {
        $namespace = Input::formatNamespace('Bar\\Baz');
        $this->namespaceManager->add('Foo', $namespace);
        $this->assertSame($this->namespaceManager->getDefaultGroups(),
            $this->namespaceManager->getNamespace('Bar', true));
    }

    /**
    * Test getNamespaceGroup with different values
    *
    */
    public function testGetNamespaceGroup()
    {
        // Check * returns any
        $value = '*';
        $this->assertEquals('any', $this->namespaceManager->getNamespaceGroup($value));

        // Check namespace returns root
        $value = 'Foo\\Bar';
        $this->assertEquals('root', $this->namespaceManager->getNamespaceGroup($value));

        // Check namespace with trailing \* returns base
        $value = 'Foo\\Bar\\*';
        $this->assertEquals('base', $this->namespaceManager->getNamespaceGroup($value));
    }

    /**
    * Test we can match a root namespace
    *
    */
    public function testMatchRoot()
    {
        $alias = 'Foo';
        $namespace = Input::formatNamespace('Bar');
        $this->namespaceManager->add($alias, $namespace);

        $this->assertTrue($this->namespaceManager->match($alias, 'Bar\\Foo'));
    }

    /**
    * Test we can match a base namespace
    *
    */
    public function testMatchBase()
    {
        $alias = 'Foo';
        $namespace = Input::formatNamespace('Bar\\*');
        $this->namespaceManager->add($alias, $namespace);

        $this->assertTrue($this->namespaceManager->match($alias, 'Bar\\Baz\\Foo'));
    }

    /**
    * Test we can match a * namespace
    *
    */
    public function testMatchAny()
    {
        $alias = 'Foo';
        $namespace = Input::formatNamespace('*');
        $this->namespaceManager->add($alias, $namespace);

        $this->assertTrue($this->namespaceManager->match($alias, 'Bar\\Baz\\Foo'));
    }

    /**
    * Test we can match a global alias with root namespace
    *
    */
    public function testMatchGlobalAlias()
    {
        $namespace = Input::formatNamespace('Bar\\Baz');
        $this->namespaceManager->add('*', $namespace);

        $this->assertTrue($this->namespaceManager->match('Foo', 'Bar\\Baz\\Foo'));
    }

    /**
    * Test we fail to match  an alias
    *
    */
    public function testMatchAliasFails()
    {
        $namespace = Input::formatNamespace('Bar\\Baz');
        $this->namespaceManager->add('Foo', $namespace);
        $this->assertFalse($this->namespaceManager->match('Foo', 'Bar\\Foo'));
    }
}
