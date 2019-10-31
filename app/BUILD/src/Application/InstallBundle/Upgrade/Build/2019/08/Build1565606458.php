<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1565606458 extends AbstractBuild implements OnlineBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
        $this->execDbQuery('voice', 'ALTER TABLE voice_tasks ADD date_expire DATETIME DEFAULT NULL, DROP timeout');
    }

    public function run()
    {
    }
}
