<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1586516059 extends AbstractBuild implements OnlineBuildInterface, SkipPostBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
        $this->execDbQuery('voice', 'ALTER TABLE voice_workers ADD voice_date_last_active DATETIME DEFAULT NULL');
    }

    public function run()
    {
    }
}
