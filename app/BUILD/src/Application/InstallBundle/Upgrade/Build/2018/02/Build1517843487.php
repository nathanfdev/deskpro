<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1517843487 extends AbstractBuild implements BlockingBuildInterface
{
    public function addNewTables()
    {
    }

    /**
     * @throws \Exception
     */
    public function runAlters()
    {
        $this->execDbQuery('default', 'ALTER TABLE report_dashboard_widget DROP hc_data');
        $this->execDbQuery('default', 'ALTER TABLE report_dashboard_report DROP `columns`');
        $this->execDbQuery('default', 'ALTER TABLE saved_dashboard_report DROP `columns`');
    }

    public function run()
    {
    }
}
