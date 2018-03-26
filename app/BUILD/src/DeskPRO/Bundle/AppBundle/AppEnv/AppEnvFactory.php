<?php

namespace DeskPRO\Bundle\AppBundle\AppEnv;

class AppEnvFactory
{
    /**
     * @return AppEnvInterface
     */
    public static function create()
    {
        /* @var \DpRun\DpEnv $DP_ENV */
        global $DP_ENV;

        return new AppEnv($DP_ENV);
    }
}
