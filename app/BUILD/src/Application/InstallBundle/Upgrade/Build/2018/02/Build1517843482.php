<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1517843482 extends AbstractBuild implements OnlineBuildInterface, SkipPostBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
        $this->execDbQuery('default', 'ALTER TABLE report_dashboard_widget ADD options LONGTEXT DEFAULT NULL');
    }

    public function run()
    {
    }
}
