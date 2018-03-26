<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1517843491 extends AbstractBuild implements BlockingBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
        $this->execDbQuery('default', 'ALTER TABLE report_dashboard ADD is_agent TINYINT(1) NOT NULL');
        $this->execDbQuery('default', 'ALTER TABLE report_dashboard_permission ADD view_all TINYINT(1) NOT NULL');
    }

    public function run()
    {
    }
}
