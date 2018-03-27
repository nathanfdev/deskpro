<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1509706441 extends AbstractBuild implements BlockingBuildInterface, SkipPostBuildInterface
{
    public function addNewTables()
    {
        $this->execDbQuery('default', 'DROP TABLE voice_queue_agents');
        $this->execDbQuery('default', 'CREATE TABLE voice_queue_agents (id INT AUTO_INCREMENT NOT NULL, voice_queue_id INT NOT NULL, agent_id INT NOT NULL, is_enabled TINYINT(1) NOT NULL, INDEX IDX_50376B502E24EDAB (voice_queue_id), INDEX IDX_50376B503414710B (agent_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->execDbQuery('default', 'ALTER TABLE voice_queue_agents ADD CONSTRAINT FK_50376B502E24EDAB FOREIGN KEY (voice_queue_id) REFERENCES voice_queues (id) ON DELETE CASCADE');
        $this->execDbQuery('default', 'ALTER TABLE voice_queue_agents ADD CONSTRAINT FK_50376B503414710B FOREIGN KEY (agent_id) REFERENCES people (id) ON DELETE CASCADE');
        $this->execDbQuery('default', 'CREATE UNIQUE INDEX queue_agent_idx ON voice_queue_agents (voice_queue_id, agent_id)');
    }

    public function runAlters()
    {
    }

    public function run()
    {
    }
}
