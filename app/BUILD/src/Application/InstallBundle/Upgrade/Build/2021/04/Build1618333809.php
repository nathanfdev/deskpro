<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1618333809 extends AbstractBuild implements BlockingBuildInterface, SkipPostBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
        $this->execDbQuery('default', 'ALTER TABLE guides CHANGE description description LONGTEXT DEFAULT NULL');
    }

    public function run()
    {
    }
}
