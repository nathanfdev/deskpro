<?php

namespace DpSys\Boot;

require_once __DIR__.'/BootTask/BootTaskInterface.php';

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\HttpFoundation\Request;

class Boot
{
    /**
     * @param \DpRun\DpEnv $env
     * @param array        $tasks
     * @param array        $resources
     *
     * @return array
     */
    public static function runBootTasks(\DpRun\DpEnv $env, array $tasks, array $resources = [])
    {
        $tasks = array_map(function ($t) {
            if (is_array($t)) {
                $classname = $t[0];
                $options = $t[1];
            } else {
                $classname = $t;
                $options = [];
            }

            $classname = $classname.'BootTask';
            $full_classname = 'DpSys\\Boot\\BootTask\\'.$classname;

            if (!class_exists($full_classname, false)) {
                require __DIR__.'/BootTask/'.$classname.'.php';
            }

            return new $full_classname($options);
        }, $tasks);

        /** @var BootTask\BootTaskInterface $t */
        foreach ($tasks as $t) {
            $res = $t->run($env, $resources);
            if ($res && is_array($res)) {
                $resources = array_merge($resources, $res);
            }
        }

        return $resources;
    }

    /**
     * Runs /__serverinfo/xyz requests.
     *
     * @param \DpRun\DpEnv $env
     * @param string       $action
     * @param array        $params
     */
    private static function bootServerInfoChecks(\DpRun\DpEnv $env, $action, array $params = [])
    {
        $tasks = ['HttpServerInfo'];
        self::runBootTasks($env, $tasks, ['serverinfo_action' => $action, 'serverinfo_params' => $params]);
        exit;
    }

    /**
     * This just sets up the basic env (e.g. sets up autoloader).
     *
     * @param \DpRun\DpEnv $env
     */
    public static function bootBasicEnv(\DpRun\DpEnv $env)
    {
        $tasks = [
            'Loader',
            'Lib',
            'PreparePaths',
        ];

        self::runBootTasks($env, $tasks);
    }

    /**
     * Boot a web request.
     *
     * @param \DpRun\DpEnv $env
     */
    public static function bootWeb(\DpRun\DpEnv $env)
    {
        //------------------------------
        // verify www path
        //------------------------------

        // Makes sure the www path we have in cache
        // is the same as the path we are requesting
        if (defined('DESKPRO_WWW_PATH')) {
            if ($env) {
                // Check if its a non-default path...
                if (DESKPRO_WWW_PATH !== $env->getDpRoot().DIRECTORY_SEPARATOR.'www') {
                    $current = $env->getDatManager()->readTxtFile('www_dir', null);
                    if (!$current || $current !== DESKPRO_WWW_PATH) {
                        $env->getDatManager()->writeTxtFile('www_dir', DESKPRO_WWW_PATH);
                    }
                // If it IS the default path, then we just
                // make sure to remove the cache file.
                } else {
                    $env->getDatManager()->removeTxtFile('www_dir');
                }
            }
        }

        //------------------------------
        // Boot request and handle serverinfo reqs
        //------------------------------

        // An 'early' serverinfo request
        if (isset($_GET['__serverinfo'])) {
            self::bootServerInfoChecks($env, $_GET['__serverinfo']);

            return;
        }

        $tasks = [
            'Loader',
            'Lib',
            'PreparePaths',
            'Request',
        ];

        $res = self::runBootTasks($env, $tasks);

        /** @var \Symfony\Component\HttpFoundation\Request $request */
        $request = $res['request'];

        // We can also enter serverinfo via a path,
        // this is usually only used when we need to test URL rewriting
        $path = '/'.ltrim($request->getPathInfo(), '/');
        if (substr($path, 0, 14) === '/__serverinfo/') {
            self::bootServerInfoChecks($env, substr($path, 14));

            return;
        }

        if (substr($path, 0, 22) === '/admin/updater-status/') {
            self::bootServerInfoChecks($env, 'update_watcher', [
                'auth'    => substr($path, 22),
                'request' => $request,
            ]);

            return;
        }

        $res = self::runBootTasks($env, ['HttpVerifyRequirements', 'OfflineCheck'], $res);

        //------------------------------
        // Boot to low scripts
        //------------------------------

        $lowClass = null;
        if (substr($path, 0, 7) === '/dp.php' && (!isset($path[7]) || $path[7] === '/')) {
            $lowClass = 'DpSys\\LowScript\\DpScript';
        } elseif (substr($path, 0, 9) === '/file.php' && (!isset($path[9]) || $path[9] === '/')) {
            $lowClass = 'DpSys\\LowScript\\ServeFileScript';
        } elseif (substr($path, 0, 23) === '/app/run/test_ping.html' && (!isset($path[23]) || $path[23] === '/')) {
            $lowClass = 'DpSys\\LowScript\\TestPing';
        } elseif (substr($path, 0, 17) === '/get_messages.php' && (!isset($path[17]) || $path[17] === '/')) {
            $lowClass = 'DpSys\\LowScript\\GetMsgScript';
        }

        if ($lowClass) {
            /** @var \DpSys\LowScript\LowScriptAbstract $lowScript */
            $lowScript = new $lowClass($env, $request);
            $lowScript->run();

            return;
        }

        //------------------------------
        // Set trusted proxies
        //------------------------------

        $proxies     = [];
        $proxyConfig = $env->getConfig('env.trust_proxy_data', []);
        if (empty($proxyConfig)) {
            $proxyConfig = $env->getConfig('settings.trust_proxy_data', []);
        }
        foreach ($proxyConfig as $item) {

            // Config can contain file paths to read proxies from e.g. @/etc/proxy_list.php
            if (strpos($item, '@') === 0) {
                $array = include $item;
                if (!is_array($array)) {
                    die("Trusted proxies source file $item must define an array of proxies");
                }
                $proxies = array_merge($proxies, $array);
            } else {
                $proxies[] = $item;
            }
        }
        Request::setTrustedProxies($proxies);

        //------------------------------
        // JS boot tasks
        //------------------------------

        self::runBootTasks($env, ['HttpJs'], $res);

        //-----------------------------
        // Workaround for Chrome Data Saver issue (DP-1430)
        //-----------------------------
        if (!empty($_SERVER['HTTP_SAVE_DATA'])) {
            Request::setTrustedHeaderName(Request::HEADER_FORWARDED, null);
        }

        //------------------------------
        // Boot to a normal symfony request
        //------------------------------

        $res = self::runBootTasks($env, ['HttpKernel'], $res);

        /** @var \Symfony\Component\HttpKernel\HttpKernel $kernel */
        $kernel = $res['http_kernel'];

        $response = $kernel->handle($request);
        $response->send();
        $kernel->terminate($request, $response);
    }

