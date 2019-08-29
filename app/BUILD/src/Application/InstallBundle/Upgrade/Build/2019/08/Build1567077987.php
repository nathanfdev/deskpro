<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1567077987 extends AbstractBuild implements OnlineBuildInterface, SkipPostBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
        $this->execDbQuery('default', 'ALTER TABLE voice_phone_calls ADD number_plain VARCHAR(50) NOT NULL');
    }

    public function run()
    {
    }
}
