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

 class Manager
 {
    /**
    * The static classes to proxy.
    *
    * @var array
    */
    protected $registry = array();

    /**
    * The alias manager
    *
    * @var \Statical\AliasManager
    */
    protected $aliasManager;

    /**
    * Whether the class is to be treated as a singleton.
    *
    * @var bool
    */
    public static $singleton = false;

    /**
    * Constructor - will throw an exception if we have been set as a singleton.
    *
    * @throws RuntimeException
    */
    public function __construct()
    {
        if (static::$singleton) {
            throw new \RuntimeException(__CLASS__ . ' has been set as a singleton.');
        }

        BaseProxy::setResolver($this);
        $this->aliasManager = new AliasManager();
    }

    /**
    * Registers ourself as a proxy and enables the service.
    *
    * The Manager is aliased as Statical and available in any namespace.
    *
    * @return void
    */
    public function addProxySelf()
    {
        $this->addProxyInstance('Statical', 'Statical\\StaticalProxy', $this);
        $this->addNamespace('Statical', '*');
        $this->enable();
    }

    /**
    * Adds a service as a proxy target
    *
    * @param string $alias
    * @param string $proxy
    * @param callable $container
    * @param string|null $id
    * @param string|array|null $namespace
    */
    public function addProxyService($alias, $proxy, $container, $id = null, $namespace = null)
    {
        $proxy = Input::checkNamespace($proxy);
        $container = Input::checkContainer($container);
        $id = $id ?: strtolower($alias);

        $this->addProxy($proxy, $id, $container);
        $this->aliasManager->add($proxy, $alias);

        if ($namespace) {
            $this->addNamespace($alias, $namespace);
        }
    }

    /**
    * Adds an instance or closure as a proxy target
    *
    * @param string $alias
    * @param string $proxy
    * @param object|closure $target
    * @param string|array|null $namespace
    */
    public function addProxyInstance($alias, $proxy, $target, $namespace = null)
    {
        $proxy = Input::checkNamespace($proxy);

        if (!is_object($target)) {
            throw new \InvalidArgumentException('Target must be an instance or closure.');
        }

        $this->addProxy($proxy, null, $target);
        $this->aliasManager->add($proxy, $alias);

        if ($namespace) {
            $this->addNamespace($alias, $namespace);
        }
    }

    /**
    * Adds a namespace
    *
    * @param string $alias
    * @param string|array $namespace
    */
    public function addNamespace($alias, $namespace)
    {
        $this->aliasManager->addNamespace($alias, $namespace);
    }

    /**
    * Enables static proxying by registering the autoloader.
    *
    * Ensures that the autoloader is always at the end of the stack.
    *
    * @return void
    */
    public function enable()
    {
        $this->aliasManager->enable();
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
        $this->aliasManager->disable($onlyNamespacing);
    }

    /**
    * Enforces a singeton pattern on the class.
    *
    * Subsequent attempts to instantiate this class will throw an exception.
    *
    * @return void
    */
    public function makeSingleton()
    {
        static::$singleton = true;
    }

    /**
    * Returns the target instance of the static proxy.
    *
    * @param string $class
    * @throws RuntimeException
    * @return mixed
    */
    public function getProxyTarget($class)
    {
        if (!isset($this->registry[$class])) {
            throw new \RuntimeException($class.' not registered as a static proxy.');
        }

        if ($id = $this->registry[$class]['id']) {
            return call_user_func_array($this->registry[$class]['target'], array($id));
        } else {

            if ($closure = $this->registry[$class]['closure']) {
                $this->registry[$class]['target'] = $closure();
                $this->registry[$class]['closure'] = null;
            }

            return $this->registry[$class]['target'];
        }
    }

    /**
    * Adds a proxy to the registry array
    *
    * Assumes that the inputs are correct.
    *
    * @param string $proxy
    * @param string $id
    * @param callable|null $target
    * @return void
    */
    protected function addProxy($proxy, $id, $target)
    {
        $callee = $target instanceof \Closure ? null : $target;
        $closure = $callee ? null : $target;

        $this->registry[$proxy] = array(
            'id' => $id,
            'target' => $callee,
            'closure' => $closure
        );
    }
 }
