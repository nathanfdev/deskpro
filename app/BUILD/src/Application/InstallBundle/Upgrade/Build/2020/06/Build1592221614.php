<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1592221614 extends AbstractBuild implements BlockingBuildInterface, SkipPostBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
        $this->execDbQuery('default', 'ALTER TABLE community_forums ADD noun VARCHAR(255) NOT NULL, ADD plural VARCHAR(255) NOT NULL, ADD verb_action VARCHAR(255) NOT NULL');
    }

    public function run()
    {
        $this->getDbConnection()->executeUpdate("UPDATE community_forums SET noun = title, plural = CONCAT(title, 's'), verb_action = CONCAT('New ', title)");
    }
}
