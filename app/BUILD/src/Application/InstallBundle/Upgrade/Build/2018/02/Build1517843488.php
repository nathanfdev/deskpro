<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1517843488 extends AbstractBuild implements BlockingBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
        $this->execDbQuery('default', 'ALTER TABLE report_dashboard_widget CHANGE position position VARCHAR(255) NOT NULL, CHANGE size size VARCHAR(255) NOT NULL, CHANGE variables variables LONGTEXT');
    }

    public function run()
    {
    }
}
