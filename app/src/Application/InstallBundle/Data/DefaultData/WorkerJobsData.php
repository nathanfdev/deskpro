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
 * @category Install
 */

namespace Application\InstallBundle\Data\DefaultData;

use Application\DeskPRO\WorkerProcess\Job;

class WorkerJobsData extends AbstractDefaultData
{
    public function runInstall()
    {
        #------------------------------
        # Define jobs
        #------------------------------

        $jobs = array();

        $jobs[] = array(
            'id'           => 'cleanup_always',
            'worker_group' => 'cleanup',
            'title'        => 'Cleanup: Always',
            'description'  => 'Cleanup that runs every minute',
            'job_class'    => 'Application\\DeskPRO\\WorkerProcess\\Job\\CleanupAlways',
            'run_interval' => Job\CleanupAlways::DEFAULT_INTERVAL
        );

        $jobs[] = array(
            'id'           => 'cleanup_quarter_hourly',
            'worker_group' => 'cleanup',
            'title'        => 'Cleanup: Quarter Hourly',
            'description'  => 'Cleanup that runs every 15 minutes',
            'job_class'    => 'Application\\DeskPRO\\WorkerProcess\\Job\\CleanupQuarterHourly',
            'run_interval' => Job\CleanupQuarterHourly::DEFAULT_INTERVAL
        );

        $jobs[] = array(
            'id'           => 'cleanup_hourly',
            'worker_group' => 'cleanup',
            'title'        => 'Cleanup: Hourly',
            'description'  => 'Cleanup that runs every hour',
            'job_class'    => 'Application\\DeskPRO\\WorkerProcess\\Job\\CleanupHourly',
            'run_interval' => Job\CleanupHourly::DEFAULT_INTERVAL
        );

        $jobs[] = array(
            'id'           => 'cleanup_daily',
            'worker_group' => 'cleanup',
            'title'        => 'Cleanup: Daily',
            'description'  => 'Cleanup that runs every day',
            'job_class'    => 'Application\\DeskPRO\\WorkerProcess\\Job\\CleanupDaily',
            'run_interval' => Job\CleanupDaily::DEFAULT_INTERVAL
        );

        $jobs[] = array(
            'id'           => 'cleanup_weekly',
            'worker_group' => 'cleanup',
            'title'        => 'Cleanup: Weekly',
            'description'  => 'Cleanup that runs every week',
            'job_class'    => 'Application\\DeskPRO\\WorkerProcess\\Job\\CleanupWeekly',
            'run_interval' => Job\CleanupWeekly::DEFAULT_INTERVAL
        );

        $jobs[] = array(
            'id'           => 'process_email_gateways',
            'worker_group' => 'process_email_gateways',
            'title'        => 'Process Email Gateways',
            'description'  => 'Processes email from all defined email gateways',
            'job_class'    => 'Application\\DeskPRO\\WorkerProcess\\Job\\ProcessEmailGateways',
            'run_interval' => Job\ProcessEmailGateways::DEFAULT_INTERVAL
        );

        $jobs[] = array(
            'id'           => 'archive_tickets',
            'worker_group' => 'archive_tickets',
            'title'        => 'Archive Tickets',
            'description'  => 'Archives old tickets',
            'job_class'    => 'Application\\DeskPRO\\WorkerProcess\\Job\\ArchiveTickets',
            'run_interval' => Job\ArchiveTickets::DEFAULT_INTERVAL
        );

        $jobs[] = array(
            'id'           => 'article_publish_state',
            'worker_group' => 'article_publish_state',
            'title'        => 'Article Publish State',
            'description'  => 'Goes through articles with a publish date that was set in the future (publish now), or an end date set (deleting or archivng now).',
            'job_class'    => 'Application\\DeskPRO\\WorkerProcess\\Job\\ArticlePublishState',
            'run_interval' => Job\ArticlePublishState::DEFAULT_INTERVAL
        );

        $jobs[] = array(
            'id'           => 'chat_ping_timeout',
            'worker_group' => 'chat',
            'title'        => 'Chat Ping Timeout',
            'description'  => 'Timesout chats where both parties are not longer participating',
            'job_class'    => 'Application\\DeskPRO\\WorkerProcess\\Job\\ChatPingTimeout',
            'run_interval' => Job\ChatPingTimeout::DEFAULT_INTERVAL
        );

        $jobs[] = array(
            'id'           => 'chat_transcripts',
            'worker_group' => 'chat',
            'title'        => 'Send Chat Transcripts',
            'description'  => 'Send chat transcripts',
            'job_class'    => 'Application\\DeskPRO\\WorkerProcess\\Job\\ChatTranscripts',
            'run_interval' => Job\ChatTranscripts::DEFAULT_INTERVAL
        );

        $jobs[] = array(
            'id'           => 'sitemap_file',
            'worker_group' => 'sitemap_file',
            'title'        => 'Generate Sitemap',
            'description'  => 'Generates the sitemap.xml file',
            'job_class'    => 'Application\\DeskPRO\\WorkerProcess\\Job\\SitemapFile',
            'run_interval' => Job\SitemapFile::DEFAULT_INTERVAL
        );

        $jobs[] = array(
            'id'           => 'hard_delete_tickets',
            'worker_group' => 'hard_delete_tickets',
            'title'        => 'Hard Delete Tickets',
            'description'  => 'Processes tickets that were soft-deleted long ago and permanantly deletes them',
            'job_class'    => 'Application\\DeskPRO\\WorkerProcess\\Job\\HardDeleteTickets',
            'run_interval' => Job\HardDeleteTickets::DEFAULT_INTERVAL
        );

        $jobs[] = array(
            'id'           => 'search_index_update',
            'worker_group' => 'search',
            'title'        => 'Search Index Update',
            'description'  => 'Updates the search index with updated objects',
            'job_class'    => 'Application\\DeskPRO\\WorkerProcess\\Job\\SearchIndexUpdate',
            'run_interval' => Job\SearchIndexUpdate::DEFAULT_INTERVAL
        );

        $jobs[] = array(
            'id'           => 'sendmail_queue',
            'worker_group' => 'sendmail_queue',
            'title'        => 'Sendmail Queue',
            'description'  => 'Attempts to send queued mail, or re-send fail mail',
            'job_class'    => 'Application\\DeskPRO\\WorkerProcess\\Job\\SendmailQueue',
            'run_interval' => Job\SendmailQueue::DEFAULT_INTERVAL
        );

        $jobs[] = array(
            'id'           => 'heartbeat',
            'worker_group' => 'heartbeat',
            'title'        => 'Heartbeat',
            'description'  => 'Send heartbeat ping',
            'job_class'    => 'Application\\DeskPRO\\WorkerProcess\\Job\\Heartbeat',
            'run_interval' => Job\Heartbeat::DEFAULT_INTERVAL
        );

        $jobs[] = array(
            'id'           => 'kb_subscriptions',
            'worker_group' => 'kb_subscriptions',
            'title'        => 'KB Subscriptions',
            'description'  => 'Sends notifications to users who are subscribed to articles or categories',
            'job_class'    => 'Application\\DeskPRO\\WorkerProcess\\Job\\KbSubscriptions',
            'run_interval' => Job\KbSubscriptions::DEFAULT_INTERVAL
        );

        $jobs[] = array(
            'id'           => 'move_blobs',
            'worker_group' => 'move_blobs',
            'title'        => 'Move Blobs',
            'description'  => 'When the storage mechanism is changed, this job moves existing blobs to the new mechanism a bit at a time',
            'job_class'    => 'Application\\DeskPRO\\WorkerProcess\\Job\\MoveBlobs',
            'run_interval' => Job\MoveBlobs::DEFAULT_INTERVAL
        );

        $jobs[] = array(
            'id'           => 'delete_spam_tickets',
            'worker_group' => 'delete_spam_tickets',
            'title'        => 'Delete Spam Tickets',
            'description'  => 'Runs through old spammed tickets and deletes them',
            'job_class'    => 'Application\\DeskPRO\\WorkerProcess\\Job\\DeleteSpamTickets',
            'run_interval' => Job\DeleteSpamTickets::DEFAULT_INTERVAL
        );

        $jobs[] = array(
            'id'           => 'agent_mode_ticket_reasssign',
            'worker_group' => 'agent_mode_ticket_reasssign',
            'title'        => 'Reassign tickets of vacation or deleted agents',
            'description'  => 'When an agent enters vacation mode or is deleted, we need to batch-update their tickets to unassigned',
            'job_class'    => 'Application\\DeskPRO\\WorkerProcess\\Job\\AgentModeTicketReassign',
            'run_interval' => Job\AgentModeTicketReassign::DEFAULT_INTERVAL
        );

        $jobs[] = array(
            'id'           => 'ticket_escalations',
            'worker_group' => 'ticket_escalations',
            'title'        => 'Ticket Escalations',
            'description'  => 'Executes ticket escalations',
            'job_class'    => 'Application\\DeskPRO\\WorkerProcess\\Job\\TicketEscalations',
            'run_interval' => Job\TicketEscalations::DEFAULT_INTERVAL
        );

        $jobs[] = array(
            'id'           => 'ticket_slas',
            'worker_group' => 'ticket_slas',
            'title'        => 'Ticket SLAs',
            'description'  => 'Updates ticket SLA status',
            'job_class'    => 'Application\\DeskPRO\\WorkerProcess\\Job\\TicketSlas',
            'run_interval' => Job\TicketSlas::DEFAULT_INTERVAL
        );

        $jobs[] = array(
            'id'           => 'run_queued_tasks',
            'worker_group' => 'run_queued_tasks',
            'title'        => 'Run Queued Tasks',
            'description'  => 'Runs any general-purpose queued tasks',
            'job_class'    => 'Application\\DeskPRO\\WorkerProcess\\Job\\RunQueuedTasks',
            'run_interval' => Job\RunQueuedTasks::DEFAULT_INTERVAL
        );

        $jobs[] = array(
            'id'           => 'task_reminders',
            'worker_group' => 'task_reminders',
            'title'        => 'Task Reminders',
            'description'  => 'Sends task reminder notifications',
            'job_class'    => 'Application\\DeskPRO\\WorkerProcess\\Job\\TaskReminders',
            'run_interval' => Job\TaskReminders::DEFAULT_INTERVAL
        );

        $jobs[] = array(
            'id'           => 'twitter_stream',
            'worker_group' => 'twitter_stream',
            'title'        => 'Twitter StreamTwitter Stream',
            'description'  => 'Imports tweets from the Twitter stream',
            'job_class'    => 'Application\\DeskPRO\\WorkerProcess\\Job\\TwitterStream',
            'run_interval' => Job\TwitterStream::DEFAULT_INTERVAL
        );

        $jobs[] = array(
            'id'           => 'jira_comments_fetch',
            'worker_group' => 'jira_comments_fetch',
            'title'        => 'JIRA Comments Fetch',
            'description'  => 'Fetches JIRA comments on issues that are associated with DeskPRO tickets',
            'job_class'    => 'Application\\DeskPRO\\WorkerProcess\\Job\\FetchJiraComments',
            'run_interval' => Job\FetchJiraComments::DEFAULT_INTERVAL
        );

        $jobs[] = array(
            'id'           => 'locked_tickets_release',
            'worker_group' => 'locked_tickets_release',
            'title'        => 'Locked Tickets Release',
            'description'  => 'Release locked tickets',
            'job_class'    => 'Application\\DeskPRO\\WorkerProcess\\Job\\ReleaseLockedTickets',
            'run_interval' => Job\ReleaseLockedTickets::DEFAULT_INTERVAL,
        );

        $jobs[] = array(
            'id'           => 'job_queue',
            'worker_group' => 'job_queue',
            'title'        => 'Job Queue',
            'description'  => 'Processes the job queue',
            'job_class'    => 'Application\\DeskPRO\\WorkerProcess\\Job\\JobQueueExecutor',
            'run_interval' => Job\JobQueueExecutor::DEFAULT_INTERVAL,
        );

        $jobs[] = array(
            'id'           => 'job_supervisor',
            'worker_group' => 'job_queue',
            'title'        => 'Job Supervisor',
            'description'  => 'Supervises the job queue',
            'job_class'    => 'Application\\DeskPRO\\WorkerProcess\\Job\\JobQueueSupervisor',
            'run_interval' => Job\JobQueueSupervisor::DEFAULT_INTERVAL,
        );

        #------------------------------
        # Insert jobs
        #------------------------------

        $got_ids = array_map(function ($j) { return $j['id']; }, $jobs);
        $got_ids = "'" . implode("', '", $got_ids) . "'";
        $this->getDb()->executeUpdate("DELETE FROM worker_jobs WHERE id NOT IN ($got_ids)");

        $exist_id_map = $this->getDb()->fetchAllKeyValue("
            SELECT id, id
            FROM worker_jobs
        ");

        foreach ($jobs as $job) {

            if (!empty($job['data'])) {
                $job['data'] = serialize($job['data']);
            } else {
                $job['data'] = 'a:0:{}';
            }

            $exist_id = isset($exist_id_map[$job['id']]) ? $exist_id_map[$job['id']] : null;
            if ($exist_id) {
                unset($job['id']);
                $this->getDb()->update('worker_jobs', $job, array('id' => $exist_id));
            } else {
                $this->getDb()->insert('worker_jobs', $job);
            }
        }
    }

    public function runReset()
    {
        $this->runInstall();
    }

    public function runSync()
    {
        $this->runInstall();
    }
}
