<?php

namespace DpSys\Boot\BootTask;

use DeskPRO\Component\Util\MapUtils;
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

        // barg -- boot args
        while (($idx = array_search('--barg', $argv, true)) !== false) {
            $val = $argv[$idx + 1];
            unset($argv[$idx + 1]);
            unset($argv[$idx]);
            $argv = array_values($argv);

            if (strpos($val, '=') !== false) {
                list($name, $val) = explode('=', $val, 2);
            } else {
                $name = $val;
                $val  = true;
            }

            $this->handleBootArg($env, $name, $val);
        }

        if (!$cmd_ns && !empty($argv[1])) {
            // try to get namespace from command name
            $cmd_ns = $this->getNamespace($argv[1]);
        }

        define('DP_INTERFACE', 'cli');

        switch ($cmd_ns) {
            case 'dpdev':
            case 'debug':
                $kernel = new Kernel\DevKernel($env->getEnvId(), $env->isDebug(), $env);
                break;

            case 'install':
            case 'update':
                $kernel = new Kernel\InstallKernel($env->getEnvId(), $env->isDebug(), $env);
                break;

            case 'portal':
                $kernel = new Kernel\PortalKernel($env->getEnvId(), $env->isDebug(), $env);
                break;

            case 'api':
                $kernel = new Kernel\ApiKernel($env->getEnvId(), $env->isDebug(), $env);
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

    private function handleBootArg(\DpRun\DpEnv $env, $name, $value)
    {
        switch ($name) {
            case 'is-building':
                $env->setRuntimeVar('is_building', true);
                break;
            case 'config':
                // Called like: --barg "config=foo.bar.baz:value"

                if (strpos($value, ':') === false) {
                    echo "Invalid config bard\n";
                    exit(1);
                }

                list($configName, $configValue) = explode(':', $value, 2);
                $configNameParts                = explode('.', $configName);

                $fileId = array_shift($configNameParts);
                $env->getConfigReader()->addConfigLoader(function ($loadFileId, array $config) use ($fileId, $configNameParts, $configValue) {
                    if ($loadFileId === $fileId) {
                        $config = MapUtils::setIn($config, $configNameParts, $configValue);
                    }

                    return $config;
                });
                $env->getConfigReader()->resetCache();
                break;
        }
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
