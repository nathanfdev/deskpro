<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1534014524 extends AbstractBuild implements OnlineBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
        $this->execDbQuery('default', 'ALTER TABLE voice_queues ADD recording_enabled TINYINT(1) NOT NULL');
        $this->execDbQuery('default', 'ALTER TABLE voice_numbers ADD outbound_calls_default TINYINT(1) NOT NULL, ADD outbound_calls_default_global TINYINT(1) NOT NULL, ADD outbound_calls_default_countries LONGTEXT NOT NULL COMMENT \'(DC2Type:json_array)\' NOT NULL');
    }

    public function run()
    {
        $this->execDbQuery('default', 'UPDATE `voice_queues` SET recording_enabled = 1');
    }
}
