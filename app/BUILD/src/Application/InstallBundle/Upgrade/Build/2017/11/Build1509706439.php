<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1509706439 extends AbstractBuild implements OnlineBuildInterface, SkipPostBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
        $this->execSlowAlterTable('blobs', 'CHANGE filename filename VARCHAR(255) NOT NULL');
    }

    public function run()
    {
    }
}
