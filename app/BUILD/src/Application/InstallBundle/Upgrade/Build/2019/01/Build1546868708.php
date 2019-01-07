<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1546868708 extends AbstractBuild implements BlockingBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
        $this->execDbQueryQuiet('default', 'ALTER TABLE chat_conversations ADD access_token VARCHAR(30) DEFAULT NULL');
        $this->execDbQueryQuiet('default', 'CREATE INDEX access_token_idx ON chat_conversations (access_token)');
    }

    public function run()
    {
    }
}
