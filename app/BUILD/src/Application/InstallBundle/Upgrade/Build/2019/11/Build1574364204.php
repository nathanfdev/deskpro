<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1574364204 extends AbstractBuild implements OnlineBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
    }

    public function run()
    {
        $this->execDbQuery('default', 'UPDATE topics SET no_content = 1 WHERE parent_id IS NULL');
    }
}
