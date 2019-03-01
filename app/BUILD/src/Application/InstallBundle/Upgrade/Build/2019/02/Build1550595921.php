<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1550595921 extends AbstractBuild implements OnlineBuildInterface, SkipPostBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
        $sql = <<<'EOS'
ADD ticket_status_id INT DEFAULT NULL AFTER status,
ADD CONSTRAINT FK_54469DF4F1CDDAF7 FOREIGN KEY (ticket_status_id) REFERENCES ticket_statuses (id) ON DELETE SET NULL,
ADD INDEX IDX_54469DF4F1CDDAF7 (ticket_status_id),
DROP INDEX status_idx,
ADD INDEX status_idx (status, ticket_status_id)
EOS;

        $this->execSlowAlterTable('tickets', $sql);
    }

    public function run()
    {
    }
}
