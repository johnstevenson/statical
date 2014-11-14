<?php

/*
 * This file is part of the Statical package.
 *
 * (c) John Stevenson <john-stevenson@blueyonder.co.uk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

 namespace Statical;

 use Statical\Input;

 class NamespaceManager
 {
    /**
    * Namespaces to modify lazy loading.
    *
    * @var array
    */
    protected $namespaces = array();

    /**
    * Adds a namespace
    *
    * @param string $alias
    * @param mixed $namespace Either a string or array of namespaces
    */
    public function add($alias, $namespace)
    {
        $alias = Input::checkAlias($alias);
        $namespace = (array) $namespace;
        $props = $this->getNamespace($alias, true);

        foreach ($namespace as $ns) {
            $group = $this->getNamespaceGroup($ns);

            if ('any' === $group) {
                $props['any'] = true;
            } else {
                // trim trailing * from base pattern
                $ns = 'base' === $group ? rtrim($ns, '*') : $ns;
                $props[$group][] = $ns;
            }
        }

        $this->setNamespace($alias, $props);
    }

    /**
    * Returns true if a matching namespace is found.
    *
    * @param string $alias
    * @param string $class
    * @return bool
    */
    public function match($alias, $class)
    {
        $result = false;

        foreach (array('*', $alias) as $key) {
            if ($props = $this->getNamespace($key)) {
                $result = $this->matchGroup($props, $alias, $class);
            }

            if ($result) {
                break;
            }
        }

        return $result;
    }

    /**
    * Returns true if a namespace entry is matched.
    *
    * @param array $props
    * @param string $alias
    * @param string $class
    * @return bool
    */
    protected function matchGroup($props, $alias, $class)
    {
        if ($props['any']) {
            return true;
        }

        foreach (array('base', 'root') as $group) {
            if ($this->matchClass($props[$group], $group, $alias, $class)) {
                return true;
            }
        }

        return false;
    }

    /**
    * Returns true if a class matches a namespace item
    *
    * @param array $array
    * @param string $group
    * @param string $alias
    * @param string $class
    */
    protected function matchClass($array, $group, $alias, $class)
    {
        $match = false;

        foreach ($array as $test) {

            if ('base' === $group) {
                $match = 0 === strpos($class, $test);
            } else {
                $match = $test.'\\'.$alias === $class;
            }

            if ($match) {
                break;
            }
        }

        return $match;
    }

    /**
    * Returns the namespace groups for an alias.
    *
    * Returns either an empty array if the alias is not found and $default is
    * false, or an array containing all namespace groups with any found values.
    *
    * @param string $alias
    * @param bool $default
    * @return array
    */
    protected function getNamespace($alias, $default = false)
    {
        $result = isset($this->namespaces[$alias]) ? $this->namespaces[$alias] : array();

        if ($result || $default) {
            $result = array_merge($this->getDefaultGroups(), $result);
        }

        return $result;
    }

    /**
    * Adds a namespace array group to the namespaces array.
    *
    * If the group is an array, duplicate entries are removed. Empty groups
    * are removed from the final array entry.
    *
    * @param string $alias
    * @param array $props
    * @return void
    */
    protected function setNamespace($alias, $props)
    {
        array_walk($props, function (&$value) {
            if (is_array($value)) {
                $value = array_unique($value);
            }
        });

        $this->namespaces[$alias] = array_filter($props);
    }

    /**
    * Returns the group name for the namespace input type.
    *
    * @param string $namespace
    * @return string
    */
    protected function getNamespaceGroup($namespace)
    {
        $namespace = Input::checkNamespace($namespace);

        if ('*' === substr($namespace, -1)) {
            if ('*' === $namespace) {
                $group = 'any';
            } else {
                $group = 'base';
            }
        } else {
            $group = 'root';
        }

        return $group;
    }

    /**
    * Returns an array of default groups.
    *
    * @return array
    */
    protected function getDefaultGroups()
    {
        return array(
            'any' => false,
            'base' => array(),
            'root' => array()
        );
    }
 }
