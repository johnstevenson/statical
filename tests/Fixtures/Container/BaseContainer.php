<?php
namespace Statical\Tests\Fixtures\Container;

abstract class BaseContainer
{
    protected $items = array();

    abstract public function accessor();

    public function getItem($id)
    {
        if (isset($this->items[$id])) {
            if ($this->items[$id] instanceof \Closure) {
                $this->items[$id] = $this->items[$id]($this);
            }

            return $this->items[$id];
        }
    }

    public function has($id) {
        return isset($this->items[$id]);
    }

    public function set($id, $value) {
        $this->items[$id] = $value;
    }

    public function remove($id) {
        unset($this->items[$id]);
    }
}
