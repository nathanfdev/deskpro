<?php

namespace Application\DeskPRO\WorkerProcess\Job;

use Application\DeskPRO\App;
use Carbon\Carbon;
use DeskPRO\Component\Util\ListUtils;
use DpSys\LowError\SystemErrorHandler;
use Symfony\Component\Finder\Finder;

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
        $this->_cleanupChatBlobs();
        $this->_cleanupTempAttachments();
        $this->_cleanupTempData();
        $this->_cleanupPrefs();
        $this->_cleanupSendmailSources();
        $this->_cleanupEmailProcessLogs();
        $this->_cleanupEmailAccountLogs();
        $this->_cleanupEmailSources();
        $this->_cleanupTicketManagerLogs();
        $this->_cleanupSavedForms();
        $this->_cleanHttpCacheDirs();
        $this->_cleanOauthTokens();
        $this->_cleanDashboardShortUrls();
    }

    //###################################################################################################################

    private function _cleanupDrafts()
    {
        $datetime = date('Y-m-d H:i:s', time() - App::getSetting('core.drafts_lifetime'));
        $num      = App::getDb()->executeUpdate('DELETE FROM drafts WHERE date_created < ?', [$datetime]);

        if ($num) {
            $this->logStatus("Cleaned up $num drafts");
        }

        $datetime = date('Y-m-d H:i:s', time() - 28800);
        $num      = App::getDb()->executeUpdate("DELETE FROM article_comments WHERE status = 'temp' AND date_created < ?", [$datetime]);
        if ($num) {
            $this->logStatus("Cleaned up $num temp article comments");
        }

        $num = App::getDb()->executeUpdate("DELETE FROM download_comments WHERE status = 'temp' AND date_created < ?", [$datetime]);
        if ($num) {
            $this->logStatus("Cleaned up $num temp download comments");
        }

        $num = App::getDb()->executeUpdate("DELETE FROM feedback_comments WHERE status = 'temp' AND date_created < ?", [$datetime]);
        if ($num) {
            $this->logStatus("Cleaned up $num temp feedback comments");
        }

        $num = App::getDb()->executeUpdate("DELETE FROM news_comments WHERE status = 'temp' AND date_created < ?", [$datetime]);
        if ($num) {
            $this->logStatus("Cleaned up $num temp news comments");
        }
    }

    //###################################################################################################################

    private function _cleanupSessions()
    {
        $datetime = date('Y-m-d H:i:s', time() - App::getSetting('core.sessions_lifetime'));
        $num      = App::getDb()->executeUpdate('DELETE FROM sessions WHERE date_last < ? LIMIT 1000', [$datetime]);

        if ($num) {
            $this->logStatus("Cleaned up $num stale sessions");
        }
    }

    //###################################################################################################################

    private function _cleanupChatBlobs()
    {
        // Clean up chat blocks
        $num = App::getOrm()->getRepository('DeskPRO:ChatBlock')->cleanupBlocks();

        if ($num) {
            $this->logStatus("Cleaned up $num stale chat blocks");
        }
    }

    //###################################################################################################################

    private function _cleanupTempAttachments()
    {
        // this is temporary return until we will find why blobs are still is_temp = 1

        return;

        $datetime = date('Y-m-d H:i:s', strtotime('-6 hours'));

        $blob_ids = App::getDb()->fetchAllCol('
            SELECT id
            FROM blobs
            WHERE is_temp = 1 AND date_created < ?
            LIMIT 1000
        ', [$datetime]);

        $num = 0;
        foreach ($blob_ids as $blob_id) {
            try {
                $blob = App::getOrm()->find('DeskPRO:Blob', $blob_id);
                if ($blob) {
                    App::getContainer()->getBlobStorage()->deleteBlobRecord($blob);
                }
            } catch (\Exception $e) {
                SystemErrorHandler::logException($e);
            }
            ++$num;
        }

        if ($num) {
            $this->logStatus("Cleaned up $num temporary attachments");
        }
    }

    //###################################################################################################################

    private function _cleanupTempData()
    {
        $datetime = date('Y-m-d H:i:s', time());

        $num = App::getDb()->executeUpdate('
            DELETE FROM tmp_data
            WHERE date_expire < ?
        ', [$datetime]);

        if ($num) {
            $this->logStatus("Cleaned up $num stale user temp data entries");
        }
    }

    //###################################################################################################################

    private function _cleanupPrefs()
    {
        $datetime = date('Y-m-d H:i:s', time());

        $num = App::getDb()->executeUpdate('
            DELETE FROM people_prefs
            WHERE date_expire < ?
        ', [$datetime]);

        if ($num) {
            $this->logStatus("Cleaned up $num stale user preference entries");
        }
    }

    //###################################################################################################################

    private function _cleanupEmailSources()
    {
        $db = $this->getContainer()->getDb();

        // note: email_sources records are deleted via cascade

        if ($storetime = $this->getContainer()->getSetting('core.email_source_storetime')) {
            $timesnip = date('Y-m-d H:i:s', time() - $storetime);
            $blob_ids = ListUtils::filterOutFalsey(ListUtils::flatten($db->fetchAll("
                SELECT email_sources.blob_id, email_sources.log_blob_id
                FROM email_sources
                WHERE email_sources.date_created < ? AND email_sources.status = 'complete'
                ORDER BY email_sources.id ASC
                LIMIT 500
            ", [$timesnip])));

            $count = $this->_deleteBlobsBatch($blob_ids);

            if ($count) {
                $this->logStatus("Cleaned up $count old email sources");
            }
        }

        if ($storetime = $this->getContainer()->getSetting('core.email_source_storetime_error')) {
            $timesnip = date('Y-m-d H:i:s', time() - $storetime);
            $blob_ids = ListUtils::filterOutFalsey(ListUtils::flatten($db->fetchAll("
                SELECT email_sources.blob_id, email_sources.log_blob_id
                FROM email_sources
                WHERE email_sources.date_created < ? AND email_sources.status = 'error'
                ORDER BY email_sources.id ASC
                LIMIT 500
            ", [$timesnip])));

            $count = $this->_deleteBlobsBatch($blob_ids);

            if ($count) {
                $this->logStatus("Cleaned up $count old email sources marked as error");
            }
        }

        if ($storetime = $this->getContainer()->getSetting('core.email_source_storetime_rejection')) {
            $timesnip = date('Y-m-d H:i:s', time() - $storetime);
            $blob_ids = ListUtils::filterOutFalsey(ListUtils::flatten($db->fetchAll("
                SELECT email_sources.blob_id, email_sources.log_blob_id
                FROM email_sources
                WHERE email_sources.date_created < ? AND email_sources.status = 'rejected'
                ORDER BY email_sources.id ASC
                LIMIT 500
            ", [$timesnip])));

            $count = $this->_deleteBlobsBatch($blob_ids);

            if ($count) {
                $this->logStatus("Cleaned up $count old email sources marked as rejected");
            }
        }
    }

    //###################################################################################################################

    /**
     * Cluanup old email_account_logs.
     */
    private function _cleanupEmailAccountLogs()
    {
        $db = $this->getContainer()->get('database_connection');

        $blob_ids = $db->fetchAllCol('
            SELECT blob_id
            FROM email_account_logs
            WHERE num_emails = 0 AND date_created < ?
        ', [Carbon::now()->subHours(1)->toDateTimeString()]);

        if ($blob_ids) {
            $count = $this->_deleteBlobsBatch($blob_ids);
            $this->logStatus("Cleaned up $count email account logs with no emails from last hour");
        }

        $days     = intval($this->getContainer()->getSetting('email_log.cleanup.delay_days')) ?: 0;
        $timesnip = date('Y-m-d H:i:s', time() - ($days * 86400));
        $blob_ids = $db->fetchAllCol('
            SELECT blob_id
            FROM email_account_logs
            WHERE date_created < ?
        ', [$timesnip]);

        if ($blob_ids) {
            $count = $this->_deleteBlobsBatch($blob_ids);
            $this->logStatus("Cleaned up $count email account logs older than $days days");
        }
    }

    private function _cleanupSendmailSources()
    {
        $db = $this->getContainer()->getDb();

        // note: sendmail_sources recs are deleted by cascade

        if ($storetime = $this->getContainer()->getSetting('core.sendmail_source_storetime')) {
            $timesnip = date('Y-m-d H:i:s', time() - $storetime);
            $blob_ids = ListUtils::filterOutFalsey(ListUtils::flatten($db->fetchAll("
                SELECT sendmail_sources.blob_id, sendmail_sources.log_blob_id
                FROM sendmail_sources
                WHERE sendmail_sources.date_created < ? AND sendmail_sources.status = 'complete'
                ORDER BY sendmail_sources.id ASC
                LIMIT 500
            ", [$timesnip])));

            $count = $this->_deleteBlobsBatch($blob_ids);

            if ($count) {
                $this->logStatus("Cleaned up $count old sendmail sources");
            }
        }

        if ($storetime = $this->getContainer()->getSetting('core.sendmail_source_storetime_error')) {
            $timesnip = date('Y-m-d H:i:s', time() - $storetime);
            $blob_ids = ListUtils::filterOutFalsey(ListUtils::flatten($db->fetchAll("
                SELECT sendmail_sources.blob_id, sendmail_sources.log_blob_id
                FROM sendmail_sources
                WHERE sendmail_sources.date_created < ? AND sendmail_sources.status IN ('error', 'aborted')
                ORDER BY sendmail_sources.id ASC
                LIMIT 500
            ", [$timesnip])));

            $count = $this->_deleteBlobsBatch($blob_ids);

            if ($count) {
                $this->logStatus("Cleaned up $count old sendmail sources");
            }
        }
    }

    //###################################################################################################################

    private function _cleanHttpCacheDirs()
    {
        // don't run on cloud
        if (!defined('DPC_IS_CLOUD')) {
            // find the cache dir
            if (defined('DP_CACHE_DIR')) {
                $cache_dir = DP_CACHE_DIR;
            } else {
                $cache_dir = DP_ROOT.'/sys/cache';
            }

            // gather the http_cache dirs from dev and prod
            $environment_dirs = [];
            $environments     = ['dev', 'prod'];
            foreach ($environments as $env) {
                $dir = $cache_dir.'/portal/'.$env.'/http_cache';
                if (is_dir($dir)) {
                    $environment_dirs[] = $dir;
                }
            }

            if (count($environment_dirs) > 0) {
                // delete all files that were last modified more than 7 days ago
                $file_finder = new Finder();
                $file_finder->files()->in($environment_dirs)->date('before 7 days ago');
                foreach ($file_finder as $deletable_file) {
                    unlink($deletable_file);
                }

                // delete all empty directories
                // do this 3 times because the depth of empty directories can be up to 3, and many won't be empty on first pass
                for ($i = 0; $i < 3; ++$i) {
                    $dir_finder = new Finder();
                    $dir_finder->directories()->in($environment_dirs);

                    /* @var \Symfony\Component\Finder\SplFileInfo $dir_name */
                    $maybe_delete_dirs = [];
                    foreach ($dir_finder as $dir_name) {
                        // cannot delete here, as it might mess up the $finder iterator
                        $maybe_delete_dirs[] = $dir_name->getRealPath();
                    }

                    foreach ($maybe_delete_dirs as $dir) {
                        $iterator = new \FilesystemIterator($dir);
                        if (!$iterator->valid()) {
                            rmdir($dir);
                        }
                    }
                }
            }
        }
    }

    //###################################################################################################################

    private function _cleanupEmailProcessLogs()
    {
        $storetime = App::$container->getSetting('core.ticket_manager_log_storetime');
        if (!$storetime) {
            return;
        }

        $timesnip = date('Y-m-d H:i:s', time() - $storetime);
        $count    = 0;

        for ($i = 0; $i < 10; ++$i) {
            $blob_ids = App::getDb()->fetchAllCol('
                SELECT email_sources.log_blob_id
                FROM email_sources
                WHERE email_sources.date_created < ? AND email_sources.log_blob_id IS NOT NULL
                ORDER BY email_sources.date_created DESC
                LIMIT 500
            ', [$timesnip]);

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

    //###################################################################################################################

    private function _cleanupTicketManagerLogs()
    {
        // note: ticket_proc_log recs are deleted via cascade

        $storetime = App::$container->getSetting('core.ticket_manager_log_storetime');
        if (!$storetime) {
            return;
        }

        $timesnip = date('Y-m-d H:i:s', time() - $storetime);
        $count    = 0;

        for ($i = 0; $i < 10; ++$i) {
            $blob_ids = App::getDb()->fetchAllCol('
                SELECT ticket_proc_log.blob_id
                FROM ticket_proc_log
                WHERE ticket_proc_log.date_created < ? AND ticket_proc_log.blob_id IS NOT NULL
                ORDER BY ticket_proc_log.date_created DESC
                LIMIT 500
            ', [$timesnip]);

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

    //###################################################################################################################

    private function _cleanupSavedForms()
    {
        $datetime = date('Y-m-d H:i:s', time());
        $count    = App::getDb()->executeUpdate('DELETE FROM saved_forms WHERE date_expires < ?', [$datetime]);

        if ($count) {
            $this->logStatus("Cleaned up $count old saved forms");
        }
    }

    //###################################################################################################################

    private function _cleanOauthTokens()
    {
        $db = App::getDb();

        // remove expired auth codes
        $result = $db->executeUpdate('DELETE FROM oauth_codes WHERE expires_at < ?', [time()]);
        $this->logStatus("Removed $result items from OAuthCode storage.");

        // remove expired refresh tokens
        $result = $db->executeUpdate('DELETE FROM oauth_refresh_tokens WHERE expires_at < ?', [time()]);
        $this->logStatus("Removed $result items from OAuthRefreshToken storage.");

        // remove expired access tokens
        $result = $db->executeUpdate('DELETE o FROM oauth_access_tokens o JOIN api_token a ON o.api_token_id = a.id WHERE a.date_expires < ?', [date('c')]);
        $this->logStatus("Removed $result items from OAuthAccessToken storage.");
    }

    /**
     * @param array $blob_ids
     *
     * @return int
     */
    private function _deleteBlobsBatch(array $blob_ids)
    {
        if (!$blob_ids) {
            return 0;
        }

        $blobs_info = App::getDb()->fetchAll('
            SELECT *
            FROM blobs
            WHERE id IN (?)
        ', [$blob_ids], [\Doctrine\DBAL\Connection::PARAM_INT_ARRAY]);

        if (!$blobs_info) {
            return 0;
        }

        $count = 0;
        $bs    = App::getContainer()->getBlobStorage();
        foreach ($blobs_info as $b) {
            try {
                $bs->deleteBlobRow($b);
                ++$count;
            } catch (\Exception $e) {
            }
        }

        return $count;
    }

    private function _cleanDashboardShortUrls()
    {
        $datetime = date('Y-m-d H:i:s', time());
        $count    = App::getDb()->executeUpdate('DELETE FROM report_dashboard_shareable_short_url WHERE date_expire < ?', [$datetime]);

        if ($count) {
            $this->logStatus("Cleaned up $count old dashboard short urls");
        }
    }
}
