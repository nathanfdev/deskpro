<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1517843492 extends AbstractBuild implements BlockingBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
        $this->execDbQuery('default', 'ALTER TABLE report_dashboard ADD system_name VARCHAR(255) DEFAULT NULL');
    }

    public function run()
    {
    }
}
