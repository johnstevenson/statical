<?php
namespace App\Library\Controllers;

class Caller
{
    public function callFoo()
    {
        return Foo::getClass();
    }
}
