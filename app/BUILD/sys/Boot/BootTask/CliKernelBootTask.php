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

namespace DpSys\Boot\BootTask;

use DpSys\Kernel;
use Symfony\Component\Console\Input\ArgvInput;

/**
 * This creates a HttpKernel based on the current CLI command.
 */
class CliKernelBootTask implements BootTaskInterface
{
    public function run(\DpRun\DpEnv $env, array $resources)
    {
        $argv = $_SERVER['argv'];

        $argv[0] = 'console';

        // --kernel virtual arg is not passed to real commands
        if (($idx = array_search('--kernel', $argv, true)) !== false) {
            $cmd_ns = $argv[$idx + 1];
            unset($argv[$idx]);
            unset($argv[$idx + 1]);
            $argv = array_values($argv);
        } else {
            $cmd_ns = null;
        }

        // another virtual arg
        if (($idx = array_search('--fake-db-connection', $argv, true)) !== false) {
            define('DP_USE_FAKE_DB_CONNECTION', true);
            unset($argv[$idx]);
            $argv = array_values($argv);
        }

        if (!$cmd_ns && !empty($argv[1])) {
            // try to get namespace from command name
            $cmd_ns = $this->getNamespace($argv[1]);
        }

        define('DP_INTERFACE', 'cli');

        switch ($cmd_ns) {
            case 'dpdev':
                $kernel = new Kernel\DevKernel($env->getEnvId(), $env->isDebug(), $env);
                break;

            case 'install':
            case 'update':
                $kernel = new Kernel\InstallKernel($env->getEnvId(), $env->isDebug(), $env);
                break;

            case 'portal':
                $kernel = new Kernel\PortalKernel($env->getEnvId(), $env->isDebug(), $env);
                break;

            default:
                $kernel = new Kernel\DpKernel($env->getEnvId(), $env->isDebug(), $env);
                break;
        }

        return [
            'cli_input'  => new ArgvInput($argv),
            'cli_kernel' => $kernel,
        ];
    }

    /**
     * @param string $cmd e.g. "foo:bar"
     *
     * @return string e.g. "foo"
     */
    private function getNamespace($cmd)
    {
        $parts = explode(':', $cmd, 2);
        if (count($parts) !== 2) {
            return;
        }

        return $parts[0];
    }
}
