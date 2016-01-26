<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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
namespace DpBehat;

/**
 * Class PermissionContext.
 */
class PermissionContext extends BaseContext
{
    /**
     * @Given I set permission :permission_name = :value for :sys_name usergroup
     *
     * @param string $permission_name
     * @param string $value
     * @param string $sys_name
     */
    public function iSetUserGroupPermission($permission_name, $value, $sys_name)
    {
        $connection = $this->em()->getConnection();
        $group_ids  = $connection->fetchAllCol('SELECT id FROM usergroups WHERE sys_name = ?', [$sys_name]);

        foreach ($group_ids as $gid) {
            $connection->executeUpdate(
                'DELETE FROM permissions WHERE usergroup_id = ? AND name = ?',
                [$gid, $permission_name]
            );
            $connection->executeUpdate(
                'INSERT INTO permissions SET usergroup_id = ?, name = ?, value = ?',
                [$gid, $permission_name, $value]
            );
        }

        $connection->executeUpdate('DELETE FROM permissions_cache');
    }
}
