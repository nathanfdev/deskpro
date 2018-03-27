<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1498832258 extends AbstractBuild implements OnlineBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
        $this->execSlowAlterTable('organization_notes', 'CHANGE note note LONGTEXT NOT NULL');
        $this->execSlowAlterTable('people_notes', 'CHANGE note note LONGTEXT NOT NULL');
    }

    public function run()
    {
    }
}
