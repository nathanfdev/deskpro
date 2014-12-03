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
 * @subpackage WorkerProcess
 */

namespace Application\DeskPRO\WorkerProcess\Job;

use Application\DeskPRO\App;

class CleanupDaily extends AbstractJob
{
    const DEFAULT_INTERVAL = 86400;

    public function run()
    {
        $this->doRun();
        App::getDb()->setIsolationDefault();
    }

    private function doRun()
    {
        #------------------------------
        # email sources
        #------------------------------

        $source_ids = array();
        $blob_ids = array();

        if (App::getSetting('core.email_source_storetime')) {
            $snip = date('Y-m-d H:i:s', time() - App::getSetting('core.email_source_storetime'));
            $email_sources = App::getDb()->fetchAll("
                SELECT email_sources.id, email_sources.blob_id
                FROM email_sources
                WHERE email_sources.date_created < ? AND email_sources.status = 'complete'
                ORDER BY email_sources.id ASC
                LIMIT 1000
            ", array($snip));

            $num = 0;
            foreach ($email_sources as $source) {

                try {
                    $blob = App::getOrm()->find('DeskPRO:Blob', $source['blob_id']);
                    if ($blob) {
                        App::getContainer()->getBlobStorage()->deleteBlobRecord($blob);
                    }
                } catch (\Exception $e) {}

                $source_ids[] = $source['id'];
                $blob_ids[] = $source['blob_id'];
                $num++;
            }

            if ($num) {
                $this->logStatus("Cleaned up $num stale email sources");
            }
        }

        if (App::getSetting('core.email_source_storetime_error')) {
            $snip = date('Y-m-d H:i:s', time() - App::getSetting('core.email_source_storetime'));
            $email_sources = App::getDb()->fetchAll("
                SELECT email_sources.id, email_sources.blob_id
                FROM email_sources
                WHERE email_sources.date_created < ? AND email_sources.status = 'error' AND email_sources.error_code IN ('server_error', 'timeout')
                ORDER BY email_sources.id ASC
                LIMIT 1000
            ", array($snip));

            $num = 0;
            foreach ($email_sources as $source) {
                try {
                    $blob = App::getOrm()->find('DeskPRO:Blob', $source['blob_id']);
                    if ($blob) {
                        App::getContainer()->getBlobStorage()->deleteBlobRecord($blob);
                    }
                } catch (\Exception $e) {}

                $source_ids[] = $source['id'];
                $blob_ids[] = $source['blob_id'];

                $num++;
            }

            if ($num) {
                $this->logStatus("Cleaned up $num stale email sources");
            }
        }

        if (App::getSetting('core.email_source_storetime_rejection')) {
            $snip = date('Y-m-d H:i:s', time() - App::getSetting('core.email_source_storetime'));
            $email_sources = App::getDb()->fetchAll("
                SELECT email_sources.id, email_sources.blob_id
                FROM email_sources
                WHERE email_sources.date_created < ? AND email_sources.status = 'error' AND email_sources.error_code NOT IN ('server_error', 'timeout')
                ORDER BY email_sources.id ASC
                LIMIT 1000
            ", array($snip));

            $num = 0;
            foreach ($email_sources as $source) {
                try {
                    $blob = App::getOrm()->find('DeskPRO:Blob', $source['blob_id']);
                    if ($blob) {
                        App::getContainer()->getBlobStorage()->deleteBlobRecord($blob);
                    }
                } catch (\Exception $e) {}

                $source_ids[] = $source['id'];
                $blob_ids[] = $source['blob_id'];

                $num++;
            }

            if ($num) {
                $this->logStatus("Cleaned up $num stale email sources");
            }
        }

        if ($source_ids) {
            App::getDb()->deleteIn('email_sources', $source_ids);
        }
        if ($blob_ids) {
            App::getDb()->deleteIn('blobs', $blob_ids);
        }

        #------------------------------
        # sendmail log
        #------------------------------

        $days = App::getSetting('core.store_sent_mail_days');

        if (!$days) {
            $blob_ids = App::getDb()->fetchAllCol("
                SELECT blob_id FROM sendmail_queue
                WHERE has_sent = 1 AND blob_id IS NOT NULL
            ");
            if ($blob_ids) {
                $blobs = App::getOrm()->getRepository('DeskPRO:Blob')->getByIds($blob_ids);
                foreach ($blobs as $blob) {
                    try {
                        App::getContainer()->getBlobStorage()->deleteBlobRecord($blob);
                    } catch (\Exception $e) {}
                }
            }

            $num = App::getDb()->executeUpdate("
                DELETE FROM sendmail_queue
                WHERE has_sent = 1
            ");
        } else {
            $datetime = date('Y-m-d H:i:s', strtotime("-$days days"));
            $datetime2 = date('Y-m-d H:i:s', strtotime("-" .($days * 5) ." days"));

            $blob_ids = App::getDb()->fetchAllCol("
                SELECT blob_id FROM sendmail_queue
                WHERE ((has_sent = 1 AND date_sent < ?) OR date_sent < ?) AND blob_id IS NOT NULL
            ", array($datetime, $datetime2));

            if ($blob_ids) {
                $blobs = App::getOrm()->getRepository('DeskPRO:Blob')->getByIds($blob_ids);
                foreach ($blobs as $blob) {
                    try {
                        App::getContainer()->getBlobStorage()->deleteBlobRecord($blob);
                    } catch (\Exception $e) {}
                }
            }

            $num = App::getDb()->executeUpdate("
                DELETE FROM sendmail_queue
                WHERE (has_sent = 1 AND date_sent < ?) OR date_sent < ?
            ", array($datetime, $datetime2));
        };

        if ($num) {
            $this->logStatus("Cleaned up $num sent emails");
        }

        #------------------------------
        # Remove old email process logs
        #------------------------------

        $datecut = date('Y-m-d H:i:s', time() - 1728000); // 20 days
        $email_sources = App::getDb()->fetchAll("
            SELECT email_sources.id, email_sources.log_blob_id
            FROM email_sources
            WHERE email_sources.date_created < ? AND email_sources.log_blob_id IS NOT NULL
            ORDER BY email_sources.date_created DESC
            LIMIT 2000
        ", array($datecut));

        if ($email_sources) {
            $blob_ids   = array_map(function ($r) { return $r['log_blob_id']; }, $email_sources);
            $blobs = App::$container->getEm()->getRepository('DeskPRO:Blob')->getByIds($blob_ids);
            foreach ($email_sources as $source) {
                if (isset($blobs[$source['log_blob_id']])) {
                    try {
                        App::$container->getBlobStorage()->deleteBlobRecord($blobs[$source['log_blob_id']]);
                    } catch (\Exception $e) {}
                }
            }

            $this->logStatus("Cleaned up " . count($blobs) . " email source process logs");
            unset($blobs);
        }

        #------------------------------
        # log_items
        #------------------------------

        $last_id = App::getDb()->fetchColumn("SELECT id FROM log_items ORDER BY id DESC LIMIT 1");
        if ($last_id) {
            $delete_before_id = $last_id - 25000; // approx 10 days worth of cron logs
            $num = App::getDb()->executeUpdate("DELETE FROM log_items WHERE id < $delete_before_id");

            if ($num) {
                $this->logStatus("Cleaned up $num cron log items");
            }
        }

        #------------------------------
        # result caches
        #------------------------------

        $datecut = date('Y-m-d H:i:s', time() - 86400);
        $num = App::getDb()->executeUpdate("
            DELETE FROM result_cache
            WHERE date_created < ?
        ", array($datecut));

        if ($num) {
            $this->logStatus("Cleaned up $num old result caches");
        }

        #------------------------------
        # Task queue logs Items
        #------------------------------

        $cutoff = 86400 * 14; // 15 days
        $datecut = date('Y-m-d H:i:s', time() - $cutoff);
        $num = App::getDb()->executeUpdate("
            DELETE FROM task_queue
            WHERE status = 'completed' AND date_completed < ?
        ", array($datecut));

        if ($num) {
            $this->logStatus("Cleaned up $num task queue logs");
        }

        #------------------------------
        # ref_reserve
        #------------------------------

        $cutoff = 86400; // 1 day
        $datecut = date('Y-m-d H:i:s', time() - $cutoff);
        $num = App::getDb()->executeUpdate("
            DELETE FROM ref_reserve
            WHERE date_created < ?
        ", array($datecut));

        if ($num) {
            $this->logStatus("Cleaned up $num ref_reserve records");
        }

        #------------------------------
        # whitelisted IPs
        #------------------------------

        if (App::getSetting('agent.ip_security.enabled')) {
            $cutoff = App::getSetting('agent.ip_security.whitelist_lifetime');
            $datecut = date('Y-m-d H:i:s', time() - $cutoff);
            $num = App::getDb()->executeUpdate("
                DELETE FROM white_listed_ips
                WHERE date_created < ?
            ", array($datecut));

            if ($num) {
                $this->logStatus("Cleaned up $num white_listed_ips records");
            }
        }

        #------------------------------
        # Temp files
        #------------------------------

        // 50 days, sanity check
        $min_time = time() - 4320000;

        $cleanup_list = array();

        $tmpdir = dp_get_tmp_dir();
        $tmpdir_swift = dp_get_tmp_dir() . DIRECTORY_SEPARATOR . 'swiftmailer-cache';

        if (is_dir($tmpdir) && is_readable($tmpdir)) {
            $dir = dir($tmpdir);

            while ($f = $dir->read()) {
                if ($f == '.' || $f == '..') continue;

                $f_path  = $dir->path . DIRECTORY_SEPARATOR . $f;
                $mtime   = @filemtime($f_path);

                if (!$mtime || $mtime < $min_time) {
                    continue;
                }

                $do_cleanup = false;

                // Temp email files are dpm* and eml*
                if (is_file($f_path) && (strpos($f, 'dpm') === 0 || strpos($f, 'eml') === 0) && $mtime < strtotime('-3 days')) {
                    $do_cleanup = true;

                // Temp files created for ticket debug export are dpd
                } elseif (is_dir($f_path) && strpos($f, 'dpd') === 0 && $mtime < strtotime('-1 day')) {
                    $do_cleanup = true;

                // Unzipped distros created during upgrade
                } elseif (is_dir($f_path) && is_file($f_path . DIRECTORY_SEPARATOR . 'config.new.php') && $mtime < strtotime('-1 day')) {
                    $do_cleanup = true;
                }

                if ($do_cleanup) {
                    $cleanup_list[] = $f_path;
                }
            }

            $dir->close();
        }

        if (is_dir($tmpdir_swift) && is_readable($tmpdir_swift)) {
            $dir = dir($tmpdir_swift);

            // Swiftmailer may write to the fs sometimes
            while ($f = $dir->read()) {
                if ($f == '.' || $f == '..' || strlen($f) != 32) continue;

                $f_path  = $dir->path . DIRECTORY_SEPARATOR . $f;
                $mtime   = @filemtime($f_path);

                if (!$mtime || $mtime > strtotime('-4 days') || !is_dir($f_path)) {
                    continue;
                }

                $cleanup_list[] = $f_path;
            }

            $dir->close();
        }

        if ($cleanup_list) {
            $file_util = new \Symfony\Component\Filesystem\Filesystem();
            $x = 0;
            foreach ($cleanup_list as $f) {
                try {
                    $file_util->remove($f);
                    $x++;
                } catch (\Exception $e) {}
            }

            $this->logStatus("Cleaned up $x of " . count($cleanup_list) . " old files");
        }
    }
}
