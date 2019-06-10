<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1560158533 extends AbstractBuild implements OnlineBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
        $this->execSlowAlterTable('phone_numbers', 'CHANGE number number VARCHAR(255) NOT NULL');
    }

    public function run()
    {
    }
}
