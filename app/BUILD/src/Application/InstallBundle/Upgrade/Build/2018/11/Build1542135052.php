<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1542135052 extends AbstractBuild implements BlockingBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
        $this->execDbQuery('default', 'ALTER TABLE chat_conversations ADD access_token VARCHAR(30) DEFAULT NULL');
        $this->execDbQuery('default', 'CREATE INDEX access_token_idx ON chat_conversations (access_token)');
    }

    public function run()
    {
    }
}
