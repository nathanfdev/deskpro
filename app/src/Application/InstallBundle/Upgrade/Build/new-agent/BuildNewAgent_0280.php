<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

/**
 * DeskPRO.
 */
namespace Application\InstallBundle\Upgrade\Build;

class BuildNewAgent_0280 extends AbstractBuild
{
    public function run()
    {
        $this->out('Chat DB architecture');
        $this->execMutateSql("CREATE TABLE agent_chat (id INT AUTO_INCREMENT NOT NULL, is_archived TINYINT(1) DEFAULT '0' NOT NULL, date_created DATETIME NOT NULL, date_last_message DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci");
        $this->execMutateSql("CREATE TABLE agent_chat_message (id INT AUTO_INCREMENT NOT NULL, agent_chat_id INT NOT NULL, person_id INT DEFAULT NULL, person_name VARCHAR(255) NOT NULL, message VARCHAR(255) NOT NULL, metadata LONGTEXT NOT NULL COMMENT '(DC2Type:json_array)', date_created DATETIME NOT NULL, INDEX IDX_8CFF73DDC8C9138F (agent_chat_id), INDEX IDX_8CFF73DD217BBB47 (person_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci");
        $this->execMutateSql('CREATE TABLE agent_chat_participant (id INT AUTO_INCREMENT NOT NULL, person_id INT DEFAULT NULL, department_id INT DEFAULT NULL, agent_team_id INT DEFAULT NULL, agent_chat_id INT NOT NULL, INDEX IDX_46627D33217BBB47 (person_id), INDEX IDX_46627D33AE80F5DF (department_id), INDEX IDX_46627D33FB3FBA04 (agent_team_id), INDEX IDX_46627D33C8C9138F (agent_chat_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci');
        $this->execMutateSql('ALTER TABLE agent_chat_message ADD CONSTRAINT FK_8CFF73DDC8C9138F FOREIGN KEY (agent_chat_id) REFERENCES agent_chat (id) ON DELETE CASCADE');
        $this->execMutateSql('ALTER TABLE agent_chat_message ADD CONSTRAINT FK_8CFF73DD217BBB47 FOREIGN KEY (person_id) REFERENCES people (id) ON DELETE SET NULL');
        $this->execMutateSql('ALTER TABLE agent_chat_participant ADD CONSTRAINT FK_46627D33217BBB47 FOREIGN KEY (person_id) REFERENCES people (id) ON DELETE SET NULL');
        $this->execMutateSql('ALTER TABLE agent_chat_participant ADD CONSTRAINT FK_46627D33AE80F5DF FOREIGN KEY (department_id) REFERENCES departments (id) ON DELETE CASCADE');
        $this->execMutateSql('ALTER TABLE agent_chat_participant ADD CONSTRAINT FK_46627D33FB3FBA04 FOREIGN KEY (agent_team_id) REFERENCES agent_teams (id) ON DELETE CASCADE');
        $this->execMutateSql('ALTER TABLE agent_chat_participant ADD CONSTRAINT FK_46627D33C8C9138F FOREIGN KEY (agent_chat_id) REFERENCES agent_chat (id) ON DELETE CASCADE');
    }
}

//[[build:1456790428]]

