<?php
namespace App\Library\Views;

class Caller
{
    public function callFoo()
    {
        return Foo::getClass();
    }
}
