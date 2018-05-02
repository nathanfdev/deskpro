<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1525268025 extends AbstractBuild implements OnlineBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
        $this->execDbQuery('default', 'ALTER TABLE agent_data CHANGE forwarding_number_type forwarding_number_type VARCHAR(50) DEFAULT NULL');
        $this->execDbQuery('default', 'ALTER TABLE voice_phone_calls CHANGE external_number_type external_number_type VARCHAR(50) NOT NULL');
    }

    public function run()
    {
    }
}
