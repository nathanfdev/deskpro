<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\People;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Person;
use Doctrine\DBAL\Connection;

class PermissionUtil
{
    /**
     * Fetches bad permissions and removes them. This makes changes to the database.
     */
    public static function cleanPermissions()
    {
        App::getDb()->exec('DELETE FROM permissions_cache');
    }

    /**
     * Optimises permissions for a particular person.
     *
     * @param Person $person
     * @param array  $ag_perms_cache
     * @param array  $ag_dep_perms_cache
     */
    public static function optimizePermissions(Person $person, array $ag_perms_cache = null, array $ag_dep_perms_cache = null)
    {
        $db = App::getDb();

        //----------------------------------------
        // Get agent group data
        //----------------------------------------

        $ag_ids = $db->fetchAllCol('
            SELECT person2usergroups.usergroup_id
            FROM person2usergroups
            LEFT JOIN usergroups ON (usergroups.id = person2usergroups.usergroup_id)
            WHERE usergroups.is_agent_group = 1
              AND person2usergroups.person_id = ?;
        ', [$person->getId()]);

        if (!$ag_ids) {
            $ag_ids = [0];
        }

        $special_ag = $db->fetchAllKeyValue("
            SELECT sys_name, id
            FROM usergroups
            WHERE sys_name IN ('agent_all_perms', 'agent_all_safe_perms')
        ");

        //----------------------------------------
        // Figure out which permissions are granted via a usergroup
        //----------------------------------------

        if ($ag_perms_cache !== null) {
            $ag_perms_names = [];
            foreach ($ag_ids as $id) {
                if (isset($ag_perms_cache[$id])) {
                    $ag_perms_names = array_merge($ag_perms_names, $ag_perms_cache[$id]);
                }
            }
            $ag_perms_names = array_unique($ag_perms_names);
        } else {
            $ag_perms_names = $db->fetchAllCol('
                SELECT DISTINCT(name) FROM permissions
                WHERE usergroup_id IN (?)
            ', [$ag_ids], [Connection::PARAM_INT_ARRAY]);
        }

        if (in_array($special_ag['agent_all_perms'], $ag_ids)) {
            $loader         = App::$container->getSystemService('AgentPermissionNamesLoader');
            $ag_perms_names = array_merge($ag_perms_names, $loader->getNames());
        } elseif (in_array($special_ag['agent_all_safe_perms'], $ag_ids)) {
            $loader         = App::$container->getSystemService('AgentPermissionNamesLoader');
            $ag_perms_names = array_merge($ag_perms_names, $loader->getSafeNames());
        }

        //----------------------------------------
        // Figure out which department permissions are granted via a usergroup
        //----------------------------------------

        if (in_array($special_ag['agent_all_perms'], $ag_ids) || in_array($special_ag['agent_all_safe_perms'], $ag_ids)) {
            $dep_id_full_perms   = $db->fetchAllCol('SELECT id FROM departments');
            $dep_id_assign_perms = [];
        } else {
            if ($ag_dep_perms_cache !== null) {
                $dep_id_full_perms   = [];
                $dep_id_assign_perms = [];

                foreach ($ag_ids as $id) {
                    if (isset($ag_dep_perms_cache['full'][$id])) {
                        $dep_id_full_perms = array_merge($dep_id_full_perms, $ag_dep_perms_cache['full'][$id]);
                    }
                    if (isset($ag_dep_perms_cache['assign'][$id])) {
                        $dep_id_assign_perms = array_merge($dep_id_assign_perms, $ag_dep_perms_cache['assign'][$id]);
                    }
                }

                $dep_id_full_perms   = array_unique($dep_id_full_perms);
                $dep_id_assign_perms = array_unique($dep_id_assign_perms);
            } else {
                $dep_id_full_perms = $db->fetchAllCol("
                    SELECT department_permissions.department_id
                    FROM department_permissions
                    WHERE department_permissions.name = 'full'
                      AND department_permissions.is_active = 1
                      AND department_permissions.usergroup_id IN (?)
                ", [$ag_ids], [Connection::PARAM_INT_ARRAY]);

                $dep_id_assign_perms = $db->fetchAllCol("
                    SELECT department_permissions.department_id
                    FROM department_permissions
                    WHERE department_permissions.name = 'assign'
                      AND department_permissions.is_active = 1
                      AND department_permissions.usergroup_id IN (?)
                ", [$ag_ids], [Connection::PARAM_INT_ARRAY]);
            }
        }

        //----------------------------------------
        // De-activate all perms that match ones granted via perms
        //----------------------------------------

        $db->beginTransaction();

        // All perms are active until we specifically turn them off
        $db->update('permissions', ['is_active' => 1], ['person_id' => $person->getId()]);
        $db->update('department_permissions', ['is_active' => 1], ['person_id' => $person->getId()]);

        if ($ag_perms_names) {
            $db->executeUpdate('
                UPDATE permissions
                SET is_active = 0
                WHERE person_id = ? AND name IN (?)
            ', [$person->getId(), $ag_perms_names], [\PDO::PARAM_INT, Connection::PARAM_STR_ARRAY]);
        }

        if ($dep_id_full_perms) {
            $db->executeUpdate('
                UPDATE department_permissions
                SET is_active = 0
                WHERE person_id = ? AND department_id IN (?)
            ', [$person->getId(), $dep_id_full_perms], [\PDO::PARAM_INT, Connection::PARAM_INT_ARRAY]);
        }

        if ($dep_id_assign_perms) {
            $db->executeUpdate("
                UPDATE department_permissions
                SET is_active = 0
                WHERE is_active = 1 AND person_id = ? AND department_id IN (?) AND name = 'assign'
            ", [$person->getId(), $dep_id_assign_perms], [\PDO::PARAM_INT, Connection::PARAM_INT_ARRAY]);
        }

        App::getDb()->exec('DELETE FROM permissions_cache');

        $db->commit();
    }
}
