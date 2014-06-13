<?php
namespace App\Library;

class Foo
{
    public function getClass()
    {
        return get_called_class();
    }
}
