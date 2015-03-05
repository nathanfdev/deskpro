<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage
 */

namespace Application\InstallBundle\Upgrade\Build;

class Build1407241623 extends AbstractBuild
{
    public function run()
    {
        $this->out("Install round robin tables");
        $this->execMutateSql("CREATE TABLE log_round_robin (id INT AUTO_INCREMENT NOT NULL, timestamp int(11) unsigned not null, round_robin_id int(11) unsigned not null, agent_id int(11) unsigned not null, ticket_id int(11) unsigned not null, trigger_id int(11) unsigned not null, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci");
        $this->execMutateSql("CREATE TABLE round_robin (id INT AUTO_INCREMENT NOT NULL, next_agent_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, INDEX IDX_A56034E1C0E3DE5 (next_agent_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci");
        $this->execMutateSql("CREATE TABLE round_robin_agents (agent_id INT NOT NULL, robin_id INT NOT NULL, sort INT NOT NULL, INDEX IDX_B51E3A8F3414710B (agent_id), INDEX IDX_B51E3A8F654E52F7 (robin_id), PRIMARY KEY(agent_id, robin_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci");
        $this->execMutateSql("ALTER TABLE round_robin ADD CONSTRAINT FK_A56034E1C0E3DE5 FOREIGN KEY (next_agent_id) REFERENCES people (id) ON DELETE SET NULL");
        $this->execMutateSql("ALTER TABLE round_robin_agents ADD CONSTRAINT FK_B51E3A8F3414710B FOREIGN KEY (agent_id) REFERENCES people (id) ON DELETE CASCADE");
        $this->execMutateSql("ALTER TABLE round_robin_agents ADD CONSTRAINT FK_B51E3A8F654E52F7 FOREIGN KEY (robin_id) REFERENCES round_robin (id) ON DELETE CASCADE");
    }
}
