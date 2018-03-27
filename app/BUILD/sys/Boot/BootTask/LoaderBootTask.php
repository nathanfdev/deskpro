<?php

namespace DpSys\Boot\BootTask;

/**
 * This sets up the auto-loader.
 */
class LoaderBootTask implements BootTaskInterface
{
    public function run(\DpRun\DpEnv $env, array $resources)
    {
        require DP_APP_DIR.'/sys/autoload.php';
    }
}
