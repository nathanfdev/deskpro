<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1551355022 extends AbstractBuild implements OnlineBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
        $this->execDbQuery('default', 'ALTER TABLE voice_phone_calls ADD task_sids LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json_array)\'');
    }

    public function run()
    {
    }
}
