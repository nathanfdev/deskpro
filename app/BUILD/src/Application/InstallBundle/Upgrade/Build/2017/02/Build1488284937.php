<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1488284937 extends AbstractBuild
{
    public function run()
    {
        $this->out('Add admin to chats');
        $this->execDbQuery('default', 'ALTER TABLE agent_chat ADD admin_id INT DEFAULT NULL');
        $this->execDbQuery('default', 'ALTER TABLE agent_chat ADD CONSTRAINT FK_C8064849642B8210 FOREIGN KEY (admin_id) REFERENCES people (id) ON DELETE SET NULL');
        $this->execDbQuery('default', 'CREATE INDEX IDX_C8064849642B8210 ON agent_chat (admin_id)');
    }
}
