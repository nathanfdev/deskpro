<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
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
 * @subpackage WorkerProcess
 */

namespace Application\DeskPRO\WorkerProcess\Job;

use Application\DeskPRO\App;
use Application\DeskPRO\DBAL\Connection;

class CleanupHourly extends AbstractJob
{
    const DEFAULT_INTERVAL = 3600;

    public function run()
    {
        $this->doRun();
        App::getDb()->setIsolationDefault();
    }

    private function doRun()
    {
        $this->_cleanupDrafts();
        $this->_cleanupSessions();
        $this->_cleanupVisitors();
        $this->_cleanupChatBlobs();
        $this->_cleanupTempAttachments();
        $this->_cleanupTempData();
        $this->_cleanupPrefs();
        $this->_cleanupSendmailSources();
        $this->_cleanupEmailProcessLogs();
        $this->_cleanupEmailSources();
        $this->_cleanupTicketManagerLogs();
    }

    ####################################################################################################################

    private function _cleanupDrafts()
    {
        $datetime = date('Y-m-d H:i:s', time() - App::getSetting('core.drafts_lifetime'));
        $num = App::getDb()->executeUpdate("DELETE FROM drafts WHERE date_created < ?", array($datetime));

        if ($num) {
            $this->logStatus("Cleaned up $num drafts");
        }

        $datetime = date('Y-m-d H:i:s', time() - 28800);
        $num = App::getDb()->executeUpdate("DELETE FROM article_comments WHERE status = 'temp' AND date_created < ?", array($datetime));
        if ($num) {
            $this->logStatus("Cleaned up $num temp article comments");
        }

        $num = App::getDb()->executeUpdate("DELETE FROM download_comments WHERE status = 'temp' AND date_created < ?", array($datetime));
        if ($num) {
            $this->logStatus("Cleaned up $num temp download comments");
        }

        $num = App::getDb()->executeUpdate("DELETE FROM feedback_comments WHERE status = 'temp' AND date_created < ?", array($datetime));
        if ($num) {
            $this->logStatus("Cleaned up $num temp feedback comments");
        }

        $num = App::getDb()->executeUpdate("DELETE FROM news_comments WHERE status = 'temp' AND date_created < ?", array($datetime));
        if ($num) {
            $this->logStatus("Cleaned up $num temp news comments");
        }
    }

    ####################################################################################################################

    private function _cleanupSessions()
    {
        $datetime = date('Y-m-d H:i:s', time() - App::getSetting('core.sessions_lifetime'));
        $num = App::getDb()->executeUpdate("DELETE FROM sessions WHERE date_last < ?", array($datetime));

        if ($num) {
            $this->logStatus("Cleaned up $num stale sessions");
        }
    }

    ####################################################################################################################

