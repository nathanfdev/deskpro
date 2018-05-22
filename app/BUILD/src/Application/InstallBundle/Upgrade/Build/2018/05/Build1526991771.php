<?php

namespace Application\InstallBundle\Upgrade\Build;

use Application\DeskPRO\DBAL\Connection;

class Build1526991771 extends AbstractBuild implements OnlineBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
    }

    public function run()
    {
        $connection = $this->getDbConnection();

        $find = <<<'FIND'
SELECT `p`.`id` FROM `people` AS `p`
WHERE `p`.`can_reports` = 1 OR `p`.`can_admin` = 1
FIND;
        $agentIds = $connection->fetchAllCol($find);

        $find = <<<'FIND'
SELECT `d`.`id` FROM `report_dashboard` AS `d`
WHERE `d`.`system_name` IS NOT NULL
FIND;

        $dashboardIds = $connection->fetchAllCol($find);

        $sql = <<<'UPDATE'
UPDATE `report_dashboard_permission` as `rdp`
SET `rdp`.`name` = 'full'
WHERE `rdp`.`person_id` IN (?)
  AND `rdp`.`dashboard_id` IN (?)      
UPDATE;

        $connection->executeQuery($sql, [$agentIds, $dashboardIds], [Connection::PARAM_INT_ARRAY, Connection::PARAM_INT_ARRAY]);

        $sql = <<<'DELETE'
DELETE FROM `report_dashboard_permission`
WHERE `person_id` NOT IN (?)
  AND `dashboard_id` IN (?)
  AND collate  `name` = 'view'      
DELETE;

        $connection->executeQuery($sql, [$agentIds, $dashboardIds], [Connection::PARAM_INT_ARRAY, Connection::PARAM_INT_ARRAY]);
    }
}
