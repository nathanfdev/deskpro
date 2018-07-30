<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1532965730 extends AbstractBuild implements OnlineBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
        $this->execDbQuery('default', 'ALTER TABLE voice_queues ADD recording_enabled TINYINT(1) NOT NULL');
    }

    public function run()
    {
        $this->execDbQuery('default', 'UPDATE `voice_queues` SET recording_enabled = 1');
    }
}
