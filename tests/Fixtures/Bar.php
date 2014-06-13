<?php
namespace Statical\Tests\Fixtures;

class Bar
{
    public function getClass()
    {
        return get_called_class();
    }
}
