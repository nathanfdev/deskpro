<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1522846868 extends AbstractBuild implements OnlineBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
    }

    public function run()
    {
        $this->execDbQuery('default', 'DELETE FROM `api_key_limits` WHERE `limit_type` = \'global\';');
    }
}
