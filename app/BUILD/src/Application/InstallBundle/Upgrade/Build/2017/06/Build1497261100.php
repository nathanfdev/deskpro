<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1497261100 extends AbstractBuild implements BlockingBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
        $this->execDbQuery('default', 'ALTER TABLE app2_app_state DROP ownerId');
    }

    public function run()
    {
    }
}
