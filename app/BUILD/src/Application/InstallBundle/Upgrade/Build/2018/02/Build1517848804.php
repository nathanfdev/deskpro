<?php

namespace Application\InstallBundle\Upgrade\Build;

/**
 * Class Build1517848804.
 */
class Build1517848804 extends AbstractBuild implements OnlineBuildInterface, SkipPostBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
        $this->execSlowAlterTable('blobs_auth_moved', 'CHANGE filename filename VARCHAR(255) NOT NULL');
    }

    public function run()
    {
    }
}
