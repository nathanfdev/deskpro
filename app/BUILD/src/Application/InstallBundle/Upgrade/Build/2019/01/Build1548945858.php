<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1548945858 extends AbstractBuild implements OnlineBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
        $this->execDbQuery('default', 'ALTER TABLE voice_phone_calls ADD recording_deleted TINYINT(1) NOT NULL');
    }

    public function run()
    {
    }
}
