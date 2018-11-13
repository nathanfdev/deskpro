<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\ServerReportFile;

use Application\DeskPRO\App;
use DpRun\LowUtil;

class InfoTpl
{
    public $genTime;
    public $version;
    public $schemaId;
    public $installTime;
    public $installSchemaId;
    public $installSource;
    public $lastCronStartTime;
    public $lastCronTime;

    public function __construct()
    {
        $this->genTime           = time();
        $this->version           = defined('DP_BUILD_NUM') ? DP_BUILD_NUM : 0;
        $this->schemaId          = defined('DP_BUILD_TIME') ? DP_BUILD_TIME : 0;
        $this->installTime       = App::getSetting('core.install_timestamp');
        $this->installSchemaId   = App::getSetting('core.install_build');
        $this->installSource     = App::getSetting('core.install_source');
        $this->lastCronStartTime = App::getSetting('core.last_cron_start');
        $this->lastCronTime      = App::getSetting('core.last_cron_run');
    }

    public function getDatabases()
    {
        /* @var \DpRun\DpEnv $DP_ENV */
        global $DP_ENV;

        $dbs = [];

        $dbs = array_merge($dbs, $this->_readDbFromConfig('default', $DP_ENV->getConfig('database')));
        $dbs = array_merge($dbs, $this->_readDbFromConfig('read', $DP_ENV->getConfig('database_advanced.read')));
        $dbs = array_merge($dbs, $this->_readDbFromConfig('reports', $DP_ENV->getConfig('database_advanced.read_reports')));
        $dbs = array_merge($dbs, $this->_readDbFromConfig('filters', $DP_ENV->getConfig('database_advanced.read_search')));
        $dbs = array_merge($dbs, $this->_readDbFromConfig('system', $DP_ENV->getConfig('database_advanced.system')));
        $dbs = array_merge($dbs, $this->_readDbFromConfig('audit', $DP_ENV->getConfig('database_advanced.audit')));

        return $dbs;
    }

    private function _readDbFromConfig($name, $info = null)
    {
        if (!$info) {
            return [];
        }

        if (isset($info[0])) {
            $r = [];
            foreach ($info as $sub) {
                $r = array_merge($r, $this->_readDbFromConfig($name, $sub));
            }

            return $r;
        }

        $d = LowUtil::getMysqlInfoFromConfigArray($info);
        if (!$d) {
            return [];
        }

        return ["[$name] ".$d['dsn'].' (user: '.$d['user'].')'];
    }

    public function getConfig($name)
    {
        /* @var \DpRun\DpEnv $DP_ENV */
        global $DP_ENV;

        return $DP_ENV->getConfig($name);
    }

    public function fullDate($t)
    {
        if (!$t) {
            return null;
        }

        return date('j F Y @ H:i:s', $t);
    }

    public function getSetting($name)
    {
    }

    public function getCronInfo()
    {
        return $jobs = App::getOrm()->createQuery('
            SELECT j
            FROM DeskPRO:WorkerJob j
            ORDER BY j.last_run_date ASC
        ')->execute();
    }

    public function getTableCounts()
    {
        try {
            $db     = App::getDbRead();
            $tables = $db->fetchAllCol('SHOW TABLES');

            $counts = [];
            foreach ($tables as $t) {
                $counts[$t] = $db->fetchColumn("SELECT COUNT(*) FROM $t");
            }

            return $counts;
        } catch (\Exception $e) {
            return ['!!!', $e->getMessage()];
        }
    }

    public function output($s)
    {
        echo trim($s)."\n";
    }

    public function render()
    {
        ob_start();
        require __DIR__.'/infoTpl.txt.php';
        $__out = ob_get_clean();

        return $__out;
    }

    public function formatLicenseCode($c)
    {
        $c = preg_replace('/\\s/', '', $c);

        return chunk_split($c, 72, "\n");
    }
}
