<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1568044083 extends AbstractBuild implements OnlineBuildInterface, SkipPostBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
        $this->execDbQuery('default', 'ALTER TABLE guides ADD description TEXT DEFAULT NULL');
    }

    public function run()
    {
    }
}
