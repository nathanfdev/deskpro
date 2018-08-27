<?php

/**
 * DeskPRO.
 *
 * @category Install
 */

namespace Application\InstallBundle\Data\DefaultData;

use Application\DeskPRO\WorkerProcess\Job;

/**
 * Class WorkerJobsData.
 */
class WorkerJobsData extends AbstractDefaultData
{
    public function runInstall()
    {
        //------------------------------
        // Define jobs
        //------------------------------

        $jobs = [];

        $jobs[] = [
            'id'           => 'cleanup_always',
            'worker_group' => 'cleanup',
            'title'        => 'Cleanup: Always',
            'description'  => 'Cleanup that runs every minute',
            'job_class'    => Job\CleanupAlways::class,
            'run_interval' => Job\CleanupAlways::DEFAULT_INTERVAL,
        ];

        $jobs[] = [
            'id'           => 'cleanup_quarter_hourly',
            'worker_group' => 'cleanup',
            'title'        => 'Cleanup: Quarter Hourly',
            'description'  => 'Cleanup that runs every 15 minutes',
            'job_class'    => Job\CleanupQuarterHourly::class,
            'run_interval' => Job\CleanupQuarterHourly::DEFAULT_INTERVAL,
        ];

        $jobs[] = [
            'id'           => 'cleanup_hourly',
            'worker_group' => 'cleanup',
            'title'        => 'Cleanup: Hourly',
            'description'  => 'Cleanup that runs every hour',
            'job_class'    => Job\CleanupHourly::class,
            'run_interval' => Job\CleanupHourly::DEFAULT_INTERVAL,
        ];

        $jobs[] = [
            'id'           => 'cleanup_daily',
            'worker_group' => 'cleanup',
            'title'        => 'Cleanup: Daily',
            'description'  => 'Cleanup that runs every day',
            'job_class'    => Job\CleanupDaily::class,
            'run_interval' => Job\CleanupDaily::DEFAULT_INTERVAL,
        ];

        $jobs[] = [
            'id'           => 'cleanup_weekly',
            'worker_group' => 'cleanup',
            'title'        => 'Cleanup: Weekly',
            'description'  => 'Cleanup that runs every week',
            'job_class'    => Job\CleanupWeekly::class,
            'run_interval' => Job\CleanupWeekly::DEFAULT_INTERVAL,
        ];

        $jobs[] = [
            'id'           => 'process_email_gateways',
            'worker_group' => 'process_email_gateways',
            'title'        => 'Process Email Gateways',
            'description'  => 'Processes email from all defined email gateways',
            'job_class'    => 'Application\\DeskPRO\\WorkerProcess\\Job\\ProcessEmailGateways',
            'run_interval' => Job\ProcessEmailGateways::DEFAULT_INTERVAL,
        ];

        $jobs[] = [
            'id'           => 'archive_tickets',
            'worker_group' => 'archive_tickets',
            'title'        => 'Archive Tickets',
            'description'  => 'Archives old tickets',
            'job_class'    => Job\ArchiveTickets::class,
            'run_interval' => Job\ArchiveTickets::DEFAULT_INTERVAL,
        ];

        $jobs[] = [
            'id'           => 'article_publish_state',
            'worker_group' => 'article_publish_state',
            'title'        => 'Article Publish State',
            'description'  => 'Goes through articles with a publish date that was set in the future (publish now), or an end date set (deleting or archivng now).',
            'job_class'    => Job\ArticlePublishState::class,
            'run_interval' => Job\ArticlePublishState::DEFAULT_INTERVAL,
        ];

        $jobs[] = [
            'id'           => 'chat_ping_timeout',
            'worker_group' => 'chat',
            'title'        => 'Chat Ping Timeout',
            'description'  => 'Timesout chats where both parties are not longer participating',
            'job_class'    => Job\ChatPingTimeout::class,
            'run_interval' => Job\ChatPingTimeout::DEFAULT_INTERVAL,
        ];

        $jobs[] = [
            'id'           => 'chat_transcripts',
            'worker_group' => 'chat',
            'title'        => 'Send Chat Transcripts',
            'description'  => 'Send chat transcripts',
            'job_class'    => Job\ChatTranscripts::class,
            'run_interval' => Job\ChatTranscripts::DEFAULT_INTERVAL,
        ];

        $jobs[] = [
            'id'           => 'sitemap_file',
            'worker_group' => 'sitemap_file',
            'title'        => 'Generate Sitemap',
            'description'  => 'Generates the sitemap.xml file',
            'job_class'    => Job\SitemapFile::class,
            'run_interval' => Job\SitemapFile::DEFAULT_INTERVAL,
        ];

        $jobs[] = [
            'id'           => 'hard_delete_tickets',
            'worker_group' => 'hard_delete_tickets',
            'title'        => 'Hard Delete Tickets',
            'description'  => 'Processes tickets that were soft-deleted long ago and permanantly deletes them',
            'job_class'    => Job\HardDeleteTickets::class,
            'run_interval' => Job\HardDeleteTickets::DEFAULT_INTERVAL,
        ];

        $jobs[] = [
            'id'           => 'sendmail_queue',
            'worker_group' => 'sendmail_queue',
            'title'        => 'Sendmail Queue',
            'description'  => 'Attempts to send queued mail, or re-send fail mail',
            'job_class'    => Job\SendmailQueue::class,
            'run_interval' => Job\SendmailQueue::DEFAULT_INTERVAL,
        ];

        $jobs[] = [
            'id'           => 'heartbeat',
            'worker_group' => 'heartbeat',
            'title'        => 'Heartbeat',
            'description'  => 'Send heartbeat ping',
            'job_class'    => Job\Heartbeat::class,
            'run_interval' => Job\Heartbeat::DEFAULT_INTERVAL,
        ];

        $jobs[] = [
            'id'           => 'ticket_reminders',
            'worker_group' => 'ticket_reminders',
            'title'        => 'Ticket Reminders',
            'description'  => 'Sends reminders to users who created a ticket but have not yet validated their email',
            'job_class'    => Job\TicketReminders::class,
            'run_interval' => Job\TicketReminders::DEFAULT_INTERVAL,
        ];

        $jobs[] = [
            'id'           => 'ticket_follow_ups',
            'worker_group' => 'ticket_follow_ups',
            'title'        => 'Ticket Follow Ups',
            'description'  => 'Basically like macros except they run on a schedule, e.g. automatically add a reply after 3 days.',
            'job_class'    => Job\TicketFollowUps::class,
            'run_interval' => Job\TicketFollowUps::DEFAULT_INTERVAL,
        ];

        $jobs[] = [
            'id'           => 'kb_subscriptions',
            'worker_group' => 'kb_subscriptions',
            'title'        => 'KB Subscriptions',
            'description'  => 'Sends notifications to users who are subscribed to articles or categories',
            'job_class'    => Job\KbSubscriptions::class,
            'run_interval' => Job\KbSubscriptions::DEFAULT_INTERVAL,
        ];

        $jobs[] = [
            'id'           => 'news_subscriptions',
            'worker_group' => 'news_subscriptions',
            'title'        => 'News Subscriptions',
            'description'  => 'Sends notifications to users who are subscribed to news articles or news categories',
            'job_class'    => Job\NewsSubscriptions::class,
            'run_interval' => Job\NewsSubscriptions::DEFAULT_INTERVAL,
        ];

        $jobs[] = [
            'id'           => 'downloads_subscriptions',
            'worker_group' => 'downloads_subscriptions',
            'title'        => 'Downloads Subscriptions',
            'description'  => 'Sends notifications to users who are subscribed to downloads or downloads categories',
            'job_class'    => Job\DownloadsSubscriptions::class,
            'run_interval' => Job\DownloadsSubscriptions::DEFAULT_INTERVAL,
        ];

        $jobs[] = [
            'id'           => 'feedback_subscriptions',
            'worker_group' => 'feedback_subscriptions',
            'title'        => 'Feedback Subscriptions',
            'description'  => 'Sends notifications to users who are subscribed to feedback items',
            'job_class'    => Job\FeedbackSubscriptions::class,
            'run_interval' => Job\FeedbackSubscriptions::DEFAULT_INTERVAL,
        ];

        $jobs[] = [
            'id'           => 'move_blobs',
            'worker_group' => 'move_blobs',
            'title'        => 'Move Blobs',
            'description'  => 'When the storage mechanism is changed, this job moves existing blobs to the new mechanism a bit at a time',
            'job_class'    => Job\MoveBlobs::class,
            'run_interval' => Job\MoveBlobs::DEFAULT_INTERVAL,
        ];

        $jobs[] = [
            'id'           => 'delete_spam_tickets',
            'worker_group' => 'delete_spam_tickets',
            'title'        => 'Delete Spam Tickets',
            'description'  => 'Runs through old spammed tickets and deletes them',
            'job_class'    => Job\DeleteSpamTickets::class,
            'run_interval' => Job\DeleteSpamTickets::DEFAULT_INTERVAL,
        ];

        $jobs[] = [
            'id'           => 'agent_mode_ticket_reasssign',
            'worker_group' => 'agent_mode_ticket_reasssign',
            'title'        => 'Reassign tickets of vacation or deleted agents',
            'description'  => 'When an agent enters vacation mode or is deleted, we need to batch-update their tickets to unassigned',
            'job_class'    => Job\AgentModeTicketReassign::class,
            'run_interval' => Job\AgentModeTicketReassign::DEFAULT_INTERVAL,
        ];

        $jobs[] = [
            'id'           => 'ticket_escalations',
            'worker_group' => 'ticket_escalations',
            'title'        => 'Ticket Escalations',
            'description'  => 'Executes ticket escalations',
            'job_class'    => Job\TicketEscalations::class,
            'run_interval' => Job\TicketEscalations::DEFAULT_INTERVAL,
        ];

        $jobs[] = [
            'id'           => 'ticket_slas',
            'worker_group' => 'ticket_slas',
            'title'        => 'Ticket SLAs',
            'description'  => 'Updates ticket SLA status',
            'job_class'    => Job\TicketSlas::class,
            'run_interval' => Job\TicketSlas::DEFAULT_INTERVAL,
        ];

        $jobs[] = [
            'id'           => 'run_queued_tasks',
            'worker_group' => 'run_queued_tasks',
            'title'        => 'Run Queued Tasks',
            'description'  => 'Runs any general-purpose queued tasks',
            'job_class'    => Job\RunQueuedTasks::class,
            'run_interval' => Job\RunQueuedTasks::DEFAULT_INTERVAL,
        ];

        $jobs[] = [
            'id'           => 'task_reminders',
            'worker_group' => 'task_reminders',
            'title'        => 'Task Reminders',
            'description'  => 'Sends task reminder notifications',
            'job_class'    => Job\TaskReminders::class,
            'run_interval' => Job\TaskReminders::DEFAULT_INTERVAL,
        ];

        $jobs[] = [
            'id'           => 'locked_tickets_release',
            'worker_group' => 'locked_tickets_release',
            'title'        => 'Locked Tickets Release',
            'description'  => 'Release locked tickets',
            'job_class'    => Job\ReleaseLockedTickets::class,
            'run_interval' => Job\ReleaseLockedTickets::DEFAULT_INTERVAL,
        ];

        $jobs[] = [
            'id'           => 'job_queue',
            'worker_group' => 'job_queue',
            'title'        => 'Job Queue',
            'description'  => 'Processes the job queue',
            'job_class'    => Job\JobQueueExecutor::class,
            'run_interval' => Job\JobQueueExecutor::DEFAULT_INTERVAL,
        ];

        $jobs[] = [
            'id'           => 'job_supervisor',
            'worker_group' => 'job_queue',
            'title'        => 'Job Supervisor',
            'description'  => 'Supervises the job queue',
            'job_class'    => Job\JobQueueSupervisor::class,
            'run_interval' => Job\JobQueueSupervisor::DEFAULT_INTERVAL,
        ];

        $jobs[] = [
            'id'           => 'incoming_email_supervisor',
            'worker_group' => 'job_queue',
            'title'        => 'Incoming Email Supervisor',
            'description'  => 'Checks for errors and timeouts during incoming email logs',
            'job_class'    => Job\IncomingEmailSupervisor::class,
            'run_interval' => Job\IncomingEmailSupervisor::DEFAULT_INTERVAL,
        ];

        $jobs[] = [
            'id'           => 'update_view_counts',
            'worker_group' => 'update_view_counts',
            'title'        => 'Udpate View Counts',
            'description'  => 'Checks hit tracker for pageviews on content, and updates the view counter',
            'job_class'    => Job\UpdateViewCounts::class,
            'run_interval' => Job\UpdateViewCounts::DEFAULT_INTERVAL,
        ];

        $jobs[] = [
            'id'           => 'update_agents_online',
            'worker_group' => 'update_agents_online',
            'title'        => 'Update online agents',
            'description'  => 'Gathers info about who is online from agents and emits action alert.',
            'job_class'    => Job\UpdateAgentsOnline::class,
            'run_interval' => Job\UpdateAgentsOnline::DEFAULT_INTERVAL,
        ];

        $jobs[] = [
            'id'           => 'process_persisted_events',
            'worker_group' => 'process_persisted_events',
            'title'        => 'Process persisted notification events',
            'description'  => 'Process persisted notification events.',
            'job_class'    => Job\ProcessPersistedEvents::class,
            'run_interval' => Job\ProcessPersistedEvents::DEFAULT_INTERVAL,
        ];

        $jobs[] = [
            'id'           => 'system_alerts_process_events',
            'worker_group' => 'system_alerts',
            'title'        => 'Process new system alert events',
            'description'  => 'Process new system alert events to raise new and update existing incidents',
            'job_class'    => Job\ProcessSystemAlertEvents::class,
            'run_interval' => Job\ProcessSystemAlertEvents::DEFAULT_INTERVAL,
        ];

        $jobs[] = [
            'id'           => 'process_scheduled_reports',
            'worker_group' => 'scheduled_reports',
            'title'        => 'Process scheduled reports',
            'description'  => 'Process scheduled reports, then save results and send email with permalink',
            'job_class'    => Job\ProcessScheduledReports::class,
            'run_interval' => Job\ProcessScheduledReports::DEFAULT_INTERVAL,
        ];

        //------------------------------
        // Insert jobs
        //------------------------------

        $got_ids = array_map(function ($j) {
            return $j['id'];
        }, $jobs);
        $got_ids = "'".implode("', '", $got_ids)."'";
        $this->getDb()->executeUpdate("DELETE FROM worker_jobs WHERE id NOT IN ($got_ids)");

        $exist_id_map = $this->getDb()->fetchAllKeyValue('
			SELECT id, id
			FROM worker_jobs
		');

        foreach ($jobs as $job) {
            if (!empty($job['data'])) {
                $job['data'] = serialize($job['data']);
            } else {
                $job['data'] = 'a:0:{}';
            }

            $exist_id = isset($exist_id_map[$job['id']]) ? $exist_id_map[$job['id']] : null;
            if ($exist_id) {
                unset($job['id']);
                $this->getDb()->update('worker_jobs', $job, ['id' => $exist_id]);
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
