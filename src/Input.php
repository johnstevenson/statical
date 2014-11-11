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
    * @param object|array $container
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
