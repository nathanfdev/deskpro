<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1575391275 extends AbstractBuild implements OnlineBuildInterface, SkipPostBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
        $this->execDbQueryQuiet('default', 'DROP INDEX UNIQ_8F94AF4B665648E9 ON community_forums');
    }

    public function run()
    {
    }
}
