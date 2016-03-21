<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

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

        #----------------------------------------
        # Get agent group data
        #----------------------------------------

        $ag_ids = $db->fetchAllCol('
            SELECT person2usergroups.usergroup_id
            FROM person2usergroups
            LEFT JOIN usergroups ON (usergroups.id = person2usergroups.usergroup_id)
            WHERE usergroups.is_agent_group = 1
              AND person2usergroups.person_id = ?;
        ', array($person->getId()));

        if (!$ag_ids) {
            $ag_ids = array(0);
        }

        $special_ag = $db->fetchAllKeyValue("
            SELECT sys_name, id
            FROM usergroups
            WHERE sys_name IN ('agent_all_perms', 'agent_all_safe_perms')
        ");

        #----------------------------------------
        # Figure out which permissions are granted via a usergroup
        #----------------------------------------

        if ($ag_perms_cache !== null) {
            $ag_perms_names = array();
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
            ', array($ag_ids), array(Connection::PARAM_INT_ARRAY));
        }

        if (in_array($special_ag['agent_all_perms'], $ag_ids)) {
            $loader         = App::$container->getSystemService('AgentPermissionNamesLoader');
            $ag_perms_names = array_merge($ag_perms_names, $loader->getNames());
        } elseif (in_array($special_ag['agent_all_safe_perms'], $ag_ids)) {
            $loader         = App::$container->getSystemService('AgentPermissionNamesLoader');
            $ag_perms_names = array_merge($ag_perms_names, $loader->getSafeNames());
        }

        #----------------------------------------
        # Figure out which department permissions are granted via a usergroup
        #----------------------------------------

        if (in_array($special_ag['agent_all_perms'], $ag_ids) || in_array($special_ag['agent_all_safe_perms'], $ag_ids)) {
            $dep_id_full_perms   = $db->fetchAllCol('SELECT id FROM departments');
            $dep_id_assign_perms = array();
        } else {
            if ($ag_dep_perms_cache !== null) {
                $dep_id_full_perms   = array();
                $dep_id_assign_perms = array();

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
                    SELECT department_id
                    FROM department_permissions
                    WHERE name = 'full'
                      AND usergroup_id IN (?)
                ", array($ag_ids), array(Connection::PARAM_INT_ARRAY));

                $dep_id_assign_perms = $db->fetchAllCol("
                    SELECT department_id
                    FROM department_permissions
                    WHERE name = 'assign'
                      AND usergroup_id IN (?)
                ", array($ag_ids), array(Connection::PARAM_INT_ARRAY));
            }
        }

        #----------------------------------------
        # De-activate all perms that match ones granted via perms
        #----------------------------------------

        $db->beginTransaction();

        // All perms are active until we specifically turn them off
        $db->update('permissions', array('is_active' => 1), array('person_id' => $person->getId()));
        $db->update('department_permissions', array('is_active' => 1), array('person_id' => $person->getId()));

        if ($ag_perms_names) {
            $db->executeUpdate('
                UPDATE permissions
                SET is_active = 0
                WHERE person_id = ? AND name IN (?)
            ', array($person->getId(), $ag_perms_names), array(\PDO::PARAM_INT, Connection::PARAM_STR_ARRAY));
        }

        if ($dep_id_full_perms) {
            $db->executeUpdate('
                UPDATE department_permissions
                SET is_active = 0
                WHERE person_id = ? AND department_id IN (?)
            ', array($person->getId(), $dep_id_full_perms), array(\PDO::PARAM_INT, Connection::PARAM_INT_ARRAY));
        }

        if ($dep_id_assign_perms) {
            $db->executeUpdate("
                UPDATE department_permissions
                SET is_active = 0
                WHERE is_active = 1 AND person_id = ? AND department_id IN (?) AND name = 'assign'
            ", array($person->getId(), $dep_id_assign_perms), array(\PDO::PARAM_INT, Connection::PARAM_INT_ARRAY));
        }

        App::getDb()->exec('DELETE FROM permissions_cache');

        $db->commit();
    }
}
