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

 class AliasManager
 {
    /**
    * Class aliases for lazy loading.
    *
    * @var array
    */
    protected $aliases = array();

    /**
    * The namespace manager
    *
    * @var NamespaceManager
    */
    protected $namespacer;

    /**
    * Whether to use namespacing
    *
    * @var bool
    */
    protected $useNamespacing = false;

    /**
    * The autoloader callable
    *
    * @var callable
    */
    protected $aliasLoader;

    public function __construct()
    {
        $this->namespacer = new NamespaceManager();
        $this->aliasLoader = array($this, 'loader');
        $this->disable();
    }

    /**
    * Adds a class alias to the aliases array
    *
    * Assumes that the inputs are correct.
    *
    * @param string $original
    * @param string $alias
    * @return void
    */
    public function add($original, $alias)
    {
        $alias = Input::checkAlias($alias);
        $this->aliases[$alias] = $original;
    }

    /**
    * Adds a namespace
    *
    * @param string $alias
    * @param string|array $namespace
    */
    public function addNamespace($alias, $namespace)
    {
        $this->namespacer->add($alias, $namespace);
    }

    /**
    * Enables static proxying by registering the autoloader.
    *
    * If the autoloader has not been registered it will be added to the end of
    * the stack and the internal useNamespacing flag will be appropriately set.
    *
    * If the autoloader has already been registered, behaviour depends on the
    * value of the $useNamespacing param.
    *
    * False: No-op. The autoloader will remain wherever it is on the stack and
    * the internal useNamespacing flag will not be modified.
    *
    * True: The autoloader will always be added or moved to the end of the stack
    * and the internal useNamespacing flag will set to true.
    *
    * @param boolean $useNamespacing
    * @return void
    */
    public function enable($useNamespacing = false)
    {
        if ($this->isLoaderRegistered($last)) {
            if (!$useNamespacing || $last) {
                return;
            }

            $this->disable(false);
        }

        spl_autoload_register($this->aliasLoader);
        $this->useNamespacing = $useNamespacing;
    }

    /**
    * Disables static proxying or the namespacing feature.
    *
    * If $onlyNamespacing is true, the autoloader is not unregistered.
    *
    * @param bool $onlyNamespacing
    * @return void
    */
    public function disable($onlyNamespacing = false)
    {
        if (!$onlyNamespacing) {
            spl_autoload_unregister($this->aliasLoader);
            $this->checkAutoloadStack();
        }

        $this->useNamespacing = false;
    }

    /**
    * Registered class loader to manage lazy class aliasing.
    *
    * @param string $class
    */
    public function loader($class)
    {
        if (isset($this->aliases[$class])) {
            class_alias($this->aliases[$class], $class);
            return;
        }

        if ($this->useNamespacing) {
            if ($alias = $this->getNamespaceAlias($class)) {
                class_alias($this->aliases[$alias], $class);
            }
        }
    }

    /**
    * Returns the class alias if matched by namespacing.
    *
    * @param string $class
    * @return string|null
    */
    protected function getNamespaceAlias($class)
    {
        $alias = basename(str_replace('\\', '/', $class));

        if (isset($this->aliases[$alias])) {
            if ($this->namespacer->match($alias, $class)) {
                return $alias;
            }
        }
    }

    /**
    * Reports whether the alias loader is registered and at the end of the stack.
    *
    * @param bool $last
    * @return bool
    */
    protected function isLoaderRegistered(&$last)
    {
        $result = false;
        $last = false;

        if ($funcs = spl_autoload_functions()) {
            $index = array_search($this->aliasLoader, $funcs, true);

            if (false !== $index) {
                $result = true;
                $last = $index === count($funcs) - 1;
            }
        }

        return $result;
    }

    /**
    * Checks if we have emptied the stack.
    *
    * Re-registers __autoload function if it exists.
    *
    * @return void
    */
    protected function checkAutoloadStack()
    {
        if (!spl_autoload_functions() && function_exists('__autoload')) {
            spl_autoload_register('__autoload');
        }
    }
 }
