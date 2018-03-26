<?php

namespace Application\DeskPRO\Departments;

use Application\DeskPRO\Hierarchy\LazyPreloadedHierarchy;

class TicketDepartments extends LazyPreloadedHierarchy
{
    /**
     * @var int
     */
    private $default_id;

    /**
     * @var array
     */
    private $allAllowedIds;

    /**
     * @var array
     */
    private $allAllowedList;

    /**
     * @return array
     */
    protected function loadRecords()
    {
        return $this->em->getRepository('DeskPRO:Department')->getTicketDepartments();
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
    public function setDefaultDepartmentPreference($dep_or_id)
    {
        if ($dep_or_id === null) {
            $this->default_id = null;
        } elseif (is_object($dep_or_id)) {
            $this->default_id = $dep_or_id->id;
        } else {
            $this->default_id = intval($dep_or_id);
        }
    }

    /**
     * @return \Application\DeskPRO\Entity\Department
     */
    public function getDefaultDepartment()
    {
        if (!$this->default_id || !$this->getById($this->default_id) || $this->hasChildren($this->default_id)) {
            foreach ($this->getAll() as $dep) {
                if (!$this->hasChildren($dep)) {
                    $this->default_id = $dep->getId();
                    break;
                }
            }
        }

        return $this->getById($this->default_id);
    }

    /**
     * Returns a settable object. That is an entity that is not a parent.
     * Returns null if the passed $id is invalid or is not a valid settable.
     *
     * @param int $id
     *
     * @return \Application\DeskPRO\Entity\Department|null
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
     * @return \Application\DeskPRO\Entity\Department
     */
    public function getById($id)
    {
        return parent::getById($id);
    }

    /**
     * @param array $ids
     *
     * @return \Application\DeskPRO\Entity\Department[]
     */
    public function getByIds(array $ids)
    {
        return parent::getByIds($ids);
    }

    /**
     * @param $obj_or_id
     *
     * @return \Application\DeskPRO\Entity\Department
     */
    public function getParent($obj_or_id)
    {
        return parent::getParent($obj_or_id);
    }

    /**
     * @return \Application\DeskPRO\Entity\Department[]
     */
    public function getParentPath($obj_or_id, $keyed = false)
    {
        return parent::getParentPath($obj_or_id, $keyed);
    }

    /**
     * @return \Application\DeskPRO\Entity\Department[]
     */
    public function getChildren($obj_or_id)
    {
        return parent::getChildren($obj_or_id);
    }

    /**
     * @return \Application\DeskPRO\Entity\Department[]
     */
    public function getRoots()
    {
        return parent::getRoots();
    }

    /**
     * @return \Application\DeskPRO\Entity\Department[]
     */
    public function getAll()
    {
        return parent::getAll();
    }

    /**
     * @return array
     */
    public function getAllAllowedIds()
    {
        if (null === $this->allAllowedIds) {
            $this->allAllowedIds = $this->connection->fetchAllCol('SELECT id FROM departments WHERE is_tickets_enabled = 1');
        }

        return $this->allAllowedIds;
    }

    /**
     * @return array
     */
    public function getAllAllowedList()
    {
        if (null === $this->allAllowedList) {
            $this->allAllowedList = [];
            foreach ($this->getAllAllowedIds() as $id) {
                $this->allAllowedList[] = [
                    'department_id' => $id,
                    'app'           => 'tickets',
                    'name'          => 'full',
                    'value'         => 1,
                ];
            }
        }

        return $this->allAllowedList;
    }
}
