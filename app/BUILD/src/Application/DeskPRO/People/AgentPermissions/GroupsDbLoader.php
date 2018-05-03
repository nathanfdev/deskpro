<?php

/**
 * DeskPRO.
 *
 * @category People
 */

namespace Application\DeskPRO\People\AgentPermissions;

use Application\DeskPRO\DBAL\Connection;
use Doctrine\ORM\EntityManager;

class GroupsDbLoader
{
    /**
     * @var int[]
     */
    private $group_ids;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @var \Application\DeskPRO\DBAL\Connection
     */
    private $db;

    /**
     * @var array
     */
    private $group_perms;

    protected $all_perms_group = 0;

    protected $all_safe_perms_group = 0;

    /**
     * @param int[]         $groups Group IDs or Usergroup objects
     * @param EntityManager $em
     */
    public function __construct(array $groups, EntityManager $em)
    {
        $this->group_ids = [];

        foreach ($groups as $g) {
            if (is_object($g)) {
                if ('agent_all_perms' === $g->sys_name) {
                    $this->all_perms_group = $g['id'];
                } elseif ('agent_all_safe_perms' === $g->sys_name) {
                    $this->all_safe_perms_group = $g['id'];
                } else {
                    $this->group_ids[] = $g->id;
                }
            } elseif (is_int($g) || ctype_digit($g)) {
                if (!$group = $em->find('DeskPRO:Usergroup', $g)) {
                    continue;
                }

                if ('agent_all_perms' === $group->sys_name) {
                    $this->all_perms_group = $group['id'];
                } elseif ('agent_all_safe_perms' === $group->sys_name) {
                    $this->all_safe_perms_group = $group['id'];
                } else {
                    $this->group_ids[] = (int) $g;
                }
            }
        }

        $this->em = $em;
        $this->db = $em->getConnection();
    }

    /**
     * @param int $group_id
     *
     * @return array
     */
    private function getPermissions($group_id)
    {
        $group_id = (int) $group_id;

        if (isset($this->group_perms[$group_id])) {
            return $this->group_perms[$group_id];
        } else {
            $this->group_perms[$group_id] = [];
        }

        $names_loader = new PermissionNamesLoader(); //TODO inject

        switch ($group_id) {
            case $this->all_perms_group:
                foreach ($names_loader->getNames() as $name) {
                    $this->group_perms[$group_id][$name] = true;
                }
                break;

            case $this->all_safe_perms_group:
                foreach ($names_loader->getSafeNames() as $name) {
                    $this->group_perms[$group_id][$name] = true;
                }
                break;

            default:
                $perm_recs = $this->db->fetchAll('
                    SELECT name, usergroup_id
                    FROM permissions
                    WHERE usergroup_id IN (?)
                        AND value = 1
                ', [$this->group_ids], [Connection::PARAM_INT_ARRAY]);

                foreach ($perm_recs as $rec) {
                    if (!isset($this->group_perms[$rec['usergroup_id']])) {
                        $this->group_perms[$rec['usergroup_id']] = [];
                    }

                    $this->group_perms[$rec['usergroup_id']][$rec['name']] = true;
                }

        }

        return $this->group_perms[$group_id];
    }

    /**
     * @param $group_id
     *
     * @return AgentPermissions
     */
    public function getGroupPermissions($group_id)
    {
        $perms = $this->getPermissions($group_id);

        return $this->createAgentPermissions($perms);
    }

    /**
     * @param array $perm_array
     *
     * @return AgentPermissions
     */
    private function createAgentPermissions(array $perm_array)
    {
        $agent_perms = new AgentPermissions();

        foreach ($perm_array as $k => $v) {
            if (!$v) {
                continue;
            } // disabled
            if (strpos($k, '.') === false) {
                continue;
            } // invalid

            list($type, $name) = explode('.', $k, 2);
            if (!isset(AgentPermissions::$prefix_map[$type])) {
                continue;
            } // unknown type

            $obj_name = AgentPermissions::$prefix_map[$type];
            $obj      = $agent_perms->$obj_name;
            if (!isset($obj->$name)) {
                continue;
            } // invalid;

            $obj->$name = true;
        }

        return $agent_perms;
    }
}
