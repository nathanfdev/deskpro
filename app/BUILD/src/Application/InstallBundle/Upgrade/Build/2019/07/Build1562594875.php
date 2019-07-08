<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1562594875 extends AbstractBuild implements OnlineBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
        $this->execDbQuery('default', 'ALTER TABLE voice_phone_calls ADD date_waiting DATETIME DEFAULT NULL');
    }

    public function run()
    {
    }
}
