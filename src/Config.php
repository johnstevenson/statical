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

 class Config
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
    * Returns an array of available empty configuration settings
    *
    * @return array
    */
    public static function getEmpty()
    {
        return array(
            'instances' => array(),
            'services' => array(),
            'namespaces' => array(),
            'boot' => ''
        );
    }

    /**
    * Merges config values with the default set.
    *
    * @param array $input
    * @throws InvalidArgumentException
    * @return array
    */
    protected function formatInput(array $input)
    {
        $config = static::getEmpty();

        foreach ($config as $key => $default) {
            $config[$key] = Input::checkConfig($input, $key, $default);
        }

        return $config;
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
 }
