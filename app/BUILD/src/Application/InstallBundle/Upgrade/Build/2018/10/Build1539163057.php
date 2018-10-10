<?php
namespace Application\InstallBundle\Upgrade\Build;

class Build1539163057 extends AbstractBuild implements OnlineBuildInterface, SkipPostBuildInterface
{
    public function addNewTables()
    {
    }


    public function runAlters()
    {
        $this->execDbQuery('default', 'ALTER TABLE phrases ADD is_managed TINYINT(1) DEFAULT \'0\'');
    }

    public function run()
    {
    }
}
