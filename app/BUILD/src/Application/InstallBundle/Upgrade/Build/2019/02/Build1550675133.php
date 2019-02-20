<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1550675133 extends AbstractBuild implements OnlineBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
        $this->execDbQuery('default', 'ALTER TABLE voice_phone_call_participants ADD cost VARCHAR(255) DEFAULT NULL, ADD cost_currency VARCHAR(255) DEFAULT NULL');
        $this->execDbQuery('default', 'ALTER TABLE voice_phone_calls ADD cost VARCHAR(255) DEFAULT NULL, ADD cost_currency VARCHAR(255) DEFAULT NULL');
    }

    public function run()
    {
    }
}
