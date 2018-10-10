<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1539164606 extends AbstractBuild implements OnlineBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
    }

    public function run()
    {
        $this->execDbQuery('default', 'UPDATE phone_numbers SET type="person" WHERE type IS NULL OR type = ""');
    }
}
