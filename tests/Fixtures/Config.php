<?php
namespace Statical\Tests\Fixtures;

class Config extends \Statical\Config
{
    public function __call($method, $arguments)
    {
        return call_user_func_array(array($this, $method), $arguments);
    }

    public function __get($property)
    {
        return $this->$property;
    }
}