    /**
     * Boot a CLI app.
     *
     * @param \DpRun\DpEnv $env
     * @param array        $commandClasses Additional command classes that don't get registered together with bundles
     */
    public static function bootCli(\DpRun\DpEnv $env, array $commandClasses = [])
    {
        $tasks = [
            'CliVerifyRequirements',
            'Loader',
            'Lib',
            'PreparePaths',
            'CliKernel',
        ];

        $res = self::runBootTasks($env, $tasks);

        /** @var \Symfony\Component\HttpKernel\KernelInterface $kernel */
        $kernel = $res['cli_kernel'];

        /** @var \Symfony\Component\Console\Input\ArgvInput $input */
        $input = $res['cli_input'];

        $commandClasses = array_merge($commandClasses, [
            'DeskPRO\Services\EmailCollection\Command\EmailCollectionCommand',
            'DeskPRO\Services\EmailProcess\Command\EmailProcessCommand',
        ]);

        $app = new Application($kernel);
        foreach ($commandClasses as $commandClass) {
            $command = new $commandClass();
            if (method_exists($command, 'setDpEnv')) {
                $command->setDpEnv($env);
            }
            $app->add($command);
        }
        $app->run($input);
    }

    /**
     * Boot cron app.
     *
     * @param \DpRun\DpEnv $env
     */
    public static function bootCron(\DpRun\DpEnv $env)
    {
        $argv    = $_SERVER['argv'];
        $argv[0] = 'console';
        array_splice($argv, 1, 0, ['dp:worker-job']);

        $tasks = [
            'CliVerifyRequirements',
            'Loader',
            'Lib',
            'PreparePaths',
            'OfflineCheck',
            'CliKernel',
        ];

        $res = self::runBootTasks($env, $tasks, ['argv' => $argv]);

        /** @var \Symfony\Component\HttpKernel\KernelInterface $kernel */
        $kernel = $res['cli_kernel'];

        $input = new ArgvInput($argv);

        $app = new Application($kernel);
        $app->run($input);
    }

    /**
     * just a shortcut.
     *
     * @return \DpRun\DpEnv
     */
    protected static function env()
    {
        /** @var \DpRun\DpEnv $env */
        $env = $GLOBALS['DP_ENV'];

        return $env;
    }
}
