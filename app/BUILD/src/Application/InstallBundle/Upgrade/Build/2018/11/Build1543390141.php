<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1543390141 extends AbstractBuild implements BlockingBuildInterface, SkipPostBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
        $this->execDbQuery('default', 'DROP INDEX status_idx ON tickets');
        $this->execDbQuery('default', 'ALTER TABLE tickets ADD ticket_status_id INT DEFAULT NULL AFTER status');
        $this->execDbQuery('default', 'ALTER TABLE tickets ADD CONSTRAINT FK_54469DF4F1CDDAF7 FOREIGN KEY (ticket_status_id) REFERENCES ticket_statuses (id) ON DELETE SET NULL');
        $this->execDbQuery('default', 'CREATE INDEX IDX_54469DF4F1CDDAF7 ON tickets (ticket_status_id)');
        $this->execDbQuery('default', 'CREATE INDEX status_idx ON tickets (status, ticket_status_id)');
    }

    public function run()
    {
    }
}
