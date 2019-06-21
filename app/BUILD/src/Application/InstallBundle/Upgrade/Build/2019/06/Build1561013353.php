<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1561013353 extends AbstractBuild implements OnlineBuildInterface, SkipPostBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
        $this->execDbQuery('default', 'CREATE UNIQUE INDEX UNIQ_6F9D0D3D539B0606 ON email_sources (uid)');
    }

    public function run()
    {
    }
}
