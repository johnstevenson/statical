<?php
namespace Statical\Tests\Fixtures\Container;

class StandardContainer extends BaseContainer
{
    public function accessor()
    {
        return 'get';
    }

    public function get($id)
    {
        return $this->getItem($id);
    }
}
