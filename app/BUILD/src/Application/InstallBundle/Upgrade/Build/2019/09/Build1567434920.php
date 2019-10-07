<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1567434920 extends AbstractBuild implements OnlineBuildInterface, SkipPostBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
        if (!$this->getSchemaHelper()->tableHasColumn('voice_phone_calls', 'number_plain')) {
            $this->execDbQuery('default', 'ALTER TABLE voice_phone_calls ADD number_plain VARCHAR(50) NOT NULL');
        }
    }

    public function run()
    {
    }
}
