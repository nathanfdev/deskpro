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

namespace Application\DeskPRO\ServerErrorLogs;

use Application\DeskPRO\App;
use Application\DeskPRO\Log\ErrorLog\ErrorLogReader;

class ServerErrorLogs
{
    /**
     * @var string
     */
    private $auth;

    /**
     * @var string
     */
    private $logsPath;

    public function __construct()
    {
        /* @var \DpRun\DpEnv */
        global $DP_ENV;

        $this->auth     = $DP_ENV->getDatManager()->readTxtFile('server_info_auth', '');
        $this->logsPath = $DP_ENV->getUserLogsDir();
    }

    /**
     * @return array
     */
    public function getAll()
    {
        if (filesize($this->logsPath.'/error.log') < 10 * 1024 * 1024) {
            $logReader = new ErrorLogReader($this->logsPath.'/error.log');
            $logReader->setDateTimezone(App::getSession()->getPerson()->getDateTimezone());
            $logs = array_values($logReader->getAll());
        } else {
            $logs = false;
        }

        return [
            'logs'                  => $logs,
            'deskpro_error_log_url' => $this->_generateUrl('logs/errors'),
            'web_error_log_url'     => $this->_generateUrl('logs/php-errors'),
        ];
    }

    /**
     * @param int $id
     *
     * @return array|null
     */
    public function getById($id)
    {
        $logReader = new ErrorLogReader($this->logsPath.'/error.log');
        $logReader->setDateTimezone(App::getSession()->getPerson()->getDateTimezone());
        $logReader->enableRawLog();
        $logReader->setIdFilter($id);

        $log = $logReader->current();

        return $log;
    }

    /**
     * @return bool
     */
    public function clearAllErrors()
    {
        if (!is_writable($this->logsPath.'/error.log')) {
            return false;
        }

        @file_put_contents($this->logsPath.'/error.log', '');

        return true;
    }

    /**
     * @param string $url
     *
     * @return string
     */
    private function _generateUrl($url)
    {
        $result = App::getContainer()->getBrandSetting('core.deskpro_url').'__serverinfo/'.$url;
        $result .= '?auth='.$this->auth;

        return $result;
    }
}
