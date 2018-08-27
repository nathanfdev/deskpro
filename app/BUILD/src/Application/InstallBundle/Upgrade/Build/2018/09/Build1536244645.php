<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1536244645 extends AbstractBuild implements OnlineBuildInterface
{
    public function addNewTables()
    {
        $this->execDbQuery('voice', 'CREATE TABLE voice_tasks (id INT AUTO_INCREMENT NOT NULL, channel VARCHAR(255) NOT NULL, priority INT NOT NULL, workers LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json_array)\', accepted_worker INT DEFAULT NULL, timeout INT DEFAULT NULL, status VARCHAR(255) NOT NULL, status_reason VARCHAR(255) DEFAULT NULL, rejected_by LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json_array)\', date_created DATETIME NOT NULL, attributes LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json_array)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->execDbQuery('voice', 'CREATE TABLE voice_task_queues (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(255) NOT NULL, type_id INT NOT NULL, attributes LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json_array)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->execDbQuery('voice', 'CREATE TABLE voice_workers (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(255) NOT NULL, type_id INT NOT NULL, activity VARCHAR(255) NOT NULL, date_last_active DATETIME DEFAULT NULL, attributes LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json_array)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
    }

    public function runAlters()
    {
        $this->execDbQuery('default', 'ALTER TABLE agent_data DROP voice_worker_sid, DROP voice_task_queue_sid');
        $this->execDbQuery('default', 'DROP INDEX workspace_sid ON voice_accounts');
        $this->execDbQuery('default', 'ALTER TABLE voice_accounts DROP workspace_sid, DROP queue_workflow_sid, DROP voicemail_queue_sid, DROP voicemail_worker_sid, DROP date_sync, DROP date_last_sync');
        $this->execDbQuery('default', 'DROP INDEX task_queue_sid ON voice_queues');
        $this->execDbQuery('default', 'ALTER TABLE voice_queues DROP task_queue_sid');
    }

    public function run()
    {
    }
}
