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

 /**
 * This class provides functions to check various input values and throw an
 * InvalidArgumentException on failure.
 *
 * All functions are static, enabling their use from various component classes.
 */
 class Input
 {
    /**
    * Checks that an alias has no backslash characters
    *
    * @param string $value
    * @throws InvalidArgumentException
    * @return string
    */
    public static function checkAlias($value)
    {
        $value = static::check($value);

        if (strpos($value, '\\')) {
            throw new \InvalidArgumentException('Alias must not be namespaced.');
        }

        return $value;
    }

    /**
    * Checks that namespace has no leading or trailing backslashes
    *
    * @param string $value
    * @throws InvalidArgumentException
    * @return string
    */
    public static function checkNamespace($value)
    {
        $value = static::check($value);

        if (0 === strpos($value, '\\') || '\\' === substr($value, -1)) {
            throw new \InvalidArgumentException('Invalid namespace.');
        }

        return $value;
    }

    /**
    * Checks that the container is valid
    *
    * @param mixed $container
    * @throws InvalidArgumentException
    * @return callable
    */
    public static function checkContainer($container)
    {
        $result = static::formatContainer($container);

        if (!is_callable($result)) {
            throw new \InvalidArgumentException('Container must be a callable.');
        }

        return $result;
    }

    /**
    * Extracts a config value and checks its type against the default.
    *
    * @param array $config
    * @param string $key
    * @param mixed $default
    * @throws InvalidArgumentException
    * @return mixed
    */
    public static function checkConfig($config, $key, $default)
    {
        $value = static::get($config, $key, $default);

        if (!is_null($default) && gettype($default) !== gettype($value)) {
            throw new \InvalidArgumentException('Invalid value for config ' . $key);
        }

        return $value;
    }

    /**
    * Returns a value from an array if the key exists, otherwise the default.
    *
    * @param array $array
    * @param string|integer $key
    * @param mixed $default
    * @return mixed
    */
    public static function get($array, $key, $default)
    {
        return isset($array[$key]) ? $array[$key] : $default;
    }

    /**
    * Checks that a value is a string and not empty
    *
    * @param string $value
    * @throws InvalidArgumentException
    * @return string
    */
    protected static function check($value)
    {
        if (!is_string($value) || !$value) {
            throw new \InvalidArgumentException('Empty or invalid value.');
        }

        return $value;
    }

    /**
    * Formats a container param as a callable.
    *
    * @param object|array $container
    * @return callable
    */
    protected static function formatContainer($container)
    {
        if (!is_array($container)) {
            $container = array($container);
        }

        $instance = array_shift($container);

        if (!$method = array_shift($container)) {
            if ($instance instanceof \ArrayAccess) {
                $method = 'offsetGet';
            } else {
                $method = 'get';
            }
        }

        return array($instance, $method);
    }
 }
