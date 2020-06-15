<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1592221615 extends AbstractBuild implements OnlineBuildInterface, SkipPostBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
    }

    public function run()
    {
        $this->execDbQueryQuiet('default', "UPDATE blobs SET is_temp = 0");
    }
}
