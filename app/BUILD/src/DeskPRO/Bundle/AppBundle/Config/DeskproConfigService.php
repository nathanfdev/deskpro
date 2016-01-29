<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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
namespace DeskPRO\Bundle\AppBundle\Config;

use Monolog\Logger;

/**
 * This is meant to be used in the container as a way of using expressions to get at some of our dynamic config
 * methods in config files.
 */
class DeskproConfigService
{
    /**
     * The dir we store all of our logs in (absolute path).
     *
     * @return string
     */
    public function getLogDir()
    {
        return dp_get_log_dir();
    }

    /**
     * This (and higher) are the only log level lines we want stored.
     */
    public function getLogLevel()
    {
        global $DP_CONFIG;

        if (isset($DP_CONFIG['log_level'])) {
            $log_level = $DP_CONFIG['log_level'];
        } else {
            $log_level = Logger::DEBUG;
        }

        return $log_level;
    }

    /**
     * We don't store logs unless we hit a line with this log level.
     */
    public function getLogLevelThreshold()
    {
        global $DP_CONFIG;

        if (isset($DP_CONFIG['log_level_threshold'])) {
            $log_level = $DP_CONFIG['log_level_threshold'];
        } else {
            $log_level = Logger::ERROR;
        }

        return $log_level;
    }

    /**
     * @return int
     */
    public function getBuildNumber()
    {
        return defined('DP_BUILD_TIME') ? DP_BUILD_TIME : 0;
    }
}
