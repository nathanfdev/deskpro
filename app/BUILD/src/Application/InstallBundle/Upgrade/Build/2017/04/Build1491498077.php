<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1491498077 extends AbstractBuild implements OnlineBuildInterface, SkipPostBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
        $this->out('Add is_pinned column to agent_chat table');
        $this->execDbQuery('default', "ALTER TABLE agent_chat ADD is_pinned TINYINT(1) DEFAULT '0' NOT NULL");
    }

    public function run()
    {
    }
}
