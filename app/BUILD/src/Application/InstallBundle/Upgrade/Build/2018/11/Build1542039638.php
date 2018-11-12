<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1542039638 extends AbstractBuild implements OnlineBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
        $this->execDbQuery('default', 'ALTER TABLE voice_queues DROP FOREIGN KEY FK_80C86EA9B6B5FBA');
        $this->execDbQuery('default', 'DROP INDEX IDX_80C86EA9B6B5FBA ON voice_queues');
        $this->execDbQuery('default', 'ALTER TABLE voice_queues DROP account_id');
    }

    public function run()
    {
    }
}
