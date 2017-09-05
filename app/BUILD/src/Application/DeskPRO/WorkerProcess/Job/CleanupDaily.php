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

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\WorkerProcess\Job;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\TmpData;
use DeskPRO\Bundle\UpdateBundle\Service\UpdateCleanup;
use Exception;
use Monolog\Logger;
use Symfony\Bridge\Monolog\Handler\DebugHandler;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

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
        $this->_cleanupLogItems();
        $this->_cleanupAgentAlerts();
        $this->_cleanupResultCaches();
        $this->_cleanupTaskQueueLogsItems();
        $this->_cleanupRefReserve();
        $this->_cleanupWhiteListIPs();
        $this->_cleanupTempFiles();
        $this->_cleanupOldExports();
        $this->_truncateBigIdTables();
        $this->_cleanupRateLimitLogs();
        $this->_cleanupOldBuilds();
        $this->_cleanHttpCache();
    }

    private function _cleanupLogItems()
    {
        $lastId = App::getDb()->fetchColumn('SELECT id FROM log_items ORDER BY id DESC LIMIT 1');
        if ($lastId) {
            $deleteBeforeId = $lastId - 25000; // approx 10 days worth of cron logs
            $num            = App::getDb()->executeUpdate("DELETE FROM log_items WHERE id < $deleteBeforeId");

            if ($num) {
                $this->logStatus("Cleaned up $num cron log items");
            }
        }
    }

    private function _cleanupAgentAlerts()
    {
        if ($maxAge = App::getSetting('agent.alerts_cleanup_time_always')) {
            $datetime = date('Y-m-d H:i:s', time() - $maxAge);
            $num      = App::getDb()->executeUpdate('
				DELETE FROM agent_alerts
				WHERE date_created < ?
			', [$datetime]);

            if ($num) {
                $this->logStatus("Cleaned up $num agent alerts");
            }
        }

        if ($maxAge = App::getSetting('agent.alerts_cleanup_time')) {
            $datetime = date('Y-m-d H:i:s', time() - $maxAge);
            $num      = App::getDb()->executeUpdate('
				DELETE FROM agent_alerts
				WHERE date_created < ? AND is_dismissed = 1
			', [$datetime]);

            if ($num) {
                $this->logStatus("Cleaned up $num dismissed agent alerts");
            }
        }
    }

    private function _cleanupResultCaches()
    {
        $dateCut = date('Y-m-d H:i:s', time() - 86400);
        $num     = App::getDb()->executeUpdate('
            DELETE FROM result_cache
            WHERE date_created < ?
        ', [$dateCut]);

        if ($num) {
            $this->logStatus("Cleaned up $num old result caches");
        }
    }

    private function _cleanupTaskQueueLogsItems()
    {
        $cutoff  = 86400 * 14; // 15 days
        $dateCut = date('Y-m-d H:i:s', time() - $cutoff);
        $num     = App::getDb()->executeUpdate("
            DELETE FROM task_queue
            WHERE status = 'completed' AND date_completed < ?
        ", [$dateCut]);

        if ($num) {
            $this->logStatus("Cleaned up $num task queue logs");
        }
    }

    private function _cleanupRefReserve()
    {
        $cutoff  = 86400; // 1 day
        $dateCut = date('Y-m-d H:i:s', time() - $cutoff);
        $num     = App::getDb()->executeUpdate('
            DELETE FROM ref_reserve
            WHERE date_created < ?
        ', [$dateCut]);

        if ($num) {
            $this->logStatus("Cleaned up $num ref_reserve records");
        }
    }

    private function _cleanupWhiteListIPs()
    {
        if (App::getSetting('agent.ip_security.enabled')) {
            $cutoff  = App::getSetting('agent.ip_security.whitelist_lifetime');
            $dateCut = date('Y-m-d H:i:s', time() - $cutoff);
            $num     = App::getDb()->executeUpdate('
                DELETE FROM white_listed_ips
                WHERE date_created < ?
            ', [$dateCut]);

            if ($num) {
                $this->logStatus("Cleaned up $num white_listed_ips records");
            }
        }
    }

    private function _cleanupTempFiles()
    {
        //------------------------------
        // Temp files
        //------------------------------

        // 50 days, sanity check
        $minTime = time() - 4320000;

        $cleanupList = [];

        $tmpDir      = dp_get_tmp_dir();
        $tmpDirSwift = dp_get_tmp_dir().DIRECTORY_SEPARATOR.'swiftmailer-cache';

        if (is_dir($tmpDir) && is_readable($tmpDir)) {
            $dir = dir($tmpDir);

            while ($f = $dir->read()) {
                if ($f == '.' || $f == '..') {
                    continue;
                }

                $fPath = $dir->path.DIRECTORY_SEPARATOR.$f;
                $mTime = @filemtime($fPath);

                if (!$mTime || $mTime < $minTime) {
                    continue;
                }

                $doCleanup = false;

                // Temp email files are dpm* and eml*
                if (is_file($fPath) && (strpos($f, 'dpm') === 0 || strpos($f, 'eml') === 0) && $mTime < strtotime('-3 days')) {
                    $doCleanup = true;

                    // Temp files created for ticket debug export are dpd
                } elseif (is_dir($fPath) && strpos($f, 'dpd') === 0 && $mTime < strtotime('-1 day')) {
                    $doCleanup = true;

                    // Unzipped distros created during upgrade
                } elseif (is_dir($fPath) && is_file($fPath.DIRECTORY_SEPARATOR.'config.new.php') && $mTime < strtotime('-1 day')) {
                    $doCleanup = true;
                }

                if ($doCleanup) {
                    $cleanupList[] = $fPath;
                }
            }

            $dir->close();
        }

        if (is_dir($tmpDirSwift) && is_readable($tmpDirSwift)) {
            $dir = dir($tmpDirSwift);

            // Swiftmailer may write to the fs sometimes
            while ($f = $dir->read()) {
                if ($f == '.' || $f == '..' || strlen($f) != 32) {
                    continue;
                }

                $fPath = $dir->path.DIRECTORY_SEPARATOR.$f;
                $mTime = @filemtime($fPath);

                if (!$mTime || $mTime > strtotime('-4 days') || !is_dir($fPath)) {
                    continue;
                }

                $cleanupList[] = $fPath;
            }

            $dir->close();
        }

        if ($cleanupList) {
            $fileUtil = new Filesystem();
            $x        = 0;
            foreach ($cleanupList as $f) {
                try {
                    $fileUtil->remove($f);
                    ++$x;
                } catch (Exception $e) {
                }
            }

            $this->logStatus("Cleaned up $x of ".count($cleanupList).' old files');
        }
    }

    private function _cleanupOldExports()
    {
        $q = App::getOrm()->createQuery('
            SELECT t FROM DeskPRO:TmpData t
            WHERE t.name = :name and t.date_expire < :date
        ')->setParameters([
            'name' => 'csv_export.file',
            'date' => date('Y-m-d H:i:s'),
        ]);
        $num = 0;

        foreach ($q->getResult() as $entry) {
            /* @var $entry TmpData */
            $file = $entry->getData('file');
            if (!file_exists($file)) {
                continue;
            }

            @unlink($file);
            @unlink($file.'.zip');
            App::getOrm()->remove($entry);
            ++$num;
        }

        if ($num) {
            App::getOrm()->flush();
            $this->logStatus("Cleaned up $num old exports");
        }
    }

    private function _truncateBigIdTables()
    {
        //------------------------------
        // Truncate tables approaching max INT size
        //------------------------------

        // - These tables are continuously filled + cleaned up
        // but the IDs arent recycled.
        // - On very big helpdesks they might overflow INT,
        // so we're just clearing them.
        // - Ideally we just use bigint but we might be using PHP
        // treating these as ints, so that'd screw up with bigints on 32b (i.e. $bigint + 1 wouldnt work)

        $db = App::getDb();

        $tables = [
            'agent_alerts',
        ];

        $threshold = 2145000000;
        foreach ($tables as $t) {
            $tableMaxId = $db->fetchColumn("SELECT id FROM $t ORDER BY id DESC LIMIT 1");
            if ($tableMaxId && $tableMaxId >= $threshold) {
                $this->logStatus("Truncating big table $t which has $tableMaxId records");
                $db->executeUpdate("DELETE FROM `$t`");
                $db->executeUpdate('SET FOREIGN_KEY_CHECKS = 0');
                $db->executeUpdate("TRUNCATE TABLE `$t`");
                $db->executeUpdate('SET FOREIGN_KEY_CHECKS = 1');
            }
        }
    }

    private function _cleanupRateLimitLogs()
    {
        $db = App::getDb();

        $db->executeQuery('DELETE FROM `rate_limit_log` WHERE `date_created` < (NOW() - INTERVAL 1 DAY)');
    }

    private function _cleanupOldBuilds()
    {
        $logger = new Logger('out');
        $debug  = new DebugHandler();
        $logger->pushHandler($debug);

        $service = new UpdateCleanup(App::getContainer());
        $service->cleanup(true, $logger);

        $logString = implode("\n", array_map(function ($r) {
            return '[UpdateCleanup] '.$r['message'];
        }, $debug->getLogs()));

        if ($logString) {
            $this->logStatus($logString);
        }
    }

    private function _cleanHttpCache()
    {
        $env = App::$container->get('deskpro.app_env');
        if ($env->getConfig('settings.disable_portal_http_cache')) {
            return;
        }
        $cacheDir = $env->getUserCacheDir().DIRECTORY_SEPARATOR.'http_cache';

        if (!is_dir($cacheDir)) {
            return;
        }

        $dirFinder = new Finder();
        $dirFinder->directories()->in([$cacheDir])->depth('== 0');

        /* @var \Symfony\Component\Finder\SplFileInfo $dirName */
        $maybeDeleteDirs = [];
        foreach ($dirFinder as $dirName) {
            if ($dirName->getFilename() !== $env->getAppName()) {
                // cannot delete here, as it might mess up the $finder iterator
                $maybeDeleteDirs[] = $dirName->getRealPath();
            }
        }

        $fs = new Filesystem();

        try {
            $fs->remove($maybeDeleteDirs);
        } catch (Exception $e) {
        }
    }
}
