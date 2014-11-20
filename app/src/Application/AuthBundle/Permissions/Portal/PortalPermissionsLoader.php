<?php
/**************************************************************************\
 * | DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
 * | a British company located in London, England.                            |
 * |                                                                          |
 * | All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
 * |                                                                          |
 * | The license agreement under which this software is released              |
 * | can be found at http://www.deskpro.com/license                           |
 * |                                                                          |
 * | By using this software, you acknowledge having read the license          |
 * | and agree to be bound thereby.                                           |
 * |                                                                          |
 * | Please note that DeskPRO is not free software. We release the full       |
 * | source code for our software because we trust our users to pay us for    |
 * | the huge investment in time and energy that has gone into both creating  |
 * | this software and supporting our customers. By providing the source code |
 * | we preserve our customers' ability to modify, audit and learn from our   |
 * | work. We have been developing DeskPRO since 2001, please help us make it |
 * | another decade.                                                          |
 * |                                                                          |
 * | Like the work you see? Think you could make it better? We are always     |
 * | looking for great developers to join us: http://www.deskpro.com/jobs/    |
 * |                                                                          |
 * | ~ Thanks, Everyone at Team DeskPRO                                       |
 * \**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage
 */

namespace Application\AuthBundle\Permissions\Portal;


use Application\DeskPRO\DBAL\Connection;
use Application\DeskPRO\Entity\Permission;

class PortalPermissionsLoader
{
    /**
     * @var \Application\DeskPRO\DBAL\Connection
     */
    private $conn;

    /**
     * @var array
     */
    protected $permissions;

    public function __construct(Connection $connection)
    {
        $this->conn = $connection;
    }

    public function loadPermissionsForGroupSet(array $usergroupIds)
    {
        $perms = $this->getUsergroupsPermissions($usergroupIds);

        $result = array();
        foreach ($perms as $permissionGroup) {
            foreach ($permissionGroup as $p) {
                $result[] = $p;
            }
        }

        return Permission::getEffectivePermissions($result);
    }

    protected function getAllPermissions()
    {
        if ($this->permissions !== null) {
            return $this->permissions;
        }

        $this->permissions = $this->conn->fetchAllGrouped(
            '
            SELECT usergroup_id, name, value
            FROM permissions
            WHERE person_id IS NULL
            ',
            array(),
            'usergroup_id'
        );

        return $this->permissions;
    }

    protected function getUsergroupsPermissions($usergroupIds)
    {
        $usergroupIds = array_fill_keys($usergroupIds, true);

        $result = array();
        foreach ($this->getAllPermissions() as $id => $permission) {
            if (isset($usergroupIds[$id])) {
                $result[$id] = $permission;
            }
        }

        return $result;
    }
}