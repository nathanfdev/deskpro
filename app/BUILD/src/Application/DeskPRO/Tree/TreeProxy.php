<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Tree;

/**
 * A wrapper around any object that has 'children' that allows you to define the structure
 * on-the-fly with a callback filter.
 *
 * This is ideal in templates. For example, you have a category hierarchy but the user only has permission
 * to view a subset of them. From the controller you can just pass the normal hierarchy wrapped in this class
 * with a filter.
 */
class TreeProxy implements \ArrayAccess
{
    /** @var mixed */
    protected $__obj;
    /** @var callable */
    protected $__filter;
    /** @var array */
    protected $__child_cache;

    /**
     * Go over each item in an array and wrap it with this tree proxy and $filter.
     *
     * @param array    $array
     * @param callback $filter
     *
     * @return array
     */
    public static function makeTreeProxyArray($array, $filter)
    {
        $ret = [];

        foreach ($array as $k => $c) {
            if (!call_user_func($filter, $c, $k)) {
                continue;
            }

            $c_obj   = new static($c, $filter);
            $ret[$k] = $c_obj;
        }

        return $ret;
    }

    /**
     * @param mixed    $obj
     * @param callback $filter Any callback that adheres to function($obj, $key)
     */
    public function __construct($obj, $filter)
    {
        $this->__obj    = $obj;
        $this->__filter = $filter;
    }

    /**
     * @return mixed
     */
    public function getObject()
    {
        return $this->__obj;
    }

    /**
     * Get the children that pas the filter, with each child itself being wrapped with the same filter.
     *
     * @return array
     */
    public function getChildren()
    {
        if ($this->__child_cache !== null) {
            return $this->__child_cache;
        }

        $children = $this->__obj->getChildren();
        if (!$children || !count($children)) {
            $this->__child_cache = [];

            return [];
        }
        $children = $children->toArray();

        uasort($children, function ($a, $b) {
            if (!isset($a->display_order)) {
                return 0;
            }

            if ($a->display_order == $b->display_order) {
                return 0;
            }

            return ($a->display_order < $b->display_order) ? -1 : 1;
        });

        $this->__child_cache = self::makeTreeProxyArray($children, $this->__filter);

        return $this->__child_cache;
    }

    //###################################################################################################################
    // Implementations of magic methods
    //###################################################################################################################

    public function offsetExists($offset)
    {
        if ($offset == 'children' || $offset == 'children_ordered') {
            return true;
        }

        return isset($this->__obj[$offset]);
    }

    public function offsetGet($offset)
    {
        if ($offset == 'children' || $offset == 'children_ordered') {
            return $this->getChildren();
        }

        return $this->__obj[$offset];
    }

    public function offsetSet($offset, $value)
    {
        if ($offset == 'children' || $offset == 'children_ordered') {
            throw new \RuntimeException();
        }

        $this->__obj[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        if ($offset == 'children' || $offset == 'children_ordered') {
            throw new \RuntimeException();
        }

        unset($this->__obj[$offset]);
    }

    public function __call($name, $arguments)
    {
        if (is_callable([$this->__obj, $name])) {
            return call_user_func_array([$this->__obj, $name], $arguments);
        } else {
            return;
        }
    }

    public function __get($name)
    {
        if ($name == 'children' || $name == 'children_ordered') {
            return $this->getChildren();
        }

        return $this->__obj->$name;
    }

    public function __set($name, $value)
    {
        if ($name == 'children' || $name == 'children_ordered') {
            throw new \RuntimeException();
        }

        $this->__obj->$name = $value;
    }
}
