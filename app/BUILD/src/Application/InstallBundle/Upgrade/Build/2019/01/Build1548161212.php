<?php

namespace Application\InstallBundle\Upgrade\Build;

use DeskPRO\Component\Lock\PdoStore;

class Build1548161212 extends AbstractBuild implements OnlineBuildInterface, SkipPostBuildInterface
{
    public function addNewTables()
    {
        $store = new PdoStore($this->getDbConnection('default'));
        $store->createTable();
    }

    public function runAlters()
    {
    }

    public function run()
    {
    }
}
