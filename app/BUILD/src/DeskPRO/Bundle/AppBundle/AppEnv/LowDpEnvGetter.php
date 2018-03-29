<?php

namespace DeskPRO\Bundle\AppBundle\AppEnv;

class LowDpEnvGetter
{
    /**
     * @return \DpRun\DpEnv
     */
    public static function getDpEnv()
    {
        /* @var \DpRun\DpEnv $DP_ENV */
        global $DP_ENV;

        return $DP_ENV;
    }
}
