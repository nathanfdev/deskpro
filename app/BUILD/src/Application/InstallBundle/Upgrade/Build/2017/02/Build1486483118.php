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

class Build1486483118 extends AbstractBuild
{
    public function run()
    {
        $this->out('My Upgrade Class');
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
        $this->execMutateSql("
            INSERT INTO `worker_jobs` (`id`, `worker_group`, `title`, `description`, `job_class`, `data`, `run_interval`, `last_run_date`)
            VALUES ('twilio_sync', 'twilio_sync', 'Sync Twilio account', 'Sync Twilio account', 'Application\\\\DeskPRO\\\\WorkerProcess\\\\Job\\\\TwilioSync', X'613A303A7B7D', 60, NULL)
        ");
    }
}
