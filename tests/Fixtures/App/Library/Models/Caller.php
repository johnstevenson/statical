<?php
namespace App\Library\Models;

class Caller
{
    public function callFoo()
    {
        return Foo::getClass();
    }
}
