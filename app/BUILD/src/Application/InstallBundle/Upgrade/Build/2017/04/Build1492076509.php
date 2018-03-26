<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1492076509 extends AbstractBuild implements OnlineBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
        if (!$this->getSchemaHelper()->tableHasColumn('agent_chat', 'is_pinned')) {
            $this->out('Add is_pinned column to agent_chat table');
            $this->execDbQuery('default', "ALTER TABLE agent_chat ADD is_pinned TINYINT(1) DEFAULT '0' NOT NULL");
        }
    }

    public function run()
    {
    }
}
