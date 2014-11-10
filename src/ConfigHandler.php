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
    protected function formatInput(array $config)
    {
        $result = $this->getDefault();

        foreach ($result as $key => $default) {
            $result[$key] = Input::checkConfig($config, $key, $default);
        }

        if (!is_null($result['container'])) {
            $container = Input::checkContainer($result['container']);

            foreach($result['services'] as &$service) {
                if (!Input::get($service, 2, null)) {
                    $service[2] = $container;
                }
            }
        }

        return $result;
    }

    protected function addSettings($items, $method)
    {
        $func = array($this->manager, $method);

        foreach ($items as $params) {
            call_user_func_array($func, $params);
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
