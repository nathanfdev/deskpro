<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\AppSecret;

use DpRun\LowUtil;

/**
 * A service that returns the app secret for this program, because the app secret will be different for
 * every install. Standard Symfony containers store static secrets, and we can't do that.
 */
class AppSecret
{
    /**
     * @return string
     */
    public function getAppSecret()
    {
        global $DP_ENV;

        // shouldnt happen
        if (!isset($DP_ENV)) {
            return uniqid(sha1(mt_rand(0, 99999)), true);
        }

        // an app key explicitly set
        if ($appKey = $DP_ENV->getConfig('settings.app_key')) {
            return $appKey;
        }

        $dbInfo = LowUtil::getMysqlInfoFromConfigArray($DP_ENV->getConfig('database'));

        return sha1(
            $dbInfo['dsn'].$dbInfo['password']
        );
    }
}
