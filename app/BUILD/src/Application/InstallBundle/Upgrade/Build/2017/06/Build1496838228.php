<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1496838228 extends AbstractBuild implements BlockingBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
        $this->execDbQuery('default', 'ALTER TABLE voice_assets ADD auto_generated TINYINT(1) DEFAULT NULL');
    }

    public function run()
    {
    }
}
