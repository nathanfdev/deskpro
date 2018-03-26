<?php

/**
 * DeskPRO.
 */

namespace Orb\Util;

class CompositeCaller
{
    /**
     * @var array
     */
    private $_objects = [];

    /**
     * @var array
     */
    private $_object_to_tag = [];

    /**
     * @var array
     */
    private $_tag_to_objects = [];

    /**
     * Add an object to the composite collection.
     *
     * @param mixed       $object
     * @param string|null $tag
     */
    public function addObject($object, $tag = null)
    {
        $id                  = spl_object_hash($object);
        $this->_objects[$id] = $object;

        if ($tag !== null) {
            if (!isset($this->_tag_to_objects[$tag])) {
                $this->_tag_to_objects[$tag] = [];
            }
            $this->_tag_to_objects[$tag][$id] = $object;
            $this->_object_to_tag[$id]        = $tag;
        }
    }

    /**
     * Count number of objects.
     *
     * @param string|null $for_tag
     *
     * @return int
     */
    public function countObjects($for_tag = null)
    {
        if ($for_tag !== null) {
            if (isset($this->_tag_to_objects[$for_tag])) {
                return count($this->_tag_to_objects[$for_tag]);
            }

            return 0;
        }

        return count($this->_objects);
    }

    /**
     * Get objects.
     *
     * @param string|null $for_tag
     *
     * @return array
     */
    public function getObjects($for_tag = null)
    {
        if ($for_tag !== null) {
            if (isset($this->_tag_to_objects[$for_tag])) {
                return $this->_tag_to_objects[$for_tag];
            }

            return [];
        }

        return $this->_objects;
    }

    /**
     * Remove an object.
     *
     * @param mixed $object
     */
    public function removeObject($object)
    {
        $id = spl_object_hash($object);
        unset($this->_objects[$id]);

        if (isset($this->_object_to_tag[$id])) {
            $tag = $this->_object_to_tag[$id];
            unset($this->_object_to_tag[$tag]);
            unset($this->_tag_to_objects[$tag][$id]);

            if (empty($this->_tag_to_objects[$tag])) {
                unset($this->_tag_to_objects[$tag]);
            }
        }
    }

    /**
     * Remove all objects with a certain tag.
     *
     * @param string $tag
     */
    public function removeTaggedObjects($tag)
    {
        if (!isset($this->_tag_to_objects[$tag])) {
            return;
        }

        foreach ($this->_tag_to_objects[$tag] as $object) {
            $id = spl_object_hash($object);

            unset($this->_objects[$id]);
            unset($this->_object_to_tag[$id]);
        }

        $this->_tag_to_objects = [];
    }

    /**
     * Call a method on all objects.
     *
     * @param string      $method
     * @param array       $args
     * @param string|null $for_tag
     * @param bool        $collect_exceptions True to collect exceptions to the return array rather than throwing
     *
     * @throws \Exception Re-throws any exception that happens unless $collect_exceptions is true
     *
     * @return array
     */
    public function callMethod($method, $args, $for_tag = null, $collect_exceptions = false)
    {
        $objects = $this->getObjects($for_tag);

        $ret_vals = [];
        foreach ($objects as $obj) {
            $exception = null;
            try {
                $ret = call_user_func_array([$obj, $method], $args);
            } catch (\Exception $e) {
                if (!$collect_exceptions) {
                    throw $e;
                }

                $exception = $e;
                $ret       = null;
            }
            $ret_vals[] = [
                'object'    => $obj,
                'return'    => $ret,
                'exception' => $exception,
            ];
        }

        return $ret_vals;
    }
}
