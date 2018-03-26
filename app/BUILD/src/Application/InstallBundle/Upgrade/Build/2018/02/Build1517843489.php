<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1517843489 extends AbstractBuild implements BlockingBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
        $this->execDbQuery('default', 'ALTER TABLE agent_data ADD can_use_forwarding TINYINT(1) NOT NULL, ADD agent_can_use_forwarding TINYINT(1) NOT NULL, ADD forwarding_number VARCHAR(50) NULL');
        $this->execDbQuery('default', 'ALTER TABLE voice_phone_calls ADD forwarding_sids LONGTEXT NOT NULL COMMENT \'(DC2Type:json_array)\'');
    }

    public function run()
    {
    }
}
