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
 * @subpackage
 */

namespace Application\InstallBundle\Upgrade\Build;

use Application\DeskPRO\People\AgentPermissions\PermissionNamesLoader;
use Doctrine\DBAL\Connection;

class Build1418912765 extends AbstractBuild
{
    public function run()
    {
        $this->out("Remove extra override permissions");
        $db = $this->container->getDb();

        #------------------------------
        # Load up data
        #------------------------------

        $agent_ids    = $db->fetchAllCol("SELECT id FROM people WHERE is_agent = 1");
        $group_ids    = $db->fetchAllCol("SELECT id FROM usergroups WHERE is_agent_group = 1");

        // agent_to_group[agent_id][usergroup_id] = truthy
        $agent_to_group = $db->fetchAllGrouped("
            SELECT person_id, usergroup_id
            FROM person2usergroups
            WHERE person_id IN (?) AND usergroup_id IN (?)
        ", array($agent_ids, $group_ids), 'person_id', 'usergroup_id', 'usergroup_id', array(Connection::PARAM_INT_ARRAY, Connection::PARAM_INT_ARRAY));

        // agent_perms[agent_id][perm_name] = record id (used to delete)
        $agent_perms  = $db->fetchAllGrouped("
          SELECT person_id, name, id
          FROM permissions
          WHERE person_id IN (?)
        ", array($agent_ids), 'person_id', 'name', 'id', array(Connection::PARAM_INT_ARRAY));

        // group_perms[usergroup_id][perm_name] = truthy
        $group_perms  = $db->fetchAllGrouped("
          SELECT usergroup_id, name, id
          FROM permissions
          WHERE usergroup_id IN (?)
        ", array($group_ids), 'usergroup_id', 'name', 'id', array(Connection::PARAM_INT_ARRAY));

        $special_ids = $db->fetchAllKeyValue("SELECT sys_name, id FROM usergroups WHERE is_agent_group = 1 AND sys_name IS NOT NULL");
        $name_loader = new PermissionNamesLoader();
        foreach ($special_ids as $name => $id) {
            $perms = $name_loader->getEnabledForGroup($name);
            if ($perms) {
                $perms = array_fill_keys($perms, 1);
                if (!isset($group_perms[$id])) {
                    $group_perms[$id] = array();
                }
                $group_perms[$id] = array_merge($group_perms[$id], $perms);
            }
        }

        #------------------------------
        # Figure out superfluous recs
        #------------------------------

        $remove_ids = array();

        foreach ($agent_ids as $agent_id) {
            if (empty($agent_perms[$agent_id]) || empty($agent_to_group[$agent_id])) {
                continue;
            }

            foreach ($agent_perms[$agent_id] as $perm_name => $perm_id) {
                foreach ($agent_to_group[$agent_id] as $usergroup_id => $x) {
                    if (isset($group_perms[$usergroup_id][$perm_name])) {
                        $remove_ids[] = $perm_id;
                    }
                }
            }
        }

        if ($remove_ids) {
            $db->deleteIn("permissions", $remove_ids, 'id');
            $this->out(sprintf("Cleaned up %d records", count($remove_ids)));
        }
    }
}