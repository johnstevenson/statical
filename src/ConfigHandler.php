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
    public function apply(array $config)
    {
        $config = $this->formatInput($config);

        $this->addSettings($config['instances'], 'addProxyInstance');
        $this->addSettings($config['services'], 'addProxyService');
        $this->addSettings($config['namespaces'], 'addNamespace');

        $this->bootManager($config['boot']);
    }

    /**
    * Merges config values with the default set.
    *
    * @param array $config
    * @throws InvalidArgumentException
    * @return array
    */
    protected function formatInput(array $input)
    {
        $config = $this->getDefault();

        foreach ($config as $key => $default) {
            $config[$key] = Input::checkConfig($input, $key, $default);
        }

        if (!is_null($config['container'])) {
            $this->checkContainer($config);
        }

        return $config;
    }

    protected function checkContainer(&$config)
    {
        $container = Input::checkContainer($config['container']);

        foreach($config['services'] as &$service) {
            if (!Input::get($service, 2, null)) {
                $service[2] = $container;
            }
        }
    }

    /**
    * Adds a group of settings to the Statical Manager.
    *
    * @param array $items The config group of settings
    * @param string $method The name of the manager method to invoke
    * @return void
    */
    protected function addSettings($items, $method)
    {
        foreach ($items as $params) {
            call_user_func_array(array($this->manager, $method), $params);
        }
    }

    /**
    * Performs startup actions.
    *
    * @return void
    */
    protected function bootManager($boot)
    {
        if ('self' === $boot) {
            $this->manager->addProxySelf();
        } elseif ('enable' === $boot) {
            $this->manager->enable();
        }
    }

    /**
    * Returns an array of available configuration settings
    *
    * @return array
    */
    protected function getDefault()
    {
        return array(
            'instances' => array(),
            'services' => array(),
            'container' => null,
            'namespaces' => array(),
            'boot' => ''
        );
    }
 }
