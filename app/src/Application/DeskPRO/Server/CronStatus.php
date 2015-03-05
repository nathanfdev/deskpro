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
 * @category Entities
 */

namespace Application\DeskPRO\Server;

use Application\DeskPRO\DBAL\Connection;
use Orb\Util\Strings;

class CronStatus
{
    /**
     * @var \Application\DeskPRO\DBAL\Connection
     */
    private $db;

    /**
     * @var \DateTime
     */
    private $last_run_ts;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }


    /**
     * @return int
     */
    public function getLastRunTimestamp()
    {
        if ($this->last_run_ts !== null) {
            return $this->last_run_ts;
        }

        $this->last_run_ts = $this->db->fetchColumn("
            SELECT value
            FROM settings
            WHERE name = ?
        ", array('core.last_cron_run'));

        if (!$this->last_run_ts) {
            $this->last_run_ts = 0;
        }

        return $this->last_run_ts;
    }


    /**
     * @return \DateTime|null
     */
    public function getLastRunDate()
    {
        $ts = $this->getLastRunTimestamp();
        if (!$ts) {
            return null;
        }

        $date = new \DateTime("@$ts");

        return $date;
    }


    /**
     * Get how long it's been since the last cron
     *
     * @return int
     */
    public function getSecsSinceLastRun()
    {
        $now = time();
        $last = $this->getLastRunTimestamp();

        return $now - $last;
    }

    /**
     * Based on the last time cron was run, guess if there's a problem.
     *
     * @return bool
     */
    public function guessIsProblem()
    {
        return $this->getSecsSinceLastRun() > 300;
    }


    /**
     * @return array
     */
    public function getCronBootErrors()
    {
        // Check for error db record
        $error_message = $this->db->fetchColumn("SELECT data FROM install_data WHERE build = 1 AND name = 'cron_run_errors'");
        if (!$error_message) {
            // Check for a logged message
            if (file_exists(dp_get_log_dir().'/cron-boot-errors.log')) {
                $error_message = file_get_contents(dp_get_log_dir().'/cron-boot-errors.log');
            }
        }

        if ($error_message) {
            $split = explode('###', $error_message);
            $codes_string = array_pop($split);
            $codes_string = trim($codes_string);

            $ini_path = Strings::extractRegexMatch('#^ini_path:(.*?)$#m', $codes_string, 1);

            $error_codes = array();
            if (preg_match_all('#^error:(.*?)$#m', $codes_string, $m, \PREG_PATTERN_ORDER)) {
                $error_codes = $m[1];
            }

            $web_ini_path = \Orb\Util\Env::getPhpIniPath();
            $is_zendserver = false;
            if ($web_ini_path) {
                $is_zendserver = strpos($web_ini_path, 'ZendServer') !== false;
            }

            return array(
                'error_codes'   => $error_codes,
                'ini_path'      => $ini_path,
                'is_zendserver' => $is_zendserver,
                'web_ini_path'  => $web_ini_path,
                'data_dir'      => dp_get_data_dir(),
                'error_log'     => @file_get_contents(dp_get_log_dir() . '/error.log') . "\n\n\n" . @file_get_contents(dp_get_log_dir() . '/cli-phperr.log')
            );
        }

        return null;
    }
}
