<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\People\AgentPermissions;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\People\AbstractGroupDbPersister;

class GroupDbPersister extends AbstractGroupDbPersister
{
    /**
     * @param Person           $person
     * @param AgentPermissions $perms
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function saveOverridePerms(Person $person, AgentPermissions $perms)
    {
        $current_perms = $this->db->fetchAllCol('SELECT name FROM permissions WHERE person_id = ?', [$person->id]);

        $group_perms  = new GroupsDbLoader($person->usergroups ? $person->usergroups->toArray() : [], $this->em);
        $via_groups   = [];
        $names_loader = new PermissionNamesLoader();

        foreach ($person->usergroups as $ug) {
            if ($ug->sys_name == 'agent_all_perms' || $ug->sys_name == 'agent_all_safe_perms') {
                $ug_perms = $names_loader->getEnabledForGroup($ug);
                if ($ug_perms) {
                    $ug_perms = array_fill_keys($ug_perms, true);
                } else {
                    $ug_perms = [];
                }
            } else {
                $ug_perms = $group_perms->getGroupPermissions($ug->id);
                if ($ug_perms) {
                    $ug_perms = $ug_perms->toArray();
                } else {
                    $ug_perms = [];
                }
            }
            $via_groups = array_merge($via_groups, $ug_perms);
        }

        $set_perms = [];
        foreach (AgentPermissions::$prefix_map as $real_name => $coll_name) {
            $obj = $perms->$coll_name;
            foreach ($obj->getNames() as $prop) {
                if ($obj->$prop) {
                    $n = $real_name.'.'.$prop;

                    if (!isset($via_groups[$n])) {
                        $set_perms[] = $n;
                    }
                }
            }
        }

        $del_perms = array_diff($current_perms, $set_perms);
        $new_perms = array_diff($set_perms, $current_perms);

        $ins = [];
        if ($new_perms) {
            foreach ($new_perms as $p) {
                $ins[] = ['person_id' => $person->id, 'name' => $p, 'value' => 1, 'is_active' => 1];
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

    /**
     * {@inheritdoc}
     */
    protected function getPermissionsProperties()
    {
        return AgentPermissions::$prefix_map;
    }
}
