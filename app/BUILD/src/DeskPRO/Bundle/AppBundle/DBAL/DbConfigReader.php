<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\AppBundle\DBAL;

use DeskPRO\Bundle\AppBundle\AppEnv\AppEnvInterface;
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
     * DbConfigReader constructor.
     *
     * @param AppEnvInterface $appEnv
     */
    public function __construct(AppEnvInterface $appEnv)
    {
        $this->appEnv = $appEnv;
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
}
