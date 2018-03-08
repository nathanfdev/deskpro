<?php

/*
 * Deskpro (r) has been developed by Deskpro Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, Deskpro Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that Deskpro is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing Deskpro since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team Deskpro
 */

namespace Application\InstallBundle\Upgrade\Build;

// we're going through all report_dashboard_permissions and delete dupes then create unique index
class Build1520511923 extends AbstractBuild implements OnlineBuildInterface
{
    /**
     * {@inheritdoc}
     */
    public function addNewTables()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function runAlters()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        $sql = <<<'SQL'
SELECT `id`, `dashboard_id`, `person_id`, `team_id`, `department_id`, `name` 
  FROM `report_dashboard_permission`
  ORDER BY `id` ASC
SQL;

        $uniquePermissions = [];
        $idsToDelete       = [];

        $permissions = $this->getDbConnection()->fetchAll($sql);
        foreach ($permissions as $permission) {
            $key = sprintf(
                '%d%s%s%s%s',
                $permission['dashboard_id'],
                $permission['person_id'] ?: 'none',
                $permission['team_id'] ?: 'none',
                $permission['department_id'] ?: 'none',
                $permission['name']
            );
            if (isset($uniquePermissions[$key])) {
                $idsToDelete[] = $permission['id'];
            } else {
                $uniquePermissions[$key] = $permission;
            }
        }

        if ($idsToDelete) {
            $idsToDelete = implode(',', $idsToDelete);
            $deleteSql   = <<<DELETESQL
DELETE FROM `report_dashboard_permission` WHERE `id` IN ({$idsToDelete})        
DELETESQL;
            $this->execDbQuery('default', $deleteSql);
        }
        $this->execDbQuery('default', 'ALTER TABLE `report_dashboard_permission` ADD UNIQUE INDEX `uniq_compose` (`name`, `department_id`, `team_id`, `person_id`, `dashboard_id`);');
    }
}
