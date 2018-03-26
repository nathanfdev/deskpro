<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Tickets;

use Application\DeskPRO\Hierarchy\LazyPreloadedHierarchy;

class TicketCategories extends LazyPreloadedHierarchy
{
    /**
     * @var int
     */
    private $default_id;

    /**
     * @return array
     */
    protected function loadRecords()
    {
        return $this->em->getRepository('DeskPRO:TicketCategory')->getCategories();
    }

    /**
     * This sets the 'default department' preference.
     * Note that setting an invalid or bogus ID here will not result in an exception.
     *
     * With an invalid pref, getDefaultDepartment will return the first selectable department.
     * Use getById() to check if a department exists before calling this.
     *
     * @param int $dep_or_id
     */
    public function setDefaultCategoryPreference($obj_or_id)
    {
        if ($obj_or_id === null) {
            $this->default_id = null;
        } elseif (is_object($obj_or_id)) {
            $this->default_id = $obj_or_id->id;
        } else {
            $this->default_id = intval($obj_or_id);
        }
    }

    /**
     * @return \Application\DeskPRO\Entity\TicketCategory
     */
    public function getDefaultCategory()
    {
        if ($this->default_id && (!$this->getById($this->default_id) || $this->hasChildren($this->default_id))) {
            foreach ($this->getAll() as $dep) {
                $this->default_id = $dep->getId();
                break;
            }
        }

        if (!$this->default_id) {
            return;
        }

        return $this->getById($this->default_id);
    }

    /**
     * Returns a settable object. That is an entity that is not a parent.
     * Returns null if the passed $id is invalid or is not a valid settable.
     *
     * @param int $id
     *
     * @return \Application\DeskPRO\Entity\TicketCategory|null
     */
    public function getSettableById($id)
    {
        $obj = $this->getById($id);
        if (!$obj || $this->getChildren($obj)) {
            return;
        }

        return $obj;
    }

    //###################################################################################################################
    // implementing these just for better auto-complete in the IDE (due to @return) :-)

    /**
     * @param int $id
     *
     * @return \Application\DeskPRO\Entity\TicketCategory[]
     */
    public function getById($id)
    {
        return parent::getById($id);
    }

    /**
     * @param array $ids
     *
     * @return \Application\DeskPRO\Entity\TicketCategory[]
     */
    public function getByIds(array $ids)
    {
        return parent::getByIds($ids);
    }

    /**
     * @param $obj_or_id
     *
     * @return \Application\DeskPRO\Entity\TicketCategory[]
     */
    public function getParent($obj_or_id)
    {
        return parent::getParent($obj_or_id);
    }

    /**
     * @return \Application\DeskPRO\Entity\TicketCategory[]
     */
    public function getParentPath($obj_or_id, $keyed = false)
    {
        return parent::getParentPath($obj_or_id, $keyed);
    }

    /**
     * @return \Application\DeskPRO\Entity\TicketCategory[]
     */
    public function getChildren($obj_or_id)
    {
        return parent::getChildren($obj_or_id);
    }

    /**
     * @return \Application\DeskPRO\Entity\TicketCategory[]
     */
    public function getRoots()
    {
        return parent::getRoots();
    }

    /**
     * @return \Application\DeskPRO\Entity\TicketCategory[]
     */
    public function getAll()
    {
        return parent::getAll();
    }
}
