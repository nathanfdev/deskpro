<?php

/**
 * DeskPRO.
 *
 * @category ORM
 */

namespace Application\DeskPRO\ORM;

class CollectionHelper
{
    /**
     * @var string
     */
    protected $entity;

    /**
     * @var string
     */
    protected $prop;

    /**
     * @var callback
     */
    protected $fn_filter;

    /**
     * @var callback
     */
    protected $fn_keep_filter;

    /**
     * $fn_filter is useful if you only want to modify parts of a set.
     * For example, if you were modifying usergroups on a Person and only
     * wanted to work with agent groups, then you could use a filter to ignore
     * any non-agent groups.
     *
     * @param string        $entity
     * @param string        $prop
     * @param Callback|null $fn_filter      Callback to filter valid items of the set. Return true to allow the item
     * @param Callback|null $fn_keep_filter Existing items are passed through this filter to determine if they should be kept.
     *                                      E.g., use this to keep records that might otherwise be deleted because they dont match the 'set'
     */
    public function __construct($entity, $prop, $fn_filter = null, $fn_keep_filter = null)
    {
        $this->entity         = $entity;
        $this->prop           = $prop;
        $this->fn_filter      = $fn_filter;
        $this->fn_keep_filter = $fn_keep_filter;
    }

    /**
     * Given an array of records we want the entity to contain ("only $set"),
     * get an array of records that need to be added or removed. Essentially an easy diff.
     *
     * @param array $set
     *
     * @return array
     */
    public function getAddRemoveForSet(array $set)
    {
        $prop = $this->prop;

        $have_ids = [];
        $want_ids = [];

        foreach ($this->entity->$prop as $item) {
            if ($this->fn_keep_filter) {
                if (call_user_func($this->fn_keep_filter, $item)) {
                    continue;
                }
            }
            if ($this->fn_filter) {
                if (call_user_func($this->fn_filter, $item)) {
                    $have_ids[] = $item->id;
                }
            } else {
                $have_ids[] = $item->id;
            }
        }

        foreach ($set as $item) {
            if ($this->fn_filter) {
                if (call_user_func($this->fn_filter, $item)) {
                    $want_ids[] = $item->id;
                }
            } else {
                $want_ids[] = $item->id;
            }
        }

        $add_ids = array_diff($want_ids, $have_ids);
        $del_ids = array_diff($have_ids, $want_ids);

        return [
            'add' => $add_ids,
            'del' => $del_ids,
        ];
    }

    /**
     * Add or remove from the collection so it matcehs $set.
     *
     * @param array $set
     *
     *                   * @return array
     */
    public function setCollection(array $set)
    {
        $prop = $this->prop;

        $info    = $this->getAddRemoveForSet($set);
        $add_ids = $info['add'];
        $del_ids = $info['del'];

        foreach ($del_ids as $id) {
            foreach ($this->entity->$prop as $k => $item) {
                if ($item->id == $id) {
                    $this->entity->$prop->remove($k);
                    break;
                }
            }
        }

        foreach ($set as $item) {
            if (in_array($item->id, $add_ids)) {
                $this->entity->$prop->add($item);
            }
        }

        return $info;
    }
}
