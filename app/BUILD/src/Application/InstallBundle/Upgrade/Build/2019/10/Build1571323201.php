<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1571323201 extends AbstractBuild implements OnlineBuildInterface, SkipPostBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
        $this->execDbQuery('default', 'ALTER TABLE voice_phone_call_participants ADD data LONGTEXT NOT NULL COMMENT \'(DC2Type:json_array)\'');
    }

    public function run()
    {
    }
}
