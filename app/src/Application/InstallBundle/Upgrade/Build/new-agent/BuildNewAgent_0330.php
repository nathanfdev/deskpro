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

class BuildNewAgent_0330 extends AbstractBuild
{
    public function run()
    {
        $this->out('My Upgrade Class');
        $this->execMutateSql("CREATE TABLE person_settings (person_id INT NOT NULL, name VARCHAR(255) NOT NULL, value LONGTEXT NOT NULL COMMENT '(DC2Type:json_array)', INDEX IDX_710305F2217BBB47 (person_id), PRIMARY KEY(person_id, name)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci");
        $this->execMutateSql('ALTER TABLE person_settings ADD CONSTRAINT FK_710305F2217BBB47 FOREIGN KEY (person_id) REFERENCES people (id)');
        $this->execMutateSql('ALTER TABLE tasks DROP date_done');
        $this->execMutateSql('ALTER TABLE agent_chat_message DROP FOREIGN KEY FK_8CFF73DD217BBB47');
        $this->execMutateSql('ALTER TABLE agent_chat_message CHANGE person_id person_id INT NOT NULL');
        $this->execMutateSql('ALTER TABLE agent_chat_message ADD CONSTRAINT FK_8CFF73DD217BBB47 FOREIGN KEY (person_id) REFERENCES people (id)');
        $this->execMutateSql('ALTER TABLE agent_chat_participant DROP FOREIGN KEY FK_46627D33217BBB47');
        $this->execMutateSql('ALTER TABLE agent_chat_participant DROP FOREIGN KEY FK_46627D33AE80F5DF');
        $this->execMutateSql('ALTER TABLE agent_chat_participant DROP FOREIGN KEY FK_46627D33FB3FBA04');
        $this->execMutateSql('ALTER TABLE agent_chat_participant ADD CONSTRAINT FK_46627D33217BBB47 FOREIGN KEY (person_id) REFERENCES people (id)');
        $this->execMutateSql('ALTER TABLE agent_chat_participant ADD CONSTRAINT FK_46627D33AE80F5DF FOREIGN KEY (department_id) REFERENCES departments (id)');
        $this->execMutateSql('ALTER TABLE agent_chat_participant ADD CONSTRAINT FK_46627D33FB3FBA04 FOREIGN KEY (agent_team_id) REFERENCES agent_teams (id)');
        $this->execMutateSql('ALTER TABLE tasks_new ADD display_order INT NOT NULL');
        $this->execMutateSql('ALTER TABLE task_assignments DROP FOREIGN KEY FK_76FFFDEF8DB60186');
        $this->execMutateSql('ALTER TABLE task_assignments ADD CONSTRAINT FK_76FFFDEF8DB60186 FOREIGN KEY (task_id) REFERENCES tasks_new (id) ON DELETE CASCADE');
        $this->execMutateSql('ALTER TABLE task_lists ADD title VARCHAR(255) NOT NULL, ADD display_order INT DEFAULT NULL');
        $this->execMutateSql('ALTER TABLE custom_ticket_filters CHANGE term term LONGTEXT NOT NULL');
    }
}

//[[build:1456790433]]

