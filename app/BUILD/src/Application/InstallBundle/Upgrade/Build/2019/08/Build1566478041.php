<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1566478041 extends AbstractBuild implements OnlineBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
    }

    public function run()
    {
        $this->execDbQuery('default', "UPDATE IGNORE `custom_def_community_topic` SET `sys_name` = 'cat' WHERE `sys_name` = 'chan'");
    }
}
