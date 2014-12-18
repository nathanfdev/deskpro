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
 * @category Entities
 */

namespace Application\DeskPRO\People\AgentPermissions;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Usergroup;
use Doctrine\ORM\EntityManager;

class GroupDbPersister
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @var \Application\DeskPRO\DBAL\Connection
     */
    private $db;


    /**
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em        = $em;
        $this->db        = $em->getConnection();
    }


    /**
     * @param  Usergroup        $group
     * @param  AgentPermissions $perms
     * @return bool
     * @throws \Exception
     */
    public function savePerms(Usergroup $group, AgentPermissions $perms)
    {
        $current_perms = $this->db->fetchAllCol("SELECT name FROM permissions WHERE usergroup_id = ?", array($group->id));

        $set_perms = array();
        foreach (GroupsDbLoader::$prefix_map as $real_name => $coll_name) {
            $obj = $perms->$coll_name;
            foreach ($obj->getNames() as $prop) {
                if ($obj->$prop) {
                    $set_perms[] = $real_name . '.' . $prop;
                }
            }
        }

        $del_perms = array_diff($current_perms, $set_perms);
        $new_perms = array_diff($set_perms, $current_perms);

        $ins = array();
        if ($new_perms) {
            foreach ($new_perms as $p) {
                $ins[] = array('usergroup_id' => $group->id, 'name' => $p, 'value' => 1);
            }

        }

        $this->db->beginTransaction();
        try {
            if ($del_perms) {
                $this->db->deleteIn('permissions', $del_perms, 'name', false, "usergroup_id = {$group->id}");
            }
            if ($ins) {
                $this->db->batchInsert('permissions', $ins, true);
            }

            $this->db->commit();
        } catch (\Exception $e) {
            $this->db->rollback();
            throw $e;
        }

        return true;
    }


    /**
     * @param  Person           $person
     * @param  AgentPermissions $perms
     * @return bool
     * @throws \Exception
     */
    public function saveOverridePerms(Person $person, AgentPermissions $perms)
    {
        $current_perms = $this->db->fetchAllCol("SELECT name FROM permissions WHERE person_id = ?", array($person->id));

        $group_perms = new GroupsDbLoader($person->usergroups, $this->em);
        $via_groups = array();
        $names_loader = new PermissionNamesLoader();

        foreach ($person->usergroups as $ug) {
            if ($ug->sys_name == 'agent_all_perms' || $ug->sys_name == 'agent_all_safe_perms') {
                $ug_perms = $names_loader->getEnabledForGroup($ug);
                if ($ug_perms) {
                    $ug_perms = array_fill_keys($ug_perms, true);
                }
            } else {
                $ug_perms = $group_perms->getGroupPermissions($ug->id);
            }
            $via_groups = array_merge($via_groups, $ug_perms);
        }

        $set_perms = array();
        foreach (GroupsDbLoader::$prefix_map as $real_name => $coll_name) {
            $obj = $perms->$coll_name;
            foreach ($obj->getNames() as $prop) {
                if ($obj->$prop) {
                    $n = $real_name . '.' . $prop;

                    if (!isset($via_groups[$n])) {
                        $set_perms[] = $n;
                    }
                }
            }
        }

        $del_perms = array_diff($current_perms, $set_perms);
        $new_perms = array_diff($set_perms, $current_perms);

        $ins = array();
        if ($new_perms) {
            foreach ($new_perms as $p) {
                $ins[] = array('person_id' => $person->id, 'name' => $p, 'value' => 1);
            }

        }

        $this->db->beginTransaction();
        try {
            if ($del_perms) {
                $this->db->deleteIn('permissions', $del_perms, 'name', false, "person_id = {$person->id}");
            }
            if ($ins) {
                $this->db->batchInsert('permissions', $ins, true);
            }

            $this->db->commit();
        } catch (\Exception $e) {
            $this->db->rollback();
            throw $e;
        }

        return true;
    }
}
