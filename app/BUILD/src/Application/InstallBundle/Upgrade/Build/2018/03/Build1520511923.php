<?php

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
