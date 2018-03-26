<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1518434839 extends AbstractBuild implements OnlineBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
        $this->execDbQuery('default', 'ALTER TABLE report_dashboard ADD display_order INT DEFAULT 0 NOT NULL');
    }

    public function run()
    {
    }
}
