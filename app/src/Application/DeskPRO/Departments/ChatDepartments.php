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

namespace Application\DeskPRO\Departments;

use Application\DeskPRO\Entity\Department;
use Application\DeskPRO\Hierarchy\LazyPreloadedHierarchy;

class ChatDepartments extends LazyPreloadedHierarchy
{
    /**
     * @return array
     */

    protected function loadRecords()
    {
        return $this->em->getRepository('DeskPRO:Department')->getChatDepartments();
    }

    ####################################################################################################################
    // implementing these just for better auto-complete in the IDE (due to @return) :-)

    /**
     * @param  int                                    $id
     * @return \Application\DeskPRO\Entity\Department
     */

    public function getById($id)
    {
        return parent::getById($id);
    }

    /**
     * @param  array                                    $ids
     * @return \Application\DeskPRO\Entity\Department[]
     */

    public function getByIds(array $ids)
    {
        return parent::getByIds($ids);
    }

    /**
     * @param $obj_or_id
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
     * @param                                           $obj_or_id
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
}
