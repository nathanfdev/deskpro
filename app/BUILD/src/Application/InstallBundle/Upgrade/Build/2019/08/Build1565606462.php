<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1565606462 extends AbstractBuild implements OnlineBuildInterface, SkipPostBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
        $this->execDbQuery('default', 'ALTER TABLE object_lang ADD input LONGTEXT DEFAULT NULL');
    }

    public function run()
    {
    }
}
