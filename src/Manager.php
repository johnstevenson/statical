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
    * The container instance
    *
    * @var callable
    */
    protected $container = array();

    /**
    * Whether the class is to be treated as a singleton.
    *
    * @var bool
    */
    public static $singleton = false;

    const STATICAL_PROXY = 'Statical\StaticalProxy';

    /**
    * Constructor - will throw an exception if we have been set as a singleton.
    *
    * @param mixed $container
    * @param array $config
    * @throws RuntimeException
    */
    public function __construct($container = null, array $config = array())
    {
        if (static::$singleton) {
            throw new \RuntimeException(__CLASS__ . ' has been set as a singleton.');
        }

        BaseProxy::setResolver($this);
        $this->aliasManager = new AliasManager();

        if ($config) {
            $this->configure($config, $container);
        } elseif ($container) {
            $this->setContainer($container);
        }
    }

    /**
    * Registers ourself as a proxy, aliased as Statical and available in any namespace.
    *
    * @return void
    */
    public function addProxySelf()
    {
        $this->addProxyInstance('Statical', static::STATICAL_PROXY, $this);
        $this->addNamespace('Statical', '*');
        $this->enable();
    }

    /**
    * Adds a service as a proxy target
    *
    * @param string $alias
    * @param string $proxy
    * @param string $id
    * @param callable|null $container
    * @param array $namespace
    */
    public function addProxyService($alias, $proxy, $id, $container = null, $namespace = array())
    {
        $proxy = Input::checkNamespace($proxy);
        $id = Input::check($id);
        $container = Input::checkContainerEx($container, $this->container);

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
    * @param object $target
    * @param array $namespace
    */
    public function addProxyInstance($alias, $proxy, $target, $namespace = array())
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
    * Applies a set of proxy settings
    *
    * @param array $config
    * @param mixed $container
    * @return void
    */
    public function configure(array $config, $container = null)
    {
        if ($container) {
            $this->setContainer($container);
        }

        $handler = new ConfigHandler($this);
        $handler->apply($config, $this->container);
    }

    /**
    * Sets the default container for proxy services and returns the old one.
    *
    * This container is used if calls to addProxyService do not pass in a
    * container.
    *
    * @param mixed $container
    * @throws RuntimeException
    * @return callable|null
    */
    public function setContainer($container)
    {
        $result = $this->container;
        $this->container = Input::checkContainer($container);

        return $result;
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
