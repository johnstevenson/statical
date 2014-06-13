<?php
namespace Statical\Tests\Fixtures\Container;

class ArrayContainer extends BaseContainer implements \ArrayAccess
{
    public function accessor()
    {
        return 'offsetGet';
    }

    public function offsetExists($id)
    {
        return $this->has($id);
    }

    public function offsetGet($id)
    {
        return $this->getItem($id);
    }

    public function offsetSet($id, $value)
    {
        $this->set($id, $value);
    }

    public function offsetUnset($id)
    {
        $this->remove($id);
    }
}
