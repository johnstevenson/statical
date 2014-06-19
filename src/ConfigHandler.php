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

 class ConfigHandler
 {
    /**
    * The statical Manager
    *
    * @var Manager
    */
    protected $manager;

    /**
    * The formatted config
    *
    * @var array
    */
    protected $config = array();

    /**
    * The container instance
    *
    * @var object
    */
    protected $instance;

    const INSTANCES = 'instances';
    const SERVICES = 'services';
    const NAMESPACES = 'namespaces';
    const BOOT = 'boot';

    /**
    * @param Manager $manager
    */
    public function __construct(Manager $manager)
    {
        $this->manager = $manager;
    }

    /**
    * Applies a set of proxy settings and actions
    *
    * @param array $config
    * @param callable $container
    * @return void
    */
    public function apply(array $config, $container)
    {
        $this->checkInput($config, $container);

        $proxies = array(self::INSTANCES, self::SERVICES);
        foreach ($proxies as $key) {
            $this->addProxies($key);
        }

        $this->addNamespaces();
        $this->bootManager();
    }

    /**
    * Checks config values and the container if required.
    *
    * Sets $this->config with a set of all config values and $this->instance
    * if it is required.
    *
    * @param array $config
    * @param callable $container
    * @throws InvalidArgumentException
    * @return void
    */
    protected function checkInput(array $config, $container)
    {
        $this->config = $this->getDefault();

        foreach($this->config as $key => $default) {
            $this->config[$key] = $this->getItem($config, $key, $default);
        }

        if ($this->requiresContainer()) {
            $container = Input::checkContainer($container);
            $this->instance = $container[0];
        }
    }

    /**
    * Extracts a config value, checks its type and formats it.
    *
    * @param array $config
    * @param string $key
    * @param mixed $default
    * @return mixed
    */
    protected function getItem($config, $key, $default)
    {
        $result = Input::checkConfig($config, $key, $default);

        if (is_array($result)) {
            array_walk($result, function (&$value) {
                $value = array_map('trim', array_pad((array) $value, 2, ''));
            });
        }

        return $result;
    }

    /**
    * Adds instances or services from the config array.
    *
    * @param string $key
    * @return void
    */
    protected function addProxies($key)
    {
        foreach ($this->config[$key] as $alias => $array) {

            if (self::INSTANCES === $key) {
                $this->manager->addProxyInstance($alias, $array[0], $this->instance);
            } else {
                $id = $array[1] ?: strtolower($alias);
                $this->manager->addProxyService($alias, $array[0], $id);
            }
        }
    }

    /**
    * Adds namespaces from the config array.
    *
    * @return void
    */
    protected function addNamespaces()
    {
        foreach ($this->config[self::NAMESPACES] as $alias => $array) {
            $this->manager->addNamespace($alias, $array[0]);
        }
    }

    /**
    * Performs startup actions.
    *
    * @return void
    */
    protected function bootManager()
    {
        $value = $this->config[self::BOOT];

        if ('self' === $value) {
            $this->manager->addProxySelf();
        } elseif ('enable' === $value) {
            $this->manager->enable();
        }
    }

    /**
    * Returns true if the config requires a container
    *
    * @return bool
    */
    protected function requiresContainer()
    {
        return $this->config[self::INSTANCES] || $this->config[self::SERVICES];
    }

    /**
    * Returns an array of available configuration settings
    *
    * @return array
    */
    protected function getDefault()
    {
        return array(
            self::INSTANCES => array(),
            self::SERVICES => array(),
            self::NAMESPACES => array(),
            self::BOOT => ''
        );
    }
 }
