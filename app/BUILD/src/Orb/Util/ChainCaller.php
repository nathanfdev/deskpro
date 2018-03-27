<?php

/**
 * Orb.
 *
 * @category Util
 */

namespace Orb\Util;

/**
 * A simple class that tracks a collection of objects, and calls a method on all
 * of them whenever a method is invoked.
 */
class ChainCaller
{
    /** @var arrayApplication\UserBundle\Controller\Helper\CommentsAdapter\AbstractComments */
    protected $_objects = [];

    /**
     * Add a new object to the chain.
     *
     * @param array $object
     */
    public function addObject($object)
    {
        $this->_objects[] = $object;
    }

    /**
     * Get all objects in the chain.
     *
     * @return array
     */
    public function getObjects()
    {
        return $this->_objects;
    }

    /**
     * Magic method calls each object in the chain.
     */
    public function __call($name, $arguments)
    {
        foreach ($this->_objects as $obj) {
            $value = call_user_func_array([$obj, $name], $arguments);
        }

        return $value;
    }
}
