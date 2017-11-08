<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

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
