<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1498832256 extends AbstractBuild implements OnlineBuildInterface, SkipPostBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
        $this->execSlowAlterTable('tickets_search_active', 'ADD email_account_id INT DEFAULT NULL');
    }

    public function run()
    {
    }
}
