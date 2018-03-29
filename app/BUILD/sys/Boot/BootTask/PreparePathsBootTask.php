<?php

namespace DpSys\Boot\BootTask;

/**
 * This just makes sure directories exist that need to exist.
 */
class PreparePathsBootTask implements BootTaskInterface
{
    public function run(\DpRun\DpEnv $env, array $resources)
    {
        @umask((int) $env->getConfig('env.set_umask', 0000));

        $expect = [
            $env->getAppBaseKernelCacheDir(),
            $env->getUserFilesDir(),
            $env->getUserBackupsDir(),
            $env->getUserDebugDir(),
            $env->getUserLogsDir(),
            $env->getUserCacheDir(),
            $env->getUserTmpDir(),
        ];

        foreach ($expect as $dir) {
            if (!is_dir($dir)) {
                @mkdir($dir, 0777, true);
            }
        }
    }
}
