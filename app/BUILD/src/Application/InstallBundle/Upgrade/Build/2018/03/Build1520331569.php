<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1520331569 extends AbstractBuild implements OnlineBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
        $this->execDbQuery('default', 'ALTER TABLE report_dashboard_widget ADD js_code LONGTEXT DEFAULT NULL');
    }

    public function run()
    {
    }
}
