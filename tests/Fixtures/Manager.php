<?php
namespace Statical\Tests\Fixtures;

class Manager extends \Statical\Manager
{
    public function __call($method, $arguments)
    {
        return call_user_func_array(array($this, $method), $arguments);
    }

    public function __get($property)
    {
        return $this->$property;
    }

    public function sayHello()
    {
        return 'Hello';
    }
}
