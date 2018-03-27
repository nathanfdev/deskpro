<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\People;

use Application\DeskPRO\App;
use Application\DeskPRO\DBAL\Connection;
use Application\DeskPRO\Entity\Person;

class Util
{
    private function __construct()
    {
    }

    /**
     * Takes a full name and an email address and tries to parse out a first and last name.
     *
     * @param string|null $full_name
     * @param string|null $email_address
     *
     * @return array
     */
    public static function guessNameParts($full_name = null, $email_address = null)
    {
        if (!$full_name && !$email_address) {
            return ['', ''];
        }

        $first_name = null;
        $last_name  = null;

        if ($full_name && strpos($full_name, ' ') !== false) {
            list($first_name, $last_name) = explode(' ', $full_name, 2);
        } elseif ($email_address) {
            list($email_name) = explode('@', $email_address, 2);
            if (strpos($email_name, '.') !== false) {
                list($first_name, $last_name) = explode('.', $email_name, 2);
                $first_name                   = ucfirst($first_name);
                $last_name                    = ucfirst($last_name);
            }
        }

        // Just set the first name to whatever we might have, its the best we can do
        if (!$first_name && !$last_name) {
            if ($full_name) {
                $first_name = $full_name;
            } elseif ($email_address) {
                list($email_name) = explode('@', $email_address, 2);
                $first_name       = ucfirst($email_name);
            }
        }

        return [
            $first_name,
            $last_name,
        ];
    }

    /**
     * Given an array of permissions, return the overrides that matter. E.g., if a ug has a permission on,
     * make sure its not recorded as an override. Basically boiling down the set of overrides to those
     * that are actually overrides.
     *
     * $ug_perm_matrix looks like:
     * <code>
     * array(
     *     'agent_tickets' => array(
     *         'override' => array( 'use' => 0, 'xxx' => 1),
     *         1 => array( 'use' => 0, 'xxx' => 1),
     *         ...
     *     )
     * )
     * </code>
     *
     * @param array $ug_perm_matrix
     *
     * @return array
     */
    public static function resolveOverridePermissions(array $ug_perm_matrix, array $usergroups, array $all_ug_perms)
    {
        $grouped_perms = [
             'agent_tickets.modify_own'        => '#^agent_tickets.modify_(.*?)_own$#',
             'agent_tickets.modify_followed'   => '#^agent_tickets.modify_(.*?)_followed$#',
             'agent_tickets.modify_unassigned' => '#^agent_tickets.modify_(.*?)_unassigned$#',
             'agent_tickets.modify_others'     => '#^agent_tickets.modify_(.*?)_others$#',
        ];

        $overrides = [];
        foreach ($ug_perm_matrix as $group => $ug_perms) {
            foreach ([0, 1] as $run_num) {
                foreach ($ug_perms as $ug_id => $perms) {
                    // Not one we enabled so we dont care
                    if ($ug_id != 'override' && !isset($usergroups[$ug_id])) {
                        continue;
                    }

                    foreach ($perms as $perm => $v) {
                        if (!$v) {
                            continue; //dont care about non 1's
                        }

                        $perm_name = "{$group}.{$perm}";
                        $is_sub    = false;

                        // If this is a sub-permission and the ug has the parent on,
                        // then this is on too
                        foreach ($grouped_perms as $parent_perm => $pattern) {
                            if (preg_match($pattern, $perm_name)) {
                                $is_sub = $parent_perm;
                            }
                        }

                        if ($is_sub) {
                            // First time around we're just building parents
                            if ($run_num == 0) {
                                continue;

                            // Second time around we're doing sub-perms
                            } else {
                                // Granted by usergroup permission
                                if (isset($all_ug_perms[$perm_name]) && $all_ug_perms[$perm_name]) {
                                    continue;

                                // Granted by parent permission of our own override
                                } elseif (isset($overrides[$perm_name]) && $overrides[$perm_name]) {
                                    continue;
                                }
                            }
                        } else {
                            // Granted by usergroup permission
                            if (isset($all_ug_perms[$perm_name]) && $all_ug_perms[$perm_name]) {
                                continue;
                            }
                        }

                        if ($ug_id == 'override') {
                            $overrides[$perm_name] = 1;
                        }
                    }
                }
            }
        }

        return $overrides;
    }

    /**
     * Just like resolveOverridePermissions but runs with current permissions on an agent.
     *
     * @param Person $agent
     *
     * @return array
     */
    public static function resolveOverridePermissionsForAgent(Person $agent)
    {
        $db = App::getDb();
        $em = App::getOrm();

        $ug_ids = $db->fetchAllCol('SELECT usergroup_id FROM person2usergroups WHERE person_id = ?', [$agent->id]);

        if ($ug_ids) {
            $usergroups   = $em->getRepository('DeskPRO:Usergroup')->getByIds($ug_ids);
            $all_ug_perms = $db->fetchAllKeyValue('
                SELECT name, value
                FROM permissions
                WHERE usergroup_id IN (?)
                ORDER BY value DESC
            ', [$ug_ids], [Connection::PARAM_INT_ARRAY]);

            $ug_perms = $db->fetchAllGrouped('
                SELECT usergroup_id, name, value
                FROM permissions
                LEFT JOIN usergroups ON (usergroups.id = permissions.id)
                WHERE usergroup_id IN (?)
            ', [$ug_ids], 'usergroup_id', 'name', 'value', [Connection::PARAM_INT_ARRAY]);
        } else {
            $all_ug_perms = [];
            $usergroups   = [];
            $ug_perms     = [];
        }

        $override_perms = $db->fetchAllKeyValue('
            SELECT name, value
            FROM permissions
            WHERE person_id = ?
        ', [$agent['id']]);

        $ug_perm_matrix = [];

        foreach ($ug_perms as $ug_id => $perms) {
            foreach ($perms as $perm_name => $perm_val) {
                list($perm_group, $perm_endname) = explode('.', $perm_name, 2);

                if (!isset($ug_perm_matrix[$perm_group])) {
                    $ug_perm_matrix[$perm_group] = [];
                }
                if (!isset($ug_perm_matrix[$perm_group][$ug_id])) {
                    $ug_perm_matrix[$perm_group][$ug_id] = [];
                }
                $ug_perm_matrix[$perm_group][$ug_id][$perm_endname] = $perm_val;
            }
        }

        foreach ($override_perms as $perm_name => $perm_val) {
            $ug_id                           = 'override';
            list($perm_group, $perm_endname) = explode('.', $perm_name, 2);

            if (!isset($ug_perm_matrix[$perm_group])) {
                $ug_perm_matrix[$perm_group] = [];
            }
            if (!isset($ug_perm_matrix[$perm_group][$ug_id])) {
                $ug_perm_matrix[$perm_group][$ug_id] = [];
            }
            $ug_perm_matrix[$perm_group][$ug_id][$perm_endname] = $perm_val;
        }

        $overrides = self::resolveOverridePermissions($ug_perm_matrix, $usergroups, $all_ug_perms);

        return $overrides;
    }
}
