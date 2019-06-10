<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1560158534 extends AbstractBuild implements OnlineBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
        $this->execDbQuery('voice', 'ALTER TABLE voice_workers ADD last_call_at DATETIME DEFAULT NULL');
    }

    public function run()
    {
    }
}
