<?php

namespace Application\InstallBundle\Upgrade\Build;

/**
 * Class Build1513248744.
 */
class Build1513248744 extends AbstractBuild implements BlockingBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
        $this->execDbQuery('default', 'ALTER TABLE report_dashboard_report ADD variables LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json_array)\', CHANGE sort_order sort_order INT DEFAULT 0 NOT NULL, CHANGE columns columns INT DEFAULT 24 NOT NULL');
    }

    public function run()
    {
    }
}
