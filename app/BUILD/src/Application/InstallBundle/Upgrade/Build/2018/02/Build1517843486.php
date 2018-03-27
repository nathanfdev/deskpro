<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1517843486 extends AbstractBuild implements OnlineBuildInterface, SkipPostBuildInterface
{
    public function addNewTables()
    {
        $this->execDbQuery('default', 'ALTER TABLE scheduled_reports ADD send_to LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json_array)\'');
    }

    public function runAlters()
    {
    }

    public function run()
    {
    }
}
