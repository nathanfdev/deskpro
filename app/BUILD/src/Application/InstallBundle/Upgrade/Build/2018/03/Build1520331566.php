<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1520331566 extends AbstractBuild implements BlockingBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
        $this->execDbQuery('default', 'ALTER TABLE agent_data ADD forwarding_number_type VARCHAR(50) NOT NULL');
        $this->execDbQuery('default', 'ALTER TABLE voice_phone_calls ADD external_number_type VARCHAR(50) NULL');
    }

    public function run()
    {
        $this->execDbQuery('default', "UPDATE agent_data SET forwarding_number_type = 'phone'");
        $this->execDbQuery('default', "UPDATE voice_phone_calls SET external_number_type = 'phone' WHERE external_number IS NOT NULL AND external_number != ''");
    }
}
