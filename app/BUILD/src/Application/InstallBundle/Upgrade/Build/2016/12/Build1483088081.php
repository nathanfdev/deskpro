<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1483088081 extends AbstractBuild
{
    public function run()
    {
        $this->out('Add group name to group chats');
        $this->execDbQuery('default', 'ALTER TABLE agent_chat ADD name VARCHAR(255) DEFAULT NULL');
    }
}
