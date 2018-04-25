<?php

namespace DeskPRO\Bundle\AppBundle\DBAL;

use DeskPRO\Bundle\AppBundle\AppEnv\AppEnvInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Logging\SQLLogger;
use DpRun\LowUtil;

class DbConfigReader
{
    const DEFAULT_ID = 'default';
    const SYSTEM_ID  = 'system';
    const AUDIT_ID   = 'audit';
    const READ_ID    = 'read';
    const REPORTS_ID = 'read_reports';
    const SEARCH_ID  = 'read_search';

    /**
     * @var \DpRun\DpEnv
     */
    private $appEnv;

    /**
     * @var SQLLogger
     */
    private $logger;

    /**
     * DbConfigReader constructor.
     *
     * @param AppEnvInterface $appEnv
     */
    public function __construct(AppEnvInterface $appEnv, SQLLogger $logger = null)
    {
        $this->appEnv = $appEnv;
        $this->logger = $logger;
    }

    /**
     * Given a connection ID, get params from config.
     *
     * @param string $id
     *
     * @return array
     */
    public function getParams($id)
    {
        switch ($id) {
            case self::DEFAULT_ID:
                $conf_array_raw = $this->appEnv->getConfig('database');
                break;
            case self::SYSTEM_ID:
                $conf_array_raw = $this->appEnv->getConfig('database_advanced.system') ?: $this->appEnv->getConfig('database');
                break;
            case self::AUDIT_ID:
                $conf_array_raw = $this->appEnv->getConfig('database_advanced.audit') ?: $this->appEnv->getConfig('database');
                break;
            case self::READ_ID:
                $conf_array_raw = $this->appEnv->getConfig('database_advanced.read')
                    ?: $this->appEnv->getConfig('database');
                break;
            case self::REPORTS_ID:
                $conf_array_raw = $this->appEnv->getConfig('database_advanced.read_reports')
                    ?: $this->appEnv->getConfig('database_advanced.read')
                    ?: $this->appEnv->getConfig('database');
                break;
            case self::SEARCH_ID:
                $conf_array_raw = $this->appEnv->getConfig('database_advanced.read_search')
                    ?: $this->appEnv->getConfig('database_advanced.read')
                    ?: $this->appEnv->getConfig('database');
                break;
        }

        // An array of configs
        if (!empty($conf_array_raw[0]) && empty($conf_array_raw['host'])) {
            $conf_array = $conf_array_raw;

        // Standard simplified config where 'host', 'user' etc are top-level
        } else {
            $conf_array = [$conf_array_raw];
        }

        $db_conf = $conf_array[array_rand($conf_array)];

        $params = LowUtil::getMysqlInfoFromConfigArray($db_conf);

        $doctrine_params                        = $params['doctrine'];
        $doctrine_params['wrapperClass']        = 'Application\\DeskPRO\\DBAL\\Connection';
        $doctrine_params['dp_connect_attempts'] = 2;

        return $doctrine_params;
    }

    public function getSqlLogger()
    {
        if ($this->appEnv->getConfig('logs.log_db_queries')) {
            return $this->logger;
        }

        return null;
    }
}
