<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1565349384 extends AbstractBuild implements OnlineBuildInterface, SkipPostBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
        $this->execDbQuery('default', 'ALTER TABLE object_lang ADD input LONGTEXT NOT NULL');
    }

    public function run()
    {
    }
}
