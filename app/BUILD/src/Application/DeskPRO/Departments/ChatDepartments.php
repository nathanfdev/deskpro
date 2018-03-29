<?php

namespace Application\DeskPRO\Departments;

use Application\DeskPRO\Entity\Department;
use Application\DeskPRO\Hierarchy\LazyPreloadedHierarchy;

class ChatDepartments extends LazyPreloadedHierarchy
{
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
        return $this->em->getRepository('DeskPRO:Department')->getChatDepartments();
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
     * @param      $obj_or_id
     * @param bool $keyed
     *
     * @return \Application\DeskPRO\Entity\Department[]
     */
    public function getParentPath($obj_or_id, $keyed = false)
    {
        return parent::getParentPath($obj_or_id, $keyed);
    }

    /**
     * @param   $obj_or_id
     *
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
     * @param Department $dep
     *
     * @return array
     */
    public function getPermissionsInfo(Department $dep)
    {
        return $this->em->getRepository('DeskPRO:Department')->getPermissionsInfo($dep);
    }

    /**
     * @return array
     */
    public function getAllAllowedIds()
    {
        if (null === $this->allAllowedIds) {
            $this->allAllowedIds = $this->connection->fetchAllCol('SELECT id FROM departments WHERE is_chat_enabled = 1');
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
                    'app'           => 'chat',
                    'name'          => 'full',
                    'value'         => 1,
                ];
            }
        }

        return $this->allAllowedList;
    }
}
