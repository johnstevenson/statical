<?php
namespace Statical\Tests\Fixtures;

class Foo
{
    public function getClass()
    {
        return get_called_class();
    }
}
