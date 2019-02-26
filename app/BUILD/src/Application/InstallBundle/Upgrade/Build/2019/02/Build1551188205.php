<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1551188205 extends AbstractBuild implements OnlineBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
        $this->execDbQuery('default', 'ALTER TABLE voice_phone_call_participants ADD member_id VARCHAR(50) DEFAULT NULL');
        $this->execDbQuery('default', 'ALTER TABLE voice_phone_call_participants ADD on_hold TINYINT(1) NOT NULL');
    }

    public function run()
    {
    }
}
