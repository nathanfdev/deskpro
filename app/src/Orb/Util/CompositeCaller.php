<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 */

namespace Orb\Util;

class CompositeCaller
{
    /**
     * @var array
     */
    private $_objects        = array();

    /**
     * @var array
     */
    private $_object_to_tag  = array();

    /**
     * @var array
     */
    private $_tag_to_objects = array();


    /**
     * Add an object to the composite collection
     *
     * @param mixed       $object
     * @param string|null $tag
     */
    public function addObject($object, $tag = null)
    {
        $id = spl_object_hash($object);
        $this->_objects[$id] = $object;

        if ($tag !== null) {
            if (!isset($this->_tag_to_objects[$tag])) {
                $this->_tag_to_objects[$tag] = array();
            }
            $this->_tag_to_objects[$tag][$id] = $object;
            $this->_object_to_tag[$id] = $tag;
        }
    }


    /**
     * Count number of objects
     *
     * @param  string|null $for_tag
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
     * Get objects
     *
     * @param  string|null $for_tag
     * @return array
     */
    public function getObjects($for_tag = null)
    {
        if ($for_tag !== null) {
            if (isset($this->_tag_to_objects[$for_tag])) {
                return $this->_tag_to_objects[$for_tag];
            }

            return array();
        }

        return $this->_objects;
    }


    /**
     * Remove an object
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
     * Remove all objects with a certain tag
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

        $this->_tag_to_objects = array();
    }


    /**
     * Call a method on all objects
     *
     * @param  string      $method
     * @param  array       $args
     * @param  string|null $for_tag
     * @param  bool        $collect_exceptions True to collect exceptions to the return array rather than throwing
     * @throws \Exception  Re-throws any exception that happens unless $collect_exceptions is true
     * @return array
     */
    public function callMethod($method, $args, $for_tag = null, $collect_exceptions = false)
    {
        $objects = $this->getObjects($for_tag);

        $ret_vals = array();
        foreach ($objects as $obj) {
            $exception = null;
            try {
                $ret = call_user_func_array(array($obj, $method), $args);
            } catch (\Exception $e) {
                if (!$collect_exceptions) {
                    throw $e;
                }

                $exception = $e;
                $ret = null;
            }
            $ret_vals[] = array(
                'object'    => $obj,
                'return'    => $ret,
                'exception' => $exception,
            );
        }

        return $ret_vals;
    }
}
