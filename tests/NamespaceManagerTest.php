<?php
namespace Statical\Tests;

use Statical\Tests\Fixtures\NamespaceManager;

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
        $this->namespaceManager->add('Foo', '');
    }

    /**
     * Test addNamespace throws an exception with leading backslash namespace.
     *
     * @expectedException InvalidArgumentException
     */
    public function testAddInvalidNamespace1()
    {
        $this->namespaceManager->add('Foo', '\\Bar\\Baz');
    }

    /**
     * Test addNamespace throws an exception with trailing backslash namespace.
     *
     * @expectedException InvalidArgumentException
     */
    public function testAddInvalidNamespace2()
    {
        $this->namespaceManager->add('Foo', 'Bar\\Baz\\');
    }

    /**
     * Test addNamespace throws an exception with a namespaced alias.
     *
     * @expectedException InvalidArgumentException
     */
    public function testAddInvalidAlias()
    {
        $this->namespaceManager->add('Foo\\Bar', 'Bar\\Baz');
    }

    /**
    * Test addNamespace adds an item
    *
    */
    public function testAddItem()
    {
        $this->namespaceManager->add('Foo', 'Bar\\Baz');
        $this->assertCount(1, $this->namespaceManager->namespaces);
    }

    /**
    * Test addNamespace adds an item to root
    *
    */
    public function testAddRoot()
    {
        $this->namespaceManager->add('Foo', 'Bar\\Baz');
        $this->assertContains('Bar\\Baz', $this->namespaceManager->namespaces['Foo']['root']);
    }

    /**
    * Test addNamespace adds trimmed namespace with trailing * to base
    *
    */
    public function testAddBase()
    {
        $this->namespaceManager->add('Foo', 'Bar\\Baz\\*');
        $this->assertContains('Bar\\Baz\\', $this->namespaceManager->namespaces['Foo']['base']);
    }

    /**
    * Test addNamespace sets * namespace to any
    *
    */
    public function testAddAny()
    {
        // Check we set * namespace to any
        $this->namespaceManager->add('Foo', '*');
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
        $this->namespaceManager->add('Foo', 'Bar\\Baz\\*');
        $this->assertContains('Bar\\Baz\\', $this->namespaceManager->namespaces['Foo']['base']);

        $this->namespaceManager->add('Foo', 'Bar\\Baz\\*');
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
        $namespace = 'Bar\\Baz';
        $group = $this->namespaceManager->getDefaultGroups();
        $group['root'][] = $namespace;

        $this->namespaceManager->add($alias, $namespace);
        $this->assertSame($group, $this->namespaceManager->getNamespace($alias, true));
    }

    /**
    * Test getNamespace returns empty array on non-match
    *
    */
    public function testGetNamespaceEmpty()
    {
        $this->namespaceManager->add('Foo', 'Bar\\Baz');
        $this->assertEmpty($this->namespaceManager->getNamespace('Bar'));
    }

    /**
    * Test getNamespace returns default groups on non-match
    *
    */
    public function testGetNamespaceEmptyWithDefault()
    {
        $this->namespaceManager->add('Foo', 'Bar\\Baz');
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
        $this->namespaceManager->add($alias, 'Bar');

        $this->assertTrue($this->namespaceManager->match($alias, 'Bar\\Foo'));
    }

    /**
    * Test we can match a base namespace
    *
    */
    public function testMatchBase()
    {
        $alias = 'Foo';
        $this->namespaceManager->add($alias, 'Bar\\*');

        $this->assertTrue($this->namespaceManager->match($alias, 'Bar\\Baz\\Foo'));
    }

    /**
    * Test we can match a * namespace
    *
    */
    public function testMatchAny()
    {
        $alias = 'Foo';
        $this->namespaceManager->add($alias, '*');

        $this->assertTrue($this->namespaceManager->match($alias, 'Bar\\Baz\\Foo'));
    }

    /**
    * Test we can match a global alias with root namespace
    *
    */
    public function testMatchGlobalAlias()
    {
        $this->namespaceManager->add('*', 'Bar\\Baz');

        $this->assertTrue($this->namespaceManager->match('Foo', 'Bar\\Baz\\Foo'));
    }

    /**
    * Test we fail to match  an alias
    *
    */
    public function testMatchAliasFails()
    {
        $this->namespaceManager->add('Foo', 'Bar\\Baz');
        $this->assertFalse($this->namespaceManager->match('Foo', 'Bar\\Foo'));
    }
}
