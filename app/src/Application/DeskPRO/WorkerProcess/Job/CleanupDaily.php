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
use Application\DeskPRO\Entity\TmpData;

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
        # Agent alerts
        #------------------------------

        if ($maxage = App::getSetting('agent.alerts_cleanup_time_always')) {
            $datetime = date('Y-m-d H:i:s', time() - $maxage);
            $num = App::getDb()->executeUpdate("
				DELETE FROM agent_alerts
				WHERE date_created < ?
			", array($datetime));

            if ($num) {
                $this->logStatus("Cleaned up $num agent alerts");
            }
        }

        if ($maxage = App::getSetting('agent.alerts_cleanup_time')) {
            $datetime = date('Y-m-d H:i:s', time() - $maxage);
            $num = App::getDb()->executeUpdate("
				DELETE FROM agent_alerts
				WHERE date_created < ? AND is_dismissed = 1
			", array($datetime));

            if ($num) {
                $this->logStatus("Cleaned up $num dismissed agent alerts");
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
        # cleanup blobs_storage with no blobs record
        #------------------------------

        $num = App::getDb()->executeUpdate("
            DELETE blobs_storage
            FROM blobs_storage
            LEFT JOIN blobs ON blobs.id = blobs_storage.blob_id
            WHERE blobs.id IS NULL
        ");

        if ($num) {
            $this->logStatus("Cleaned up $num blobs_storage records without blobs");
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

        #------------------------------
        # Clean old exports
        #------------------------------

        $q = App::getOrm()->createQuery('
            SELECT t FROM DeskPRO:TmpData t
            WHERE t.name = :name and t.date_expire < :date
        ')->setParameters(array(
            'name' => 'csv_export.file',
            'date' => date('Y-m-d H:i:s'),
        ));
        $num = 0;

        foreach ($q->getResult() as $entry) {
            /** @var $entry TmpData */
            $file = $entry->getData('file');
            if (!file_exists($file)) continue;

            unlink($file);
            App::getOrm()->remove($entry);
            $num++;
        }

        if ($num) {
            App::getOrm()->flush();
            $this->logStatus("Cleaned up $num old exports");
        }
    }
}
