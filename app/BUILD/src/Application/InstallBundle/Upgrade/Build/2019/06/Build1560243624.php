<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1560243624 extends AbstractBuild implements OnlineBuildInterface, SkipPostBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
        $this->execDbQuery('default', 'ALTER TABLE custom_def_people ADD is_public TINYINT(1) NOT NULL');
    }

    public function run()
    {
    }
}
