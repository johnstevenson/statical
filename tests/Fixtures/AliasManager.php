<?php
namespace Statical\Tests\Fixtures;

class AliasManager extends \Statical\AliasManager
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
