<?php

namespace Application\DeskPRO\Groups;

use Application\DeskPRO\DBAL\Connection;
use Application\DeskPRO\Entity\Permission;
use Application\DeskPRO\Entity\Usergroup;

/**
 * Class PermissionsLoader.
 */
class PermissionsLoader
{
    /**
     * @var \Application\DeskPRO\DBAL\Connection
     */
    private $db;

    /**
     * @var array
     */
    private $ug_perms;

    /**
     * @var array
     */
    private $agent_override_perms;

    /**
     * @var array
     */
    private $effectivePermissions = [];

    /**
     * @param Connection $db
     */
    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    /**
     * @return array
     */
    public function getAllPermissions()
    {
        if ($this->ug_perms !== null) {
            return $this->ug_perms;
        }

        $this->ug_perms = $this->db->fetchAllGrouped('
            SELECT usergroup_id, name, value
            FROM permissions
            WHERE person_id IS NULL
        ', [], 'usergroup_id');

        return $this->ug_perms;
    }

    /**
     * @param array $usergroupIds
     *
     * @return array
     */
    public function getUsergroupPermissions(array $usergroupIds)
    {
        $usergroupIds   = array_fill_keys($usergroupIds, true);
        $allPermissions = $this->getAllPermissions();

        $ret = [];
        foreach ($allPermissions as $ugid => $p) {
            if (isset($usergroupIds[$ugid])) {
                $ret[$ugid] = $p;
            }
        }

        return $ret;
    }

    /**
     * @return array
     */
    public function getAllAgentOverridePermissions()
    {
        if ($this->agent_override_perms !== null) {
            return $this->agent_override_perms;
        }

        $this->agent_override_perms = $this->db->fetchAllGrouped('
            SELECT person_id, name, value
            FROM permissions
            WHERE person_id IS NOT NULL AND is_active = 1
        ', [], 'person_id');

        return $this->agent_override_perms;
    }

    /**
     * @param int $agent_id
     *
     * @return array
     */
    public function getAgentOverridePermissions($agent_id)
    {
        $this->getAllAgentOverridePermissions();
        if (!isset($this->agent_override_perms[$agent_id])) {
            return [];
        }

        return $this->agent_override_perms[$agent_id];
    }

    /**
     * @param array $uIds
     *
     * @return array
     */
    public function getEffectivePermissionsForUsergroups(array $uIds)
    {
        $uKey = Usergroup::generateUsergroupSetKey($uIds);
        if (!isset($this->effectivePermissions[$uKey])) {
            $permissions = $this->getUsergroupPermissions($uIds);
            $permResult  = [];
            foreach ($permissions as $permissionGroup) {
                foreach ($permissionGroup as $p) {
                    $permResult[] = $p;
                }
            }

            $this->effectivePermissions[$uKey] = Permission::getEffectivePermissions($permResult);
        }

        return $this->effectivePermissions[$uKey];
    }
}
