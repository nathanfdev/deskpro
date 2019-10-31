<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1560448193 extends AbstractBuild implements OnlineBuildInterface, SkipPostBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
        $this->execDbQuery('default', 'ALTER TABLE voice_phone_calls ADD enqueued_as VARCHAR(255) NOT NULL');
    }

    public function run()
    {
    }
}
