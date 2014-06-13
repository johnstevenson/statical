<?php
namespace Statical\Tests;

use Statical\Tests\Fixtures\NamespaceManager;

class NamespaceManagerTest extends \PHPUnit_Framework_TestCase
{
    protected $manager;

    public function setUp()
    {
        $this->manager = new NamespaceManager();
    }

    /**
     * Test addNamespace throw an exception with an empty namespace
     *
     * @expectedException InvalidArgumentException
     */
    public function testAddEmptyNamespace()
    {
        $this->manager->add('Foo', '');
    }

    /**
     * Test addNamespace throws an exception with leading backslash namespace.
     *
     * @expectedException InvalidArgumentException
     */
    public function testAddInvalidNamespace1()
    {
        $this->manager->add('Foo', '\\Bar\\Baz');
    }

    /**
     * Test addNamespace throws an exception with trailing backslash namespace.
     *
     * @expectedException InvalidArgumentException
     */
    public function testAddInvalidNamespace2()
    {
        $this->manager->add('Foo', 'Bar\\Baz\\');
    }

    /**
     * Test addNamespace throws an exception with leading backslash namespace.
     *
     * @expectedException InvalidArgumentException
     */
    public function testAddInvalidAlias()
    {
        $this->manager->add('Foo\\Bar', 'Bar\\Baz');
    }

    /**
    * Test addNamespace adds an item
    *
    */
    public function testAddItem()
    {
        $this->manager->add('Foo', 'Bar\\Baz');
        $this->assertCount(1, $this->manager->namespaces);
    }

    /**
    * Test addNamespace adds an item to root
    *
    */
    public function testAddRoot()
    {
        $this->manager->add('Foo', 'Bar\\Baz');
        $this->assertContains('Bar\\Baz', $this->manager->namespaces['Foo']['root']);
    }

    /**
    * Test addNamespace adds trimmed namespace with trailing * to base
    *
    */
    public function testAddBase()
    {
        $this->manager->add('Foo', 'Bar\\Baz\\*');
        $this->assertContains('Bar\\Baz\\', $this->manager->namespaces['Foo']['base']);
    }

    /**
    * Test addNamespace sets * namespace to any
    *
    */
    public function testAddAny()
    {
        // Check we set * namespace to any
        $this->manager->add('Foo', '*');
        $this->assertTrue($this->manager->namespaces['Foo']['any']);
    }

    /**
    * Test addNamespace accepts multiple items
    *
    */
    public function testAddMultiple()
    {
        $this->manager->add('Foo', array('Bar\\Baz\\*', 'Bar\\Barry\\*'));
        $this->assertContains('Bar\\Baz\\', $this->manager->namespaces['Foo']['base']);
        $this->assertContains('Bar\\Barry\\', $this->manager->namespaces['Foo']['base']);
        $this->assertCount(2, $this->manager->namespaces['Foo']['base']);
    }

    /**
    * Test addNamespace overwrites duplicate items
    *
    */
    public function testAddUnique()
    {
        $this->manager->add('Foo', 'Bar\\Baz\\*');
        $this->assertContains('Bar\\Baz\\', $this->manager->namespaces['Foo']['base']);

        $this->manager->add('Foo', 'Bar\\Baz\\*');
        $this->assertContains('Bar\\Baz\\', $this->manager->namespaces['Foo']['base']);
        $this->assertCount(1, $this->manager->namespaces['Foo']['base']);
    }

    /**
    * Test getNamespace returns correct groups
    *
    */
    public function testGetNamespace()
    {
        $alias = 'Foo';
        $namespace = 'Bar\\Baz';
        $group = $this->manager->getDefaultGroups();
        $group['root'][] = $namespace;

        $this->manager->add($alias, $namespace);
        $this->assertSame($group, $this->manager->getNamespace($alias, true));
    }

    /**
    * Test getNamespace returns empty array on non-match
    *
    */
    public function testGetNamespaceEmpty()
    {
        $this->manager->add('Foo', 'Bar\\Baz');
        $this->assertEmpty($this->manager->getNamespace('Bar'));
    }

    /**
    * Test getNamespace returns default groups on non-match
    *
    */
    public function testGetNamespaceEmptyWithDefault()
    {
        $this->manager->add('Foo', 'Bar\\Baz');
        $this->assertSame($this->manager->getDefaultGroups(),
            $this->manager->getNamespace('Bar', true));
    }

    /**
    * Test getNamespaceGroup with different values
    *
    */
    public function testGetNamespaceGroup()
    {
        // Check * returns any
        $value = '*';
        $this->assertEquals('any', $this->manager->getNamespaceGroup($value));

        // Check namespace returns root
        $value = 'Foo\\Bar';
        $this->assertEquals('root', $this->manager->getNamespaceGroup($value));

        // Check namespace with trailing \* returns base
        $value = 'Foo\\Bar\\*';
        $this->assertEquals('base', $this->manager->getNamespaceGroup($value));
    }

    /**
    * Test we can match a root namespace
    *
    */
    public function testMatchRoot()
    {
        $alias = 'Foo';
        $this->manager->add($alias, 'Bar');

        $this->assertTrue($this->manager->match($alias, 'Bar\\Foo'));
    }

    /**
    * Test we can match a base namespace
    *
    */
    public function testMatchBase()
    {
        $alias = 'Foo';
        $this->manager->add($alias, 'Bar\\*');

        $this->assertTrue($this->manager->match($alias, 'Bar\\Baz\\Foo'));
    }

    /**
    * Test we can match a * namespace
    *
    */
    public function testMatchAny()
    {
        $alias = 'Foo';
        $this->manager->add($alias, '*');

        $this->assertTrue($this->manager->match($alias, 'Bar\\Baz\\Foo'));
    }

    /**
    * Test we can match a global alias with root namespace
    *
    */
    public function testMatchGlobalAlias()
    {
        $this->manager->add('*', 'Bar\\Baz');

        $this->assertTrue($this->manager->match('Foo', 'Bar\\Baz\\Foo'));
    }

    /**
    * Test we fail to match  an alias
    *
    */
    public function testMatchAliasFails()
    {
        $this->manager->add('Foo', 'Bar\\Baz');
        $this->assertFalse($this->manager->match('Foo', 'Bar\\Foo'));
    }
}
