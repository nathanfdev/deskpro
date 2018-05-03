<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1504602761 extends AbstractBuild implements BlockingBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
        $this->execSlowAlterTable('tickets', 'ADD date_on_hold DATETIME DEFAULT NULL');
    }

    public function run()
    {
    }
}
