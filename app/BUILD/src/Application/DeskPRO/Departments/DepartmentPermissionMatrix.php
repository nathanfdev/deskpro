<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Departments;

use Application\DeskPRO\Entity\Department;
use Application\DeskPRO\Entity\DepartmentPermission;
use Application\DeskPRO\People\PermissionMatrix;
use Doctrine\ORM\EntityManager;

class DepartmentPermissionMatrix extends PermissionMatrix
{
    /**
     * Get an array of DepartmentPermission entities for the current permission set on the specified departmetn.
     *
     * @param Department $department
     *
     * @return array
     */
    public function getPermRecords(Department $department)
    {
        $recs = [];
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
     * @param Department    $department
     * @param EntityManager $em
     *
     * @return array
     */
    public function getDiff(Department $department, EntityManager $em)
    {
        $recs = [];
        foreach ($this->getPermRecords($department) as $rec) {
            $recs[$rec->getPermissionSysId()] = $rec;
        }

        if ($department->is_tickets_enabled) {
            $ticketsExisting = $em->getRepository('DeskPRO:DepartmentPermission')->getRecordsForDepartment(
                $department,
                'tickets'
            );
        } else {
            $ticketsExisting = [];
        }

        if ($department->is_chat_enabled) {
            $chatsExisting = $em->getRepository('DeskPRO:DepartmentPermission')->getRecordsForDepartment(
                $department,
                'chat'
            );
        } else {
            $chatsExisting = [];
        }

        $existing = array_merge($ticketsExisting, $chatsExisting);

        $remove = [];

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

        return [
            'create' => $add,
            'remove' => $remove,
        ];
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
