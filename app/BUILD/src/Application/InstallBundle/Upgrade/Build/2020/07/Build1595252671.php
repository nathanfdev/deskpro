<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1595252671 extends AbstractBuild implements OnlineBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
        $this->execDbQuery('default', 'CREATE UNIQUE INDEX queue_agent_idx ON user_chat_queue_targets (queue_id, agent_id)');
    }

    public function run()
    {
    }
}