    private function _cleanupVisitors()
    {
        $datesnip = date('Y-m-d H:i:s', time() - App::getSetting('core.visitor_cleanup_time'));
        $datesnip2 = date('Y-m-d H:i:s', time() - App::getSetting('core.visitor_cleanup_bogus_time'));

        // old
        $ids = App::getDb()->fetchAllCol("
            SELECT id
            FROM visitors
            WHERE date_last < ?
            LIMIT 1500
        ", array($datesnip));

        // bogus
        $ids = array_merge($ids, App::getDb()->fetchAllCol("
            SELECT id
            FROM visitors
            WHERE
                date_last < ?
                AND (
                    visitors.hint_hidden = 1
                    OR visitors.last_track_id IS NULL
                )
        ", array($datesnip2)));

        $ids = array_unique($ids);

        if ($ids) {
            $batch_ids = array_chunk($ids, 50);
            foreach ($batch_ids as $ids) {
                $num = App::getDb()->executeUpdate("
                    DELETE FROM visitors
                    WHERE id IN (?)
                ", array($ids), array(Connection::PARAM_INT_ARRAY));

                if ($num) {
                    $this->logStatus("Cleaned up $num stale visitors");
                }
            }
        }
    }

    ####################################################################################################################

    private function _cleanupChatBlobs()
    {
        // Clean up chat blocks
        $num = App::getOrm()->getRepository('DeskPRO:ChatBlock')->cleanupBlocks();

        if ($num) {
            $this->logStatus("Cleaned up $num stale chat blocks");
        }
    }

    ####################################################################################################################

    private function _cleanupTempAttachments()
    {
        $now = date('Y-m-d H:i:s');
        $datetime = date('Y-m-d H:i:s', strtotime('-6 hours'));

        $blob_ids = App::getDb()->fetchAllCol("
            SELECT id
            FROM blobs
            WHERE is_temp = 1 AND date_created < ?
            LIMIT 1000
        ", array($datetime, $now));

        $num = 0;
        foreach ($blob_ids as $blob_id) {
            try {
                $blob = App::getOrm()->find('DeskPRO:Blob', $blob_id);
                if ($blob) {
                    App::getContainer()->getBlobStorage()->deleteBlobRecord($blob);
                }
            } catch (\Exception $e) {}
            $num++;
        }

        if ($num) {
            $this->logStatus("Cleaned up $num temporary attachments");
        }
    }

    ####################################################################################################################

    private function _cleanupTempData()
    {
        $datetime = date('Y-m-d H:i:s', time());

        $num = App::getDb()->executeUpdate("
            DELETE FROM tmp_data
            WHERE date_expire < ?
        ", array($datetime));

        if ($num) {
            $this->logStatus("Cleaned up $num stale user temp data entries");
        }
    }

    ####################################################################################################################

    private function _cleanupPrefs()
    {
        $datetime = date('Y-m-d H:i:s', time());

        $num = App::getDb()->executeUpdate("
            DELETE FROM people_prefs
            WHERE date_expire < ?
        ", array($datetime));

        if ($num) {
            $this->logStatus("Cleaned up $num stale user preference entries");
        }
    }

    ####################################################################################################################

    private function _cleanupEmailSources()
    {
        // note: email_sources records are deleted via cascade

        if ($storetime = App::$container->getSetting('core.email_source_storetime')) {
            $timesnip = date('Y-m-d H:i:s', time() - $storetime);
            $blob_ids = App::getDb()->fetchAllCol("
                SELECT email_sources.blob_id
                FROM email_sources
                WHERE email_sources.date_created < ? AND email_sources.status = 'complete'
                ORDER BY email_sources.id ASC
                LIMIT 500
            ", array($timesnip));

            $count = $this->_deleteBlobsBatch($blob_ids);

            if ($count) {
                $this->logStatus("Cleaned up $count old email sources");
            }
        }

        if ($storetime = App::$container->getSetting('core.email_source_storetime_error')) {
            $timesnip = date('Y-m-d H:i:s', time() - $storetime);
            $blob_ids = App::getDb()->fetchAllCol("
                SELECT email_sources.blob_id
                FROM email_sources
                WHERE email_sources.date_created < ? AND email_sources.status = 'error'
                ORDER BY email_sources.id ASC
                LIMIT 500
            ", array($timesnip));

            $count = $this->_deleteBlobsBatch($blob_ids);

            if ($count) {
                $this->logStatus("Cleaned up $count old email sources marked as error");
            }
        }

        if ($storetime = App::$container->getSetting('core.email_source_storetime_rejection')) {
            $timesnip = date('Y-m-d H:i:s', time() - $storetime);
            $blob_ids = App::getDb()->fetchAllCol("
                SELECT email_sources.blob_id
                FROM email_sources
                WHERE email_sources.date_created < ? AND email_sources.status = 'rejected'
                ORDER BY email_sources.id ASC
                LIMIT 500
            ", array($timesnip));

            $count = $this->_deleteBlobsBatch($blob_ids);

            if ($count) {
                $this->logStatus("Cleaned up $count old email sources marked as rejected");
            }
        }
    }

    ####################################################################################################################

    private function _cleanupSendmailSources()
    {
        // note: sendmail_sources recs are deleted by cascade

        if ($storetime = App::$container->getSetting('core.sendmail_source_storetime')) {
            $timesnip = date('Y-m-d H:i:s', time() - $storetime);
            $blob_ids = App::getDb()->fetchAllCol("
                SELECT sendmail_sources.blob_id
                FROM sendmail_sources
                WHERE sendmail_sources.date_created < ? AND sendmail_sources.status = 'complete'
                ORDER BY sendmail_sources.id ASC
                LIMIT 500
            ", array($timesnip));

            $count = $this->_deleteBlobsBatch($blob_ids);

            if ($count) {
                $this->logStatus("Cleaned up $count old sendmail sources");
            }
        }

        if ($storetime = App::$container->getSetting('core.sendmail_source_storetime_error')) {
            $timesnip = date('Y-m-d H:i:s', time() - $storetime);
            $blob_ids = App::getDb()->fetchAllCol("
                SELECT sendmail_sources.blob_id
                FROM sendmail_sources
                WHERE sendmail_sources.date_created < ? AND sendmail_sources.status IN ('error', 'aborted')
                ORDER BY sendmail_sources.id ASC
                LIMIT 500
            ", array($timesnip));

            $count = $this->_deleteBlobsBatch($blob_ids);

            if ($count) {
                $this->logStatus("Cleaned up $count old sendmail sources");
            }
        }
    }

    ####################################################################################################################

    private function _cleanupEmailProcessLogs()
    {
        $storetime = App::$container->getSetting('core.ticket_manager_log_storetime');
        if (!$storetime) {
            return;
        }

        $timesnip = date('Y-m-d H:i:s', time() - $storetime);
        $count = 0;

        for ($i = 0; $i < 10; $i++) {
            $blob_ids = App::getDb()->fetchAllCol("
                SELECT email_sources.log_blob_id
                FROM email_sources
                WHERE email_sources.date_created < ? AND email_sources.log_blob_id IS NOT NULL
                ORDER BY email_sources.date_created DESC
                LIMIT 500
            ", array($timesnip));

            if ($blob_ids) {
                $count += $this->_deleteBlobsBatch($blob_ids);
            } else {
                break;
            }
        }

        if ($count) {
            $this->logStatus("Cleaned up $count old email process logs");
        }
    }

    ####################################################################################################################

    private function _cleanupTicketManagerLogs()
    {
        // note: ticket_proc_log recs are deleted via cascade

        $storetime = App::$container->getSetting('core.ticket_manager_log_storetime');
        if (!$storetime) {
            return;
        }

        $timesnip = date('Y-m-d H:i:s', time() - $storetime);
        $count = 0;

        for ($i = 0; $i < 10; $i++) {
            $blob_ids = App::getDb()->fetchAllCol("
                SELECT ticket_proc_log.blob_id
                FROM ticket_proc_log
                WHERE ticket_proc_log.date_created < ? AND ticket_proc_log.blob_id IS NOT NULL
                ORDER BY ticket_proc_log.date_created DESC
                LIMIT 500
            ", array($timesnip));

            if ($blob_ids) {
                $count += $this->_deleteBlobsBatch($blob_ids);
            } else {
                break;
            }
        }

        if ($count) {
            $this->logStatus("Cleaned up $count old ticket manager logs");
        }
    }

    ####################################################################################################################

    /**
     * @param array $blob_ids
     * @return int
     */
    private function _deleteBlobsBatch(array $blob_ids)
    {
        if (!$blob_ids) {
            return 0;
        }

        $blobs_info = App::getDb()->fetchAll("
            SELECT *
            FROM blobs
            WHERE id IN (?)
        ", array($blob_ids), array(\Doctrine\DBAL\Connection::PARAM_INT_ARRAY));

        if (!$blobs_info) {
            return 0;
        }

        $count = 0;
        $bs = App::getContainer()->getBlobStorage();
        foreach ($blobs_info as $b) {
            try {
                $bs->deleteBlobRow($b);
                ++$count;
            } catch (\Exception $e) {}
        }

        return $count;
    }
}
