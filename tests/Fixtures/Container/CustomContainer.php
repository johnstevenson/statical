<?php
namespace Statical\Tests\Fixtures\Container;

class CustomContainer extends BaseContainer
{
    public function accessor()
    {
        return 'getValue';
    }

    public function getValue($id)
    {
        return $this->getItem($id);
    }
}
