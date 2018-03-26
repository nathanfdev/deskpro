<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1465209612 extends AbstractBuild
{
    public function run()
    {
        $connection = $this->getDbConnection('default');

        $this->out('Removing old feedbacks with invalid hidden status (e.g. spam)');
        $updateSQL = <<<'SQL'
        DELETE FROM feedback
        WHERE status = 'hidden' AND hidden_status != 'validating'
SQL;
        $connection->exec($updateSQL);

        $this->out('Fixing feedback statuses based on status_category');
        $updateSQL = <<<'SQL'
        UPDATE  `feedback` AS `f` 
        INNER JOIN `feedback_status_categories` AS `fsc` 
        ON `f`.`status_category_id` = `fsc`.`id` 
        SET `f`.`status` = `fsc`.`status_type`, `f`.`hidden_status` = null
SQL;
        $connection->exec($updateSQL);
    }
}
