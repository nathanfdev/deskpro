<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
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
use Application\DeskPRO\Entity\DepartmentPermission;
use Application\DeskPRO\ORM\EntityManager;
use Application\DeskPRO\People\PermissionMatrix;

class DepartmentPermissionMatrix extends PermissionMatrix
{
    /**
     * Get an array of DepartmentPermission entities for the current permission set on the specified departmetn
     *
     * @param  Department $department
     * @return array
     */
    public function getPermRecords(Department $department)
    {
        $recs = array();
        foreach ($this->getPermsArray() as $row) {
            $rec             = new DepartmentPermission();
            $rec->department = $department;
            $rec->name       = $row['name'];
            $rec->value      = 1;

            if (!empty($row['usergroup_id']) && $row['usergroup_id'] && isset($this->agent_groups[$row['usergroup_id']])) {
                $rec->usergroup = $this->agent_groups[$row['usergroup_id']];
            } elseif (!empty($row['usergroup_id']) && $row['usergroup_id'] && $row['usergroup_id'] && isset($this->user_groups[$row['usergroup_id']])) {
                $rec->usergroup = $this->user_groups[$row['usergroup_id']];
            } elseif (!empty($row['person_id']) && $row['person_id'] && isset($this->agents[$row['person_id']])) {
                $rec->person = $this->agents[$row['person_id']];
            } else {
                continue;
            }

            if ($department->is_tickets_enabled) {
                $rec->app = 'tickets';
            } elseif ($department->is_chat_enabled) {
                $rec->app = 'chat';
            }

            $recs[] = $rec;
        }

        return $recs;
    }

    /**
     * Get a diff of DepartmentPermission records that should be added/removed to make the current permissions set live.
     *
     * Returns an array:
     * <code>
     * array('create' => array(...), 'remove' => array(...))
     * </code>
     *
     * @param  Department    $department
     * @param  EntityManager $em
     * @return array
     */
    public function getDiff(Department $department, EntityManager $em)
    {
        $recs = array();
        foreach ($this->getPermRecords($department) as $rec) {
            $recs[$rec->getPermissionSysId()] = $rec;
        }

        if ($department->is_tickets_enabled) {
            $existing = $em->getRepository('DeskPRO:DepartmentPermission')->getRecordsForDepartment(
                $department,
                'tickets'
            );
        }

        if ($department->is_chat_enabled) {
            $existing = $em->getRepository('DeskPRO:DepartmentPermission')->getRecordsForDepartment(
                $department,
                'chat'
            );
        }

        $remove = array();

        foreach ($existing as $rec) {
            // Already exists, so dont re-insert
            if (isset($recs[$rec->getPermissionSysId()])) {
                unset($recs[$rec->getPermissionSysId()]);

            // Doesnt exist, so its been removed
            } else {
                $remove[] = $rec;
            }
        }

        $add = array_values($recs);

        return array(
            'create'  => $add,
            'remove'  => $remove,
        );
    }

    /**
     * Applies the current permission set to the database.
     *
     * @param Department    $department
     * @param EntityManager $em
     */
    public function save(Department $department, EntityManager $em)
    {
        $diff = $this->getDiff($department, $em);

        $em->getConnection()->beginTransaction();
        foreach ($diff['remove'] as $rec) {
            $em->remove($rec);
        }
        foreach ($diff['create'] as $rec) {
            $em->persist($rec);
        }
        $em->flush();
        $em->commit();
    }
}
