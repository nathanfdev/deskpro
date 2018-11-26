<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1539025026 extends AbstractBuild implements BlockingBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
        $this->execDbQuery('default', 'ALTER TABLE notify_action_alerts CHANGE target_id target_id VARCHAR(200) NOT NULL');
    }

    public function run()
    {
    }
}
