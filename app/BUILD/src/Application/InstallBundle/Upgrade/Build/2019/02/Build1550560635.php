<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1550560635 extends AbstractBuild implements BlockingBuildInterface, SkipPostBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
        $this->execDbQuery('default', 'TRUNCATE lock_keys');
        $this->execDbQuery('default', 'ALTER TABLE lock_keys DROP PRIMARY KEY, ADD PRIMARY KEY (`key_id`);');
    }

    public function run()
    {
    }
}
