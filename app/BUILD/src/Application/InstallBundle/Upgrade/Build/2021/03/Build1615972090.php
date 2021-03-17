<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1615972090 extends AbstractBuild implements OnlineBuildInterface, SkipPostBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
    }

    public function run()
    {
        $this->execDbQuery('default', "UPDATE languages SET base_filepath = '%DP_ROOT%/locales/ro', locale = 'ro' WHERE sys_name = 'romanian'");
    }
}
