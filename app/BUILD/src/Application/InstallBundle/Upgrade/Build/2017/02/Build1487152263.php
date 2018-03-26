<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1487152263 extends AbstractBuild
{
    public function run()
    {
        $this->out('Updates to voice tables');
        $this->execDbQuery('default', 'ALTER TABLE voice_queues ADD voicemail_department INT DEFAULT NULL, ADD voicemail_agent INT DEFAULT NULL, ADD voicemail_agent_team INT DEFAULT NULL');
        $this->execDbQuery('default', 'ALTER TABLE voice_queues ADD CONSTRAINT FK_80C86EADE85D5A5 FOREIGN KEY (voicemail_department) REFERENCES departments (id)');
        $this->execDbQuery('default', 'ALTER TABLE voice_queues ADD CONSTRAINT FK_80C86EA2FD734D6 FOREIGN KEY (voicemail_agent) REFERENCES people (id)');
        $this->execDbQuery('default', 'ALTER TABLE voice_queues ADD CONSTRAINT FK_80C86EA7AE328D3 FOREIGN KEY (voicemail_agent_team) REFERENCES agent_teams (id)');
        $this->execDbQuery('default', 'CREATE INDEX IDX_80C86EADE85D5A5 ON voice_queues (voicemail_department)');
        $this->execDbQuery('default', 'CREATE INDEX IDX_80C86EA2FD734D6 ON voice_queues (voicemail_agent)');
        $this->execDbQuery('default', 'CREATE INDEX IDX_80C86EA7AE328D3 ON voice_queues (voicemail_agent_team)');
        $this->execDbQuery('default', 'ALTER TABLE voice_accounts ADD voicemail_queue_sid VARCHAR(100) DEFAULT NULL;');
        $this->execDbQuery('default', 'ALTER TABLE voice_accounts ADD voicemail_worker_sid VARCHAR(100) DEFAULT NULL;');
        $this->execDbQuery('default', 'ALTER TABLE agent_data ADD voice_task_queue_sid VARCHAR(100) DEFAULT NULL;');
        $this->execDbQuery('default', 'ALTER TABLE voice_queues CHANGE task_queue_sid task_queue_sid VARCHAR(100) DEFAULT NULL;');
        $this->execDbQuery('default', 'ALTER TABLE voice_accounts ADD date_sync DATETIME DEFAULT NULL, ADD date_last_sync DATETIME DEFAULT NULL;');
    }
}
