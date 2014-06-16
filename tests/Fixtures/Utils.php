<?php
namespace Statical\Tests\Fixtures;

class Utils
{
    public static function container()
    {
        return new \Statical\Tests\Fixtures\Container\StandardContainer();
    }

    public static function arrayContainer()
    {
        return new \Statical\Tests\Fixtures\Container\ArrayContainer();
    }

    public static function customContainer()
    {
        return new \Statical\Tests\Fixtures\Container\CustomContainer();
    }

    public static function formatContainer($container)
    {
        return array($container, $container->accessor());
    }

    public static function fooInstance()
    {
        return new \Statical\Tests\Fixtures\Foo();
    }

    public static function fooClosure()
    {
        return function ($c = null) {
            return new \Statical\Tests\Fixtures\Foo();
        };
    }

    public static function barInstance()
    {
        return new \Statical\Tests\Fixtures\Bar();
    }

    public static function barClosure()
    {
        return function ($c = null) {
            return new \Statical\Tests\Fixtures\Bar();
        };
    }

    public static function requireAutoload()
    {
        require __DIR__ .'/Autoload.php';
    }

    public static function registerAppLoader()
    {
        spl_autoload_register(__NAMESPACE__.'\\Utils::appLoader');
    }

    public static function appLoader($class)
    {
        if ('\\' == $class[0]) {
            $class = substr($class, 1);
        }

        if (0 === strpos($class, 'App\\')) {
            $file = str_replace('\\', '/', __DIR__ .'/'.$class).'.php';

            if (file_exists($file)) {
                require $file;
            }
        }
    }

    public static function unregisterAppLoader()
    {
        spl_autoload_unregister(__NAMESPACE__.'\\Utils::appLoader');
    }
}
