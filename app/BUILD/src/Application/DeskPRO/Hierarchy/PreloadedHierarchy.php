<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Hierarchy;

class PreloadedHierarchy
{
    /**
     * @var array
     */
    protected $objects;

    /**
     * @var array
     */
    protected $root_ids = [];

    /**
     * @var array
     */
    protected $child_to_parent = [];

    /**
     * @var array
     */
    protected $parent_to_children = [];

    /**
     * @var array
     */
    protected $all_values;

    /**
     * @var array
     */
    protected $all_ids;

    /**
     * @var int
     */
    protected $count = 0;

    /**
     * @var int
     */
    protected $count_roots;

    public function __construct($objects)
    {
        $this->objects = [];

        foreach ($objects as $obj) {
            $this->objects[$obj->getId()] = $obj;

            if (!$obj->parent) {
                $this->root_ids[] = $obj->getId();
            } else {
                $pid                                  = $obj->parent->getId();
                $this->child_to_parent[$obj->getId()] = $pid;

                if (!isset($this->parent_to_children[$pid])) {
                    $this->parent_to_children[$pid] = [];
                }
                $this->parent_to_children[$pid][$obj->getId()] = $obj->getId();
            }
        }

        $this->count       = count($this->objects);
        $this->count_roots = count($this->root_ids);
    }

    /**
     * Get by ID.
     *
     * @param int $id
     *
     * @return mixed Returns null when not found
     */
    public function getById($id)
    {
        return isset($this->objects[$id]) ? $this->objects[$id] : null;
    }

    /**
     * Get an array of objects by an array of IDs.
     *
     * @param array $ids
     *
     * @return array
     */
    public function getByIds(array $ids)
    {
        $ret = [];
        foreach ($ids as $id) {
            if (!isset($this->objects[$id])) {
                continue;
            }
            $ret[] = $this->objects[$id];
        }

        return $ret;
    }

    /**
     * Check if an object is a child or is a root level.
     *
     * @param mixed $obj_or_id
     *
     * @return bool
     */
    public function isChild($obj_or_id)
    {
        $id = is_object($obj_or_id) ? $obj_or_id->getId() : intval($obj_or_id);

        return isset($this->child_to_parent[$id]);
    }

    /**
     * Check if an object is a root level.
     *
     * @param mixed $obj_or_id
     *
     * @return bool
     */
    public function isRoot($obj_or_id)
    {
        return !$this->isChild($obj_or_id);
    }

    /**
     * Get the parent object.
     *
     * @param $obj_or_id
     *
     * @return mixed|null
     */
    public function getParent($obj_or_id)
    {
        $parent_id = $this->getParentId($obj_or_id);
        if ($parent_id === null) {
            return;
        }

        return $this->getById($parent_id);
    }

    /**
     * Get the parent ID.
     *
     * @param $obj_or_id
     */
    public function getParentId($obj_or_id)
    {
        $id = is_object($obj_or_id) ? $obj_or_id->getId() : intval($obj_or_id);

        if (!$this->isChild($id)) {
            return;
        }

        $parent_id = $this->child_to_parent[$id];

        return $parent_id;
    }

    /**
     * Get an array of parent IDs from deepest to root.
     *
     * @return array
     */
    public function getParentPathIds($obj_or_id)
    {
        $id = is_object($obj_or_id) ? $obj_or_id->getId() : intval($obj_or_id);

        if ($this->isRoot($id)) {
            return [];
        }

        $parent_ids = [];
        $current_id = $id;

        while (isset($this->child_to_parent[$current_id])) {
            $current_id   = $this->child_to_parent[$current_id];
            $parent_ids[] = $current_id;
        }

        return $parent_ids;
    }

    /**
     * Get an array of parent objects from deepest to root.
     *
     * @return array
     */
    public function getParentPath($obj_or_id, $keyed = false)
    {
        $id         = is_object($obj_or_id) ? $obj_or_id->getId() : intval($obj_or_id);
        $parent_ids = $this->getParentPathIds($id);
        $parents    = [];

        foreach ($parent_ids as $pid) {
            $parents[] = $this->getById($pid);
        }

        return $parents;
    }

    /**
     * Check if an object has children.
     *
     * @param $obj_or_id
     *
     * @return bool
     */
    public function hasChildren($obj_or_id)
    {
        $id = is_object($obj_or_id) ? $obj_or_id->getId() : intval($obj_or_id);

        return isset($this->parent_to_children[$id]);
    }

    /**
     * @param $obj_or_id
     *
     * @return int
     */
    public function countChildren($obj_or_id)
    {
        $id = is_object($obj_or_id) ? $obj_or_id->getId() : intval($obj_or_id);

        return isset($this->parent_to_children[$id]) ? count($this->parent_to_children[$id]) : 0;
    }

    /**
     * Get children on an object.
     *
     * @param $obj_or_id
     *
     * @return bool
     */
    public function getChildrenIds($obj_or_id)
    {
        $id = is_object($obj_or_id) ? $obj_or_id->getId() : intval($obj_or_id);
        if (!isset($this->parent_to_children[$id])) {
            return [];
        }

        $child_ids = [];
        foreach ($this->parent_to_children[$id] as $cid) {
            $child_ids[] = $cid;
        }

        return $child_ids;
    }

    /**
     * @param $obj_or_id
     *
     * @return array
     */
    public function getChildren($obj_or_id)
    {
        $child_ids = $this->getChildrenIds($obj_or_id);
        $childs    = [];

        foreach ($child_ids as $cid) {
            $childs[] = $this->getById($cid);
        }

        return $childs;
    }

    /**
     * @return array
     */
    public function getRoots()
    {
        return $this->getByIds($this->root_ids);
    }

    /**
     * @return array
     */
    public function getRootIds()
    {
        return $this->root_ids;
    }

    /**
     * @return array
     */
    public function getAllIds()
    {
        if ($this->all_ids === null) {
            $this->all_ids = array_keys($this->objects);
        }

        return $this->all_ids;
    }

    /**
     * @return array
     */
    public function getAll()
    {
        if ($this->all_values === null) {
            $this->all_values = array_values($this->objects);
        }

        return $this->all_values;
    }

    /**
     * Gets an array of array('object' => $obj, 'depth' => 1), in order.
     *
     * @return array
     */
    public function getFlatArray()
    {
        return $this->getFlatArrayRecursive($this->getRoots());
    }

    private function getFlatArrayRecursive($objects, $depth = 0)
    {
        $flat = [];

        foreach ($objects as $obj) {
            $flat[] = ['object' => $obj, 'depth' => $depth];

            if ($this->hasChildren($obj)) {
                $subs = $this->getFlatArrayRecursive($this->getChildren($obj), $depth + 1);
                $flat = array_merge($flat, $subs);
            }
        }

        return $flat;
    }

    /**
     * @return int
     */
    public function count()
    {
        return $this->count;
    }

    /**
     * @return int
     */
    public function countRoots()
    {
        return $this->count_roots;
    }
}
