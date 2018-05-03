<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1511790409 extends AbstractBuild implements OnlineBuildInterface, SkipPostBuildInterface
{
    public function addNewTables()
    {
        $this->execDbQuery('default', 'CREATE TABLE chat_round_robin (id INT AUTO_INCREMENT NOT NULL, last_agent_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, online_only TINYINT(1) NOT NULL, apply_by_default TINYINT(1) NOT NULL, routing_type INT NOT NULL, INDEX IDX_9A12C3794C753495 (last_agent_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->execDbQuery('default', 'CREATE TABLE chat_round_robin_to_department (chat_round_robin_id INT NOT NULL, department_id INT NOT NULL, INDEX IDX_7AF10D1D2E0F63B0 (chat_round_robin_id), INDEX IDX_7AF10D1DAE80F5DF (department_id), PRIMARY KEY(chat_round_robin_id, department_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->execDbQuery('default', 'CREATE TABLE chat_round_robin_agents (agent_id INT NOT NULL, robin_id INT NOT NULL, sort INT NOT NULL, last_activity DATETIME DEFAULT NULL, INDEX IDX_4AF4702D3414710B (agent_id), INDEX IDX_4AF4702D654E52F7 (robin_id), PRIMARY KEY(agent_id, robin_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->execDbQuery('default', 'CREATE TABLE chat_round_robin_log (id INT AUTO_INCREMENT NOT NULL, rr_id INT DEFAULT NULL, chat_id INT NOT NULL, chat_subject VARCHAR(255) NOT NULL, actions LONGBLOB NOT NULL COMMENT \'(DC2Type:array)\', created DATETIME NOT NULL, INDEX IDX_2FA4BCB91D063087 (rr_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->execDbQuery('default', 'ALTER TABLE chat_round_robin ADD CONSTRAINT FK_9A12C3794C753495 FOREIGN KEY (last_agent_id) REFERENCES people (id) ON DELETE SET NULL');
        $this->execDbQuery('default', 'ALTER TABLE chat_round_robin_to_department ADD CONSTRAINT FK_7AF10D1D2E0F63B0 FOREIGN KEY (chat_round_robin_id) REFERENCES chat_round_robin (id) ON DELETE CASCADE');
        $this->execDbQuery('default', 'ALTER TABLE chat_round_robin_to_department ADD CONSTRAINT FK_7AF10D1DAE80F5DF FOREIGN KEY (department_id) REFERENCES departments (id) ON DELETE CASCADE');
        $this->execDbQuery('default', 'ALTER TABLE chat_round_robin_agents ADD CONSTRAINT FK_4AF4702D3414710B FOREIGN KEY (agent_id) REFERENCES people (id) ON DELETE CASCADE');
        $this->execDbQuery('default', 'ALTER TABLE chat_round_robin_agents ADD CONSTRAINT FK_4AF4702D654E52F7 FOREIGN KEY (robin_id) REFERENCES chat_round_robin (id) ON DELETE CASCADE');
        $this->execDbQuery('default', 'ALTER TABLE chat_round_robin_log ADD CONSTRAINT FK_2FA4BCB91D063087 FOREIGN KEY (rr_id) REFERENCES chat_round_robin (id) ON DELETE CASCADE');
    }

    public function runAlters()
    {
    }

    public function run()
    {
    }
}
